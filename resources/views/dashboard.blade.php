@extends('layouts.panel')

@php($pageTitle = 'Panel Data Master')

@section('content')
<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded bg-gradient-to-r from-orange-300 via-pink-400 to-rose-400 p-5 text-white shadow">
        <div class="text-sm font-medium text-white/90">Role Aktif</div>
        <div class="mt-2 text-3xl font-bold uppercase tracking-wide">{{ Auth::user()->role }}</div>
        <div class="mt-4 text-xs text-white/80">Sesi panel sedang aktif</div>
    </div>
    <div class="rounded bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500 p-5 text-white shadow">
        <div class="text-sm font-medium text-white/90">Pengguna</div>
        <div class="mt-2 text-3xl font-bold">{{ Auth::user()->name }}</div>
        <div class="mt-4 text-xs text-white/80">Akses terautentikasi</div>
    </div>
    <div class="rounded bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 p-5 text-white shadow">
        <div class="text-sm font-medium text-white/90">Kontak</div>
        <div class="mt-2 text-xl font-bold">{{ Auth::user()->email }}</div>
        <div class="mt-4 text-xs text-white/80">Email akun terdaftar</div>
    </div>
</div>

@if (Auth::user()->role === 'admin')
    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded bg-white p-5 shadow lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold">Master Data Admin (CRUD)</h2>
            <p class="mb-4 text-sm text-slate-500">Kelola seluruh data utama untuk ujian, soal, dan tambahan waktu peserta.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr>
                            <th class="px-3 py-2">Data Master</th>
                            <th class="px-3 py-2">Create</th>
                            <th class="px-3 py-2">Read</th>
                            <th class="px-3 py-2">Update</th>
                            <th class="px-3 py-2">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t">
                            <td class="px-3 py-2 font-medium">Ujian</td>
                            <td class="px-3 py-2"><a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.create') }}">Tambah</a></td>
                            <td class="px-3 py-2"><a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.index') }}">Lihat</a></td>
                            <td class="px-3 py-2 text-slate-500">Kelola di detail ujian</td>
                            <td class="px-3 py-2 text-slate-500">(opsional berikutnya)</td>
                        </tr>
                        <tr class="border-t">
                            <td class="px-3 py-2 font-medium">Soal Ujian</td>
                            <td class="px-3 py-2 text-slate-500">Dari halaman detail ujian</td>
                            <td class="px-3 py-2"><a class="text-indigo-600 hover:underline" href="{{ route('admin.exams.index') }}">Lihat</a></td>
                            <td class="px-3 py-2 text-slate-500">(opsional berikutnya)</td>
                            <td class="px-3 py-2 text-slate-500">(opsional berikutnya)</td>
                        </tr>
                        <tr class="border-t">
                            <td class="px-3 py-2 font-medium">Tambahan Waktu User</td>
                            <td class="px-3 py-2"><a class="text-indigo-600 hover:underline" href="{{ route('admin.extensions.index') }}">Tambah</a></td>
                            <td class="px-3 py-2"><a class="text-indigo-600 hover:underline" href="{{ route('admin.extensions.index') }}">Lihat</a></td>
                            <td class="px-3 py-2 text-slate-500">(opsional berikutnya)</td>
                            <td class="px-3 py-2 text-slate-500">(opsional berikutnya)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded bg-white p-5 shadow">
            <h3 class="text-lg font-bold">Quick Access</h3>
            <p class="mt-2 text-sm text-slate-500">Akses cepat untuk operasi yang paling sering digunakan admin.</p>
            <div class="mt-4 space-y-2">
                <a href="{{ route('admin.exams.create') }}" class="inline-flex w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">+ Buat Ujian Baru</a>
                <a href="{{ route('admin.exams.index') }}" class="inline-flex w-full rounded-lg bg-slate-700 px-3 py-2 text-sm font-semibold text-white">Kelola Ujian</a>
                <a href="{{ route('admin.extensions.index') }}" class="inline-flex w-full rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white">Tambah Waktu User</a>
            </div>
        </div>
    </div>
@else
    <div class="mt-6 rounded bg-white p-5 shadow">
        <h2 class="mb-3 text-lg font-bold">Panel Peserta</h2>
        <p class="mb-4 text-slate-600">Gunakan menu di kiri untuk daftar ujian, mengerjakan ujian, dan review hasil.</p>
        <a href="{{ route('user.exams.index') }}" class="inline-block rounded bg-emerald-600 px-4 py-2 text-white">Buka Daftar Ujian</a>
    </div>
@endif
@endsection
