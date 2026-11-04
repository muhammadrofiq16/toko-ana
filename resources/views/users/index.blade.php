@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="userManagementApp()" x-cloak>

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                <span>PENGATURAN SISTEM</span>
                <span>•</span>
                <span class="text-brand-600">HAK AKSES PENGGUNA</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Manajemen Akun</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola akun pengguna, peran otorisasi (Admin / Kasir), dan kata sandi sistem.</p>
        </div>

        <button @click="openAddModal()" 
                class="bg-brand-dark hover:bg-brand-800 text-white font-semibold text-xs md:text-sm px-4 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition-all hover:shadow self-start md:self-auto">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Akun Baru</span>
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
        <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
        <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 flex items-center gap-3" x-data="{ show: true }" x-show="show" x-transition>
        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
        <p class="text-sm font-semibold text-rose-800">{{ session('error') }}</p>
        <button @click="show = false" class="ml-auto text-rose-400 hover:text-rose-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
        <div class="flex items-center gap-2 text-rose-800 font-bold text-sm mb-1">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <span>Terdapat beberapa kesalahan input:</span>
        </div>
        <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5 ml-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Akun -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500">Total Akun Terdaftar</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $totalUsers }}</span>
                    <span class="text-xs text-slate-400 font-medium">pengguna</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Akun Admin -->
        <div class="bg-gradient-to-br from-indigo-50/70 to-indigo-100/50 border border-indigo-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-indigo-700">Role Admin Toko</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-2xl font-black text-indigo-900 tracking-tight">{{ $adminCount }}</span>
                    <span class="text-xs text-indigo-600 font-medium">akses penuh</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-700">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Akun Kasir -->
        <div class="bg-gradient-to-br from-emerald-50/70 to-emerald-100/50 border border-emerald-200/80 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-700">Role Kasir POS</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span class="text-2xl font-black text-emerald-900 tracking-tight">{{ $kasirCount }}</span>
                    <span class="text-xs text-emerald-600 font-medium">operasional</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-700">
                <i data-lucide="monitor" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Input -->
        <form method="GET" action="{{ route('pengguna.index') }}" class="relative w-full md:w-80">
            <input type="hidden" name="role" value="{{ $roleFilter }}">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Cari nama atau email..." 
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-xs md:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
        </form>

        <!-- Role Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-semibold">
            <a href="{{ route('pengguna.index', ['role' => 'all', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-lg transition-all {{ $roleFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua ({{ $totalUsers }})
            </a>
            <a href="{{ route('pengguna.index', ['role' => 'admin', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-lg transition-all {{ $roleFilter === 'admin' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Admin ({{ $adminCount }})
            </a>
            <a href="{{ route('pengguna.index', ['role' => 'kasir', 'search' => $search]) }}" 
               class="px-3 py-1.5 rounded-lg transition-all {{ $roleFilter === 'kasir' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Kasir ({{ $kasirCount }})
            </a>
        </div>
    </div>

    {{-- User Table Card --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs md:text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-slate-400 uppercase tracking-wider text-[11px] font-bold">
                        <th class="py-3.5 px-5">PENGGUNA</th>
                        <th class="py-3.5 px-4 text-center">ROLE / HAK AKSES</th>
                        <th class="py-3.5 px-4">TANGGAL DIBUAT</th>
                        <th class="py-3.5 px-5 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- User Name & Email -->
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $user->isAdmin() ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }} flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-slate-900 truncate">{{ $user->name }}</p>
                                        @if($user->id === auth()->id())
                                        <span class="bg-brand-50 border border-brand-200 text-brand-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                                            Akun Anda
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Role Badge -->
                        <td class="py-4 px-4 text-center">
                            @if($user->isAdmin())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                <span>Admin Toko</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="monitor" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Kasir POS</span>
                            </span>
                            @endif
                        </td>

                        <!-- Created At -->
                        <td class="py-4 px-4 text-xs font-medium text-slate-500">
                            {{ $user->created_at ? $user->created_at->locale('id')->isoFormat('D MMMM YYYY') : '-' }}
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Edit Button -->
                                <button type="button" 
                                        @click="openEditModal({{ json_encode($user) }})"
                                        class="p-2 text-slate-500 hover:text-brand-700 hover:bg-brand-50 rounded-lg transition-all" 
                                        title="Ubah Data & Role">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>

                                <!-- Delete Button -->
                                @if($user->id !== auth()->id())
                                <form action="{{ route('pengguna.destroy', $user->id) }}" 
                                      method="POST" 
                                      class="inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" 
                                            title="Hapus Akun">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @else
                                <span class="p-2 text-slate-300 cursor-not-allowed" title="Tidak dapat menghapus akun sendiri">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400">
                            <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                            <p class="font-semibold text-slate-600">Tidak ada akun ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian lain atau tambah akun baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL 1: Tambah Akun Baru --}}
    <div x-show="addModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4" 
         x-transition.opacity 
         style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-md w-full p-6 shadow-2xl relative" 
             @click.away="addModalOpen = false">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold border border-brand-200/50">
                        <i data-lucide="user-plus" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Tambah Akun Baru</h3>
                        <p class="text-xs text-slate-400">Buat akun untuk staf kasir atau admin toko.</p>
                    </div>
                </div>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('pengguna.store') }}" method="POST" class="space-y-4 mt-5">
                @csrf

                <!-- Name Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           required 
                           placeholder="Contoh: Siti Aisyah" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                </div>

                <!-- Email Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           required 
                           placeholder="nama@toko.com" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Peran / Hak Akses <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="border rounded-xl p-3 flex flex-col gap-1 cursor-pointer transition-all border-slate-200 hover:border-emerald-300"
                               :class="newRole === 'kasir' ? 'bg-emerald-50/80 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-slate-50'">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Kasir POS</span>
                                <input type="radio" name="role" value="kasir" x-model="newRole" class="text-emerald-600 focus:ring-emerald-500">
                            </div>
                            <p class="text-[10px] text-slate-500">POS Kasir, Stok Masuk, dan Dashboard Ringkas.</p>
                        </label>

                        <label class="border rounded-xl p-3 flex flex-col gap-1 cursor-pointer transition-all border-slate-200 hover:border-indigo-300"
                               :class="newRole === 'admin' ? 'bg-indigo-50/80 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50'">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Admin Toko</span>
                                <input type="radio" name="role" value="admin" x-model="newRole" class="text-indigo-600 focus:ring-indigo-500">
                            </div>
                            <p class="text-[10px] text-slate-500">Akses penuh: Keuangan, Barang, dan Manajemen Akun.</p>
                        </label>
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           required 
                           minlength="6"
                           placeholder="Minimal 6 karakter" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" 
                            @click="addModalOpen = false" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-brand-dark hover:bg-brand-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Edit Akun & Ubah Role --}}
    <div x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4" 
         x-transition.opacity 
         style="display: none;">
        <div class="bg-white border border-slate-200 rounded-2xl max-w-md w-full p-6 shadow-2xl relative" 
             @click.away="editModalOpen = false">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold border border-indigo-200/50">
                        <i data-lucide="user-cog" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Ubah Data & Role Akun</h3>
                        <p class="text-xs text-slate-400">Edit informasi profil atau ubah peran pengguna.</p>
                    </div>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'{{ url('pengguna') }}/' + editForm.id" method="POST" class="space-y-4 mt-5">
                @csrf
                @method('PUT')

                <!-- Name Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           x-model="editForm.name"
                           required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                </div>

                <!-- Email Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           x-model="editForm.email"
                           required 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                </div>

                <!-- Role Selection (UBAH ROLE) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Ubah Peran / Hak Akses <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="border rounded-xl p-3 flex flex-col gap-1 cursor-pointer transition-all border-slate-200 hover:border-emerald-300"
                               :class="editForm.role === 'kasir' ? 'bg-emerald-50/80 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-slate-50'">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Kasir POS</span>
                                <input type="radio" name="role" value="kasir" x-model="editForm.role" class="text-emerald-600 focus:ring-emerald-500">
                            </div>
                            <p class="text-[10px] text-slate-500">Akses dibatasi untuk kasir operasional.</p>
                        </label>

                        <label class="border rounded-xl p-3 flex flex-col gap-1 cursor-pointer transition-all border-slate-200 hover:border-indigo-300"
                               :class="editForm.role === 'admin' ? 'bg-indigo-50/80 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-slate-50'">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">Admin Toko</span>
                                <input type="radio" name="role" value="admin" x-model="editForm.role" class="text-indigo-600 focus:ring-indigo-500">
                            </div>
                            <p class="text-[10px] text-slate-500">Akses penuh ke seluruh sistem toko.</p>
                        </label>
                    </div>
                </div>

                <!-- Optional Password Change -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi Baru (Opsional)
                        </label>
                        <span class="text-[10px] text-slate-400">Kosongkan jika tidak ingin diubah</span>
                    </div>
                    <input type="password" 
                           name="password" 
                           minlength="6"
                           placeholder="••••••••" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" 
                            @click="editModalOpen = false" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function userManagementApp() {
        return {
            addModalOpen: false,
            editModalOpen: false,
            newRole: 'kasir',
            editForm: {
                id: '',
                name: '',
                email: '',
                role: 'kasir'
            },
            openAddModal() {
                this.newRole = 'kasir';
                this.addModalOpen = true;
                this.$nextTick(() => {
                    lucide.createIcons();
                });
            },
            openEditModal(user) {
                this.editForm = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    role: user.role
                };
                this.editModalOpen = true;
                this.$nextTick(() => {
                    lucide.createIcons();
                });
            }
        }
    }
</script>
@endsection
