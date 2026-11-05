@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    {{-- Page Header --}}
    <div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
            <span>SISTEM</span>
            <span>•</span>
            <span class="text-slate-500">KONFIGURASI</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Pengaturan Toko & Kasir</h2>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola informasi toko, shift kerja, dan kontak untuk struk kasir.</p>
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
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3" x-data="{ show: true }" x-show="show" x-transition>
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
        <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
        <button @click="show = false" class="ml-auto text-red-400 hover:text-red-700">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center gap-2 text-red-800 font-bold text-sm mb-1">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            <span>Terdapat beberapa kesalahan input:</span>
        </div>
        <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5 ml-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Settings --}}
    <form action="{{ route('pengaturan.update') }}" method="POST" class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-6">
        @csrf

        {{-- Section 1: Informasi Toko --}}
        <div>
            <h3 class="font-bold text-slate-900 text-base pb-3 border-b border-slate-100 flex items-center gap-2">
                <i data-lucide="store" class="w-4.5 h-4.5 text-brand-700"></i>
                <span>Informasi Toko</span>
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="store_name" 
                           value="{{ old('store_name', $settings['store_name'] ?? 'Toko Berkah Kelontong') }}" 
                           required
                           placeholder="Contoh: Toko Ana, Toko Berkah"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('store_name') border-red-300 bg-red-50/50 @enderror">
                    @error('store_name')
                        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Status Operasional <span class="text-red-500">*</span>
                    </label>
                    @php
                        $currentStatus = old('store_status', $settings['store_status'] ?? 'Buka (Operasional Normal)');
                    @endphp
                    <select name="store_status" 
                            required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                        <option value="Buka (Operasional Normal)" {{ $currentStatus === 'Buka (Operasional Normal)' ? 'selected' : '' }}>Buka (Operasional Normal)</option>
                        <option value="Tutup Shift" {{ $currentStatus === 'Tutup Shift' ? 'selected' : '' }}>Tutup Shift</option>
                        <option value="Maintenance" {{ $currentStatus === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                    @error('store_status')
                        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Toko (Untuk Struk)</label>
                    <input type="text" 
                           name="store_address" 
                           value="{{ old('store_address', $settings['store_address'] ?? 'Jl. Contoh Alamat No. 123, Kota') }}" 
                           placeholder="Alamat lengkap toko"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon Toko (Untuk Struk)</label>
                    <input type="text" 
                           name="store_phone" 
                           value="{{ old('store_phone', $settings['store_phone'] ?? '021-12345678') }}" 
                           placeholder="Nomor telepon toko"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                </div>
            </div>
        </div>

        {{-- Section 2: Petugas & Shift Aktif --}}
        <div>
            <h3 class="font-bold text-slate-900 text-base pb-3 border-b border-slate-100 pt-2 flex items-center gap-2">
                <i data-lucide="user-check" class="w-4.5 h-4.5 text-brand-700"></i>
                <span>Petugas & Shift Current</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Kasir Active <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="cashier_name" 
                           value="{{ old('cashier_name', $settings['cashier_name'] ?? 'Budi Santoso') }}" 
                           required
                           placeholder="Nama kasir yang bertugas"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('cashier_name') border-red-300 bg-red-50/50 @enderror">
                    @error('cashier_name')
                        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Shift Kerja <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="work_shift" 
                           value="{{ old('work_shift', $settings['work_shift'] ?? 'Kasir Shift 1 (Pagi)') }}" 
                           required
                           placeholder="Contoh: Kasir Shift 1 (Pagi)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 @error('work_shift') border-red-300 bg-red-50/50 @enderror">
                    @error('work_shift')
                        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-400">Perubahan akan langsung berpengaruh ke sidebar dan struk kasir.</p>
            <button type="submit" class="bg-brand-dark hover:bg-brand-800 text-white font-bold text-xs px-6 py-2.5 rounded-xl transition-all shadow-sm hover:shadow flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Simpan Pengaturan</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
