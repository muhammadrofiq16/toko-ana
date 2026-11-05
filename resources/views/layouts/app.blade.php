<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appSettings['store_name'] ?? 'Toko Kelontong' }} - Inventaris & Kasir</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Atkinson Hyperlegible Next"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            dark: '#0d6e4a',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible+Next:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Atkinson Hyperlegible Next', sans-serif;
            background-color: #f8fafc;
            font-size: 16px; /* Base: 1rem = 16px */
            font-weight: 400;
            line-height: 1.5;
        }

        /* ===== Tipografi — Atkinson Hyperlegible Next ===== */

        /* H1 — Judul Utama / Hero Banner: 2rem–3rem, Bold 700 / Extra Bold 800 */
        h1 {
            font-size: 2.5rem;   /* 40px */
            font-weight: 800;
            line-height: 1.2;
        }

        /* H2 — Judul Sekunder / Section Title: 1.5rem–2rem, Bold 700 / Semi Bold 600 */
        h2 {
            font-size: 1.75rem;  /* 28px */
            font-weight: 700;
            line-height: 1.3;
        }

        /* H3 — Sub-Judul: 1.25rem–1.5rem, Semi Bold 600 / Medium 500 */
        h3 {
            font-size: 1.25rem;  /* 20px */
            font-weight: 600;
            line-height: 1.4;
        }

        /* H4, H5, H6 — Judul Kecil: 1rem–1.125rem, Medium 500 / Semi Bold 600 */
        h4, h5, h6 {
            font-size: 1.0rem;   /* 16px */
            font-weight: 500;
            line-height: 1.4;
        }

        /* Body Text / Teks Utama: 1rem (16px), Regular 400 */
        p, span, div, td, th, li, a, label, button, input, select, textarea {
            font-family: 'Atkinson Hyperlegible Next', sans-serif;
        }

        /* Teks Pendukung / Sub-text: 0.875rem (14px), Regular 400 */
        small, .text-sm {
            font-size: 0.875rem;
        }

        /* Teks Kecil / Caption / Badge: 0.75rem–0.8125rem (12px–13px), Regular 400 / Medium 500 */
        .text-xs {
            font-size: 0.75rem;
        }

        /* Hilangkan panah spinner atas-bawah pada input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false, quickModalOpen: false, barcodeScannerOpen: false }">

    <div class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col justify-between shrink-0 transition-transform duration-300 lg:sticky lg:top-0 lg:translate-x-0 h-screen overflow-y-auto">
            <div class="p-4">
                <!-- Logo & Brand Header -->
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-700 font-bold text-xl shrink-0">
                            <i data-lucide="store" class="w-5 h-5 text-brand-700"></i>
                        </div>
                        <div class="overflow-hidden min-w-0">
                            <h1 class="font-extrabold text-base text-slate-900 leading-tight tracking-tight truncate" title="{{ $appSettings['store_name'] ?? 'Toko Kelontong' }}">
                                {{ $appSettings['store_name'] ?? 'Toko Kelontong' }}
                            </h1>
                            <p class="text-[11px] text-slate-400 font-medium truncate">Inventaris & Kasir</p>
                        </div>
                    </div>
                    <!-- Close Sidebar Button (Mobile) -->
                    <button @click="sidebarOpen = false" class="lg:hidden p-2 text-slate-400 hover:bg-slate-100 rounded-lg shrink-0">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Navigation Section -->
                <div class="mb-2">
                    <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3">MENU UTAMA</span>
                </div>

                <nav class="space-y-0.5">
                    <!-- Kasir POS (Accessible by Kasir & Admin) -->
                    <a href="{{ route('kasir.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('kasir.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="monitor" class="w-4.5 h-4.5 {{ request()->routeIs('kasir.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Kasir POS</span>
                    </a>

                    @if(auth()->user() && auth()->user()->isAdmin())
                    <!-- Pelanggan & Suplier (Admin Only) -->
                    <a href="{{ route('pelanggan.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('pelanggan.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="users" class="w-4.5 h-4.5 {{ request()->routeIs('pelanggan.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Pelanggan & Suplier</span>
                    </a>

                    <!-- Riwayat Transaksi & Laporan (Admin Only) -->
                    <a href="{{ route('transaksi.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('transaksi.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="receipt" class="w-4.5 h-4.5 {{ request()->routeIs('transaksi.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    @endif

                    <div class="pt-2 pb-1">
                        <span class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3">DATA & KELOLA</span>
                    </div>

                    <!-- Dashboard (Both Kasir & Admin) -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="layout-grid" class="w-4.5 h-4.5 {{ request()->routeIs('dashboard') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Dashboard</span>
                    </a>

                    <!-- Catat Stok Masuk (Accessible by Kasir & Admin - Now in DATA & KELOLA) -->
                    <a href="{{ route('stok.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('stok.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="package-plus" class="w-4.5 h-4.5 {{ request()->routeIs('stok.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Catat Stok Masuk</span>
                    </a>

                    @if(auth()->user() && auth()->user()->isAdmin())
                    <!-- Master Stok Barang (Admin Only) -->
                    <a href="{{ route('products.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('products.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="package-search" class="w-4.5 h-4.5 {{ request()->routeIs('products.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Master Data Barang</span>
                        @php
                            $lowStockCount = \App\Models\Product::where(function($q) { $q->whereRaw('stock <= min_stock'); })->count();
                            $nearExpiryCount = \App\Models\Product::whereNotNull('expiry_date')->where('expiry_date', '<=', \Carbon\Carbon::now()->addDays(30))->count();
                            $alertTotal = $lowStockCount + $nearExpiryCount;
                        @endphp
                        @if($alertTotal > 0)
                        <span class="ml-auto bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ $alertTotal }}</span>
                        @endif
                    </a>

                    <!-- Manajemen Akun (Admin Only) -->
                    <a href="{{ route('pengguna.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('pengguna.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="user-cog" class="w-4.5 h-4.5 {{ request()->routeIs('pengguna.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Manajemen Akun</span>
                    </a>

                    <!-- Pengaturan Toko (Admin Only) -->
                    <a href="{{ route('pengaturan.index') }}" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('pengaturan.*') ? 'bg-brand-100/70 text-brand-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="sliders" class="w-4.5 h-4.5 {{ request()->routeIs('pengaturan.*') ? 'text-brand-700' : 'text-slate-400' }}"></i>
                        <span>Pengaturan</span>
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Sidebar Bottom Profile Section -->
            <div class="p-4 border-t border-slate-100 space-y-3">
                <!-- Toko Status Pill -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-2.5 flex items-center justify-between text-xs font-semibold gap-2">
                    <div class="flex items-center gap-2 text-slate-700 min-w-0">
                        @php
                            $status = $appSettings['store_status'] ?? 'Buka (Operasional Normal)';
                            $isOpen = str_starts_with($status, 'Buka');
                        @endphp
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                        <span class="truncate font-medium">{{ $appSettings['store_name'] ?? 'Toko Berkah' }}</span>
                    </div>
                    <span class="text-brand-700 font-bold bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200 shrink-0 text-[11px]">{{ $isOpen ? 'Buka' : ($status === 'Maintenance' ? 'Maint' : 'Tutup') }}</span>
                </div>

                <!-- User Profile Card -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-800 border border-brand-200 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ Auth::user() && Auth::user()->isAdmin() ? 'bg-indigo-500' : 'bg-emerald-500' }}"></span>
                                <p class="text-[10px] font-semibold uppercase tracking-wider {{ Auth::user() && Auth::user()->isAdmin() ? 'text-indigo-600' : 'text-emerald-600' }}">
                                    {{ Auth::user() && Auth::user()->isAdmin() ? 'Admin Toko' : 'Kasir POS' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Keluar / Ganti Shift" class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition-colors shrink-0">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navigation Header -->
            <header class="bg-white border-b border-slate-200 px-4 md:px-8 py-3.5 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Button -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <!-- Search Bar -->
                    <div class="relative w-full max-w-md hidden md:block">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" 
                               placeholder="Cari barang, SKU, atau barcode..." 
                               class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all">
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3 md:gap-4">
                    <!-- Date & Shift Info -->
                    <div class="hidden lg:flex items-center gap-2 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200/80 px-3 py-2 rounded-xl">
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        <span>{{ now()->locale('id')->isoFormat('dddd, D MMM YYYY') }}</span>
                    </div>

                    <!-- Primary Action Button: + Catat Stok -->
                    <a href="{{ route('stok.index') }}" 
                       class="bg-brand-dark hover:bg-brand-800 text-white font-semibold text-sm px-3 md:px-4 py-2 rounded-xl flex items-center gap-2 shadow-sm transition-all hover:shadow">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="hidden md:inline">Catat Stok</span>
                    </a>

                    <!-- Notification Bell with Expiring Products -->
                    @php
                        $expiringProducts = \App\Models\Product::whereNotNull('expiry_date')
                            ->where('expiry_date', '<=', \Carbon\Carbon::now()->addDays(30))
                            ->orderBy('expiry_date', 'asc')
                            ->get();
                        
                        $lowStockProducts = \App\Models\Product::whereRaw('stock <= min_stock')
                            ->orderByRaw('stock - min_stock ASC')
                            ->get();
                        
                        $totalNotifCount = $expiringProducts->count() + $lowStockProducts->count();
                        $hasNotifications = $totalNotifCount > 0;
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                                class="relative p-2 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-all">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @if($hasNotifications)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black ring-2 ring-white px-1">{{ $totalNotifCount }}</span>
                            @endif
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div x-show="open" x-cloak
                             class="absolute right-0 mt-2 w-96 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                <h4 class="font-bold text-slate-800 text-sm">Notifikasi</h4>
                                @if($hasNotifications)
                                <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ $totalNotifCount }} Peringatan</span>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @if($hasNotifications)
                                    {{-- Low Stock Section --}}
                                    @if($lowStockProducts->count() > 0)
                                    <div class="px-4 py-2 bg-amber-50/70 border-b border-amber-100">
                                        <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider flex items-center gap-1">
                                            <i data-lucide="package-x" class="w-3 h-3"></i>
                                            Stok Menipis ({{ $lowStockProducts->count() }})
                                        </span>
                                    </div>
                                    @foreach($lowStockProducts->take(5) as $lp)
                                    <div class="p-3 border-b border-slate-50 hover:bg-slate-50 flex items-start gap-3 transition-colors">
                                        <div class="mt-0.5 {{ $lp->stock <= 0 ? 'text-red-500 bg-red-50' : 'text-amber-500 bg-amber-50' }} p-1.5 rounded-lg shrink-0">
                                            <i data-lucide="package-x" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">{{ $lp->name }}</p>
                                            <p class="text-[11px] font-medium {{ $lp->stock <= 0 ? 'text-red-600' : 'text-amber-700' }} mt-0.5">
                                                {{ $lp->stock <= 0 ? 'STOK HABIS' : 'Stok menipis — perlu restock' }}
                                            </p>
                                            <p class="text-[10px] text-slate-500 mt-0.5">Sisa: {{ $lp->stock }} {{ $lp->unit }} (min: {{ $lp->min_stock }})</p>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif

                                    {{-- Expiring Products Section --}}
                                    @if($expiringProducts->count() > 0)
                                    <div class="px-4 py-2 bg-rose-50/70 border-b border-rose-100">
                                        <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider flex items-center gap-1">
                                            <i data-lucide="calendar-clock" class="w-3 h-3"></i>
                                            Hampir/Sudah Kadaluarsa ({{ $expiringProducts->count() }})
                                        </span>
                                    </div>
                                    @foreach($expiringProducts->take(5) as $ep)
                                        @php 
                                            $isExpired = \Carbon\Carbon::parse($ep->expiry_date)->isPast();
                                        @endphp
                                    <div class="p-3 border-b border-slate-50 hover:bg-slate-50 flex items-start gap-3 transition-colors">
                                        <div class="mt-0.5 {{ $isExpired ? 'text-red-500 bg-red-50' : 'text-amber-500 bg-amber-50' }} p-1.5 rounded-lg shrink-0">
                                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-900">{{ $ep->name }}</p>
                                            <p class="text-[11px] font-medium {{ $isExpired ? 'text-red-600' : 'text-amber-700' }} mt-0.5">
                                                {{ $isExpired ? 'Kadaluarsa pada' : 'Akan kadaluarsa pada' }} 
                                                {{ \Carbon\Carbon::parse($ep->expiry_date)->format('d M Y') }}
                                            </p>
                                            <p class="text-[10px] text-slate-500 mt-0.5">Sisa stok: {{ $ep->stock }} {{ $ep->unit }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                @else
                                    <div class="py-6 flex flex-col items-center justify-center text-slate-400">
                                        <i data-lucide="check-circle-2" class="w-8 h-8 mb-2 text-emerald-400"></i>
                                        <p class="text-xs font-medium">Semua stok aman, tidak ada peringatan.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Avatar Dropdown -->
                    <div class="flex items-center gap-1 cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120" 
                             alt="Profile" class="w-9 h-9 rounded-full object-cover border border-brand-500/40">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden sm:block"></i>
                    </div>
                </div>
            </header>

            <!-- Main Page View Content -->
            <main class="flex-1 p-4 md:p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold shadow-xs">
                        <div class="flex items-center gap-2 mb-2 font-bold text-red-900">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                            <span>Terjadi kesalahan input:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-red-700 ml-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
