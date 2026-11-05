<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $appSettings['store_name'] ?? 'SIKOL Toko' }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden" x-data="loginForm()">

    <!-- Background Decorative Elements -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-200/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-200/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">

        <!-- Brand Card Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-700 to-brand-500 text-white shadow-lg shadow-brand-500/20 mb-3">
                <i data-lucide="store" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ strtoupper($appSettings['store_name'] ?? 'SIKOL POS') }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">SIKOL POS &bull; Sistem Informasi Kasir & Inventaris Toko</p>
        </div>

        <!-- Main Login Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xl p-7 space-y-6">

            <!-- Flash Status Messages -->
            @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center gap-2.5 text-xs text-blue-700 font-medium">
                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 flex items-center gap-2.5 text-xs text-rose-700 font-medium">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-xs text-rose-700 space-y-1">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                    <span>Terjadi kesalahan:</span>
                </div>
                <ul class="list-disc list-inside pl-1 text-[11px] space-y-0.5">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               x-model="email"
                               required 
                               placeholder="nama@toko.com" 
                               class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input :type="showPassword ? 'text' : 'password'" 
                               id="password" 
                               name="password" 
                               x-model="password"
                               required 
                               placeholder="••••••••" 
                               class="w-full bg-slate-50/80 border border-slate-200 rounded-xl pl-10 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all font-medium">
                        <button type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i data-lucide="eye" x-show="!showPassword" class="w-4 h-4"></i>
                            <i data-lucide="eye-off" x-show="showPassword" class="w-4 h-4" style="display: none;"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                        <span class="text-xs font-medium text-slate-600">Ingat perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-brand-dark hover:bg-brand-800 active:bg-brand-900 text-white font-bold py-2.5 px-4 rounded-xl shadow-md hover:shadow-lg shadow-brand-700/20 transition-all flex items-center justify-center gap-2 text-sm mt-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="pt-4 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-2.5">
                    Pilihan Akun Demo (Klik untuk Isi Cepat)
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" 
                            @click="fillCredentials('admin@toko.com', 'password')"
                            class="p-2.5 bg-slate-50 hover:bg-brand-50 border border-slate-200 hover:border-brand-300 rounded-xl text-left transition-all group">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 group-hover:text-brand-800">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-brand-600"></i>
                            <span>Admin Toko</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">admin@toko.com</p>
                    </button>

                    <button type="button" 
                            @click="fillCredentials('kasir@toko.com', 'password')"
                            class="p-2.5 bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 rounded-xl text-left transition-all group">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 group-hover:text-emerald-800">
                            <i data-lucide="monitor" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Kasir POS</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5 truncate">kasir@toko.com</p>
                    </button>
                </div>
            </div>

        </div>

        <!-- Footer Notice -->
        <p class="text-center text-xs text-slate-400 mt-6 font-medium">
            &copy; {{ date('Y') }} {{ $appSettings['store_name'] ?? 'SIKOL Toko Kelontong' }}. Seluruh hak cipta dilindungi.
        </p>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function loginForm() {
            return {
                email: '{{ old('email') }}',
                password: '',
                showPassword: false,
                fillCredentials(email, pass) {
                    this.email = email;
                    this.password = pass;
                    this.$nextTick(() => {
                        lucide.createIcons();
                    });
                }
            }
        }
    </script>
</body>
</html>
