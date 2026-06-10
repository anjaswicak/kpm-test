@extends('layouts.panel')

@section('content')
@if (!$currentQuestion)
    <div class="rounded bg-white p-6 shadow">Soal belum tersedia.</div>
@else
    @php
        $questionsPayload = $attempt->exam->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'number' => $question->question_number,
                'text' => $question->question_text,
                'image_url' => $question->image_path ? asset('storage/'.$question->image_path) : null,
                'options' => $question->options ?? [],
            ];
        })->values();

        $serverAnswers = $answersByQuestion->mapWithKeys(function ($answer) {
            return [
                $answer->exam_question_id => [
                    'question_id' => $answer->exam_question_id,
                    'answer_option' => $answer->answer_option,
                    'answer_text' => $answer->answer_text,
                ],
            ];
        });
    @endphp

    <div id="exam-focus-root" class="fixed inset-0 z-50 overflow-y-auto bg-slate-100">
        <div id="fullscreen-gate" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 text-center shadow-xl">
                <h2 class="text-lg font-bold text-slate-800">Mode Fullscreen Wajib</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Untuk mulai mengerjakan ujian, klik tombol di bawah untuk masuk mode fullscreen.
                </p>
                <button id="start-exam-fullscreen" type="button" class="mt-4 rounded bg-indigo-600 px-4 py-2 text-white">
                    Mulai Ujian
                </button>
                <p id="fullscreen-gate-error" class="mt-3 hidden text-xs text-rose-600"></p>
            </div>
        </div>

        <div class="mx-auto max-w-6xl p-4 md:p-6">
            <div class="mb-3 rounded bg-white p-4 shadow">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-xl font-bold">{{ $attempt->exam->title }}</h1>
                    <div class="flex items-center gap-2">
                        <span id="violation-indicator" class="rounded bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pelanggaran: 0/3</span>
                        <span id="countdown" class="rounded bg-rose-100 px-3 py-1 text-sm font-semibold text-rose-700">Memuat durasi...</span>
                        <button id="fullscreen-button" type="button" class="rounded bg-slate-800 px-3 py-1 text-xs text-white">Masuk Fullscreen</button>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    Durasi ujian: {{ gmdate('H:i:s', max(0, (int) $allowedSeconds)) }}
                </p>
                <p class="mt-3 text-xs text-slate-500">
                    Mode pengawasan aktif: pindah tab/aplikasi, keluar fullscreen, atau kehilangan fokus akan tercatat sebagai pelanggaran.
                    Setelah 3 pelanggaran, ujian akan dikumpulkan otomatis.
                </p>
                <div id="warning-box" class="mt-3 hidden rounded border border-rose-300 bg-rose-50 p-2 text-sm text-rose-700"></div>
            </div>

            <div class="mb-3 rounded bg-white p-4 shadow">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div id="question-nav" class="flex flex-wrap gap-2"></div>

                    <form id="submit-attempt-form" method="POST" action="{{ route('user.attempts.submit', $attempt) }}" onsubmit="return window.autoSubmitting || confirm('Yakin kumpulkan ujian?');">
                        @csrf
                        <button id="submit-attempt-button" class="rounded bg-emerald-600 px-4 py-2 text-white" type="submit">Kumpulkan Ujian</button>
                    </form>
                </div>
            </div>

            <div class="rounded bg-white p-5 shadow">
                <h2 id="question-title" class="text-lg font-semibold"></h2>
                <p id="question-text" class="mt-2"></p>
                <img id="question-image" class="mt-3 hidden max-h-60 rounded border" alt="question image">

                <div class="mt-4 space-y-2" id="answer-options"></div>

                <textarea id="answer-text" rows="4" class="mt-4 w-full rounded border px-3 py-2" placeholder="Tulis jawaban jika perlu..."></textarea>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <button id="prev-question-button" type="button" class="rounded bg-slate-200 px-4 py-2 text-sm font-medium text-slate-700">
                        Prev
                    </button>

                    <button id="next-question-button" type="button" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.autoSubmitting = false;

        const attemptId = {{ $attempt->id }};
        const storageKey = `attempt-${attemptId}-answers`;
        const violationKey = `attempt-${attemptId}-violations`;
        const shouldResetViolations = @json((bool) ($resetViolations ?? false));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const timeStatusUrl = "{{ route('user.attempts.time-status', $attempt) }}";
        let remainingSeconds = {{ (int) $remainingSeconds }};
        const maxViolations = 3;

        const questions = @json($questionsPayload);
        const serverAnswers = @json($serverAnswers);
        let currentIndex = {{ max(0, $questionIndex - 1) }};

        const submitForm = document.getElementById('submit-attempt-form');
        const submitButton = document.getElementById('submit-attempt-button');
        const warningBox = document.getElementById('warning-box');
        const violationIndicator = document.getElementById('violation-indicator');
        const fullscreenButton = document.getElementById('fullscreen-button');
        const prevQuestionButton = document.getElementById('prev-question-button');
        const nextQuestionButton = document.getElementById('next-question-button');
        const fullscreenGate = document.getElementById('fullscreen-gate');
        const startExamFullscreenButton = document.getElementById('start-exam-fullscreen');
        const fullscreenGateError = document.getElementById('fullscreen-gate-error');

        const questionNav = document.getElementById('question-nav');
        const questionTitle = document.getElementById('question-title');
        const questionText = document.getElementById('question-text');
        const questionImage = document.getElementById('question-image');
        const answerOptions = document.getElementById('answer-options');
        const answerText = document.getElementById('answer-text');

        let cache = {};

        if (shouldResetViolations) {
            sessionStorage.setItem(violationKey, '0');
        }

        let violations = Number(sessionStorage.getItem(violationKey) || '0');
        let lastViolationAt = 0;
        let hasEnteredFullscreen = false;
        let monitoringActive = false;

        try {
            cache = JSON.parse(localStorage.getItem(storageKey) || '{}');
        } catch (error) {
            cache = {};
        }

        for (const [qid, data] of Object.entries(serverAnswers)) {
            if (!cache[qid]) {
                cache[qid] = data;
            }
        }
        localStorage.setItem(storageKey, JSON.stringify(cache));

        const getCurrentQuestion = () => questions[currentIndex];

        const getCurrentAnswer = () => {
            const q = getCurrentQuestion();
            return cache[q.id] || { question_id: q.id, answer_option: null, answer_text: null };
        };

        const setWarning = (text) => {
            warningBox.textContent = text;
            warningBox.classList.remove('hidden');
        };

        const updateViolationIndicator = () => {
            violationIndicator.textContent = `Pelanggaran: ${violations}/${maxViolations}`;
        };

        const persistCurrent = () => {
            const q = getCurrentQuestion();
            const pickedOption = answerOptions.querySelector('input[name="answer_option"]:checked')?.value || null;

            cache[q.id] = {
                question_id: q.id,
                answer_option: pickedOption,
                answer_text: answerText.value || null,
            };

            localStorage.setItem(storageKey, JSON.stringify(cache));
        };

        const syncToServer = async () => {
            persistCurrent();
            const payload = Object.values(cache).filter((item) => item && item.question_id);

            if (!payload.length) {
                return;
            }

            try {
                await fetch("{{ route('user.attempts.autosave', $attempt) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ answers: payload }),
                });
            } catch (error) {
                // Tetap simpan lokal saat offline.
            }
        };

        const submitNow = (message) => {
            if (window.autoSubmitting) {
                return;
            }

            window.autoSubmitting = true;
            setWarning(message);

            syncToServer().finally(() => {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-60', 'cursor-not-allowed');
                submitButton.textContent = 'Mengumpulkan...';
                submitForm.submit();
            });
        };

        const registerViolation = (reason) => {
            if (window.autoSubmitting) {
                return;
            }

            const now = Date.now();
            if (now - lastViolationAt < 1200) {
                return;
            }
            lastViolationAt = now;

            violations += 1;
            sessionStorage.setItem(violationKey, String(violations));
            updateViolationIndicator();
            setWarning(`Peringatan: ${reason}. Pelanggaran ${violations}/${maxViolations}.`);

            if (violations >= maxViolations) {
                submitNow('Batas pelanggaran tercapai. Ujian dikumpulkan otomatis.');
            }
        };

        const renderNavigation = () => {
            questionNav.innerHTML = '';

            questions.forEach((q, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = q.number;
                button.className = 'rounded px-3 py-1 text-sm';

                if (index === currentIndex) {
                    button.classList.add('bg-indigo-600', 'text-white');
                } else {
                    button.classList.add('bg-slate-200');
                }

                if (cache[q.id] && (cache[q.id].answer_option || cache[q.id].answer_text)) {
                    button.classList.add('ring-2', 'ring-emerald-400');
                }

                button.addEventListener('click', () => {
                    persistCurrent();
                    currentIndex = index;
                    renderQuestion();
                });

                questionNav.appendChild(button);
            });
        };

        const renderQuestion = () => {
            const q = getCurrentQuestion();
            const answer = getCurrentAnswer();

            questionTitle.textContent = `Soal ${q.number}`;
            questionText.textContent = q.text;

            if (q.image_url) {
                questionImage.src = q.image_url;
                questionImage.classList.remove('hidden');
            } else {
                questionImage.classList.add('hidden');
                questionImage.removeAttribute('src');
            }

            answerOptions.innerHTML = '';
            const options = q.options || {};

            Object.entries(options).forEach(([key, label]) => {
                const wrapper = document.createElement('label');
                wrapper.className = 'flex items-start gap-2 rounded border p-2';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'answer_option';
                radio.value = key;
                radio.checked = answer.answer_option === key;
                radio.addEventListener('change', persistCurrent);

                const text = document.createElement('span');
                text.textContent = `${key}. ${label}`;

                wrapper.appendChild(radio);
                wrapper.appendChild(text);
                answerOptions.appendChild(wrapper);
            });

            answerText.value = answer.answer_text || '';
            renderNavigation();

            prevQuestionButton.disabled = currentIndex === 0;
            prevQuestionButton.classList.toggle('opacity-50', currentIndex === 0);
            prevQuestionButton.classList.toggle('cursor-not-allowed', currentIndex === 0);

            const isLastQuestion = currentIndex === questions.length - 1;
            nextQuestionButton.disabled = isLastQuestion;
            nextQuestionButton.classList.toggle('opacity-50', isLastQuestion);
            nextQuestionButton.classList.toggle('cursor-not-allowed', isLastQuestion);
        };

        prevQuestionButton.addEventListener('click', () => {
            if (currentIndex === 0) {
                return;
            }

            persistCurrent();
            currentIndex -= 1;
            renderQuestion();
        });

        nextQuestionButton.addEventListener('click', () => {
            if (currentIndex >= questions.length - 1) {
                return;
            }

            persistCurrent();
            currentIndex += 1;
            renderQuestion();
        });

        const requestFullscreen = async () => {
            const root = document.documentElement;

            if (document.fullscreenElement) {
                hasEnteredFullscreen = true;
                return true;
            }

            const request =
                root.requestFullscreen ||
                root.webkitRequestFullscreen ||
                root.msRequestFullscreen;

            if (!request) {
                return false;
            }

            try {
                await request.call(root);
                hasEnteredFullscreen = true;
                return true;
            } catch (error) {
                return false;
            }
        };

        const startMonitoring = () => {
            if (monitoringActive) {
                return;
            }
            monitoringActive = true;

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    registerViolation('Berpindah tab/jendela');
                }
            });

            window.addEventListener('blur', () => {
                registerViolation('Kehilangan fokus jendela ujian');
            });

            document.addEventListener('fullscreenchange', () => {
                if (hasEnteredFullscreen && !document.fullscreenElement) {
                    registerViolation('Keluar dari mode fullscreen');
                }
            });
        };

        const beginExamSession = async () => {
            const entered = await requestFullscreen();

            if (!entered) {
                fullscreenGateError.textContent = 'Browser memblokir fullscreen. Coba klik lagi atau izinkan fullscreen untuk situs ini.';
                fullscreenGateError.classList.remove('hidden');
                return;
            }

            fullscreenGate.classList.add('hidden');
            fullscreenGateError.classList.add('hidden');
            startMonitoring();
        };

        fullscreenButton.addEventListener('click', async () => {
            const entered = await requestFullscreen();
            if (!entered) {
                setWarning('Fullscreen gagal. Pastikan browser mengizinkan fullscreen untuk situs ini.');
            }
        });

        startExamFullscreenButton.addEventListener('click', beginExamSession);

        document.addEventListener('contextmenu', (event) => event.preventDefault());

        document.addEventListener('copy', (event) => event.preventDefault());
        document.addEventListener('cut', (event) => event.preventDefault());
        document.addEventListener('paste', (event) => event.preventDefault());

        document.addEventListener('keydown', (event) => {
            const key = event.key.toLowerCase();
            if ((event.ctrlKey || event.metaKey) && ['c', 'v', 'x', 'u', 'p', 's'].includes(key)) {
                event.preventDefault();
            }
        });

        answerText.addEventListener('input', persistCurrent);

        setInterval(syncToServer, 5000);

        window.addEventListener('beforeunload', (event) => {
            if (!window.autoSubmitting) {
                persistCurrent();
                event.preventDefault();
                event.returnValue = '';
            }
        });

        updateViolationIndicator();
        renderQuestion();

        const countdownEl = document.getElementById('countdown');
        const formatDuration = (totalSeconds) => {
            const safeSeconds = Math.max(0, Number(totalSeconds) || 0);
            const hours = Math.floor(safeSeconds / 3600);
            const mins = Math.floor((safeSeconds % 3600) / 60);
            const secs = Math.floor(safeSeconds % 60);
            return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        };

        const renderCountdown = () => {
            countdownEl.textContent = `Sisa waktu: ${formatDuration(remainingSeconds)}`;
        };

        const syncRemainingFromServer = async () => {
            if (window.autoSubmitting) {
                return;
            }

            try {
                const response = await fetch(timeStatusUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const serverRemaining = Number(data?.remaining_seconds);

                if (!Number.isFinite(serverRemaining)) {
                    return;
                }

                if (serverRemaining > remainingSeconds) {
                    setWarning(`Waktu ujian ditambahkan oleh admin. Sisa waktu sekarang: ${formatDuration(serverRemaining)}.`);
                }

                remainingSeconds = Math.max(0, Math.floor(serverRemaining));
                renderCountdown();
            } catch (error) {
                // Abaikan error network sementara, sinkronisasi akan dicoba lagi.
            }
        };

        renderCountdown();
        setInterval(syncRemainingFromServer, 5000);

        const timer = setInterval(() => {
            if (remainingSeconds <= 0) {
                clearInterval(timer);
                countdownEl.textContent = 'Waktu habis';
                submitNow('Waktu ujian habis. Ujian dikumpulkan otomatis.');
                return;
            }

            remainingSeconds -= 1;
            renderCountdown();
        }, 1000);
    </script>
@endif
@endsection
