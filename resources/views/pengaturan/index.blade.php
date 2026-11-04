@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
            <span>SISTEM</span>
            <span>•</span>
            <span class="text-slate-500">KONFIGURASI</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Pengaturan Toko & Kasir</h2>
        <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola informasi toko, shift kerja, dan koneksi barcode scanner.</p>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold">
        <div class="flex items-center gap-2 mb-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
            <span>Terdapat kesalahan pada input:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1 ml-7">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('pengaturan.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-6">
            <h3 class="font-bold text-slate-900 text-base pb-3 border-b border-slate-100">Informasi Toko</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="store_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Toko</label>
                    <input type="text" 
                           id="store_name"
                           name="store_name" 
                           value="{{ old('store_name', $settings['store_name']) }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all"
                           required>
                </div>
                <div>
                    <label for="store_status" class="block text-xs font-bold text-slate-700 mb-1">Status Operasional</label>
                    <select id="store_status"
                            name="store_status" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all">
                        <option value="Buka (Operasional Normal)" {{ old('store_status', $settings['store_status']) == 'Buka (Operasional Normal)' ? 'selected' : '' }}>Buka (Operasional Normal)</option>
                        <option value="Tutup Shift" {{ old('store_status', $settings['store_status']) == 'Tutup Shift' ? 'selected' : '' }}>Tutup Shift</option>
                        <option value="Maintenance" {{ old('store_status', $settings['store_status']) == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
            </div>

            <h3 class="font-bold text-slate-900 text-base pb-3 border-b border-slate-100 pt-4">Petugas & Shift Current</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="cashier_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Kasir Active</label>
                    <input type="text" 
                           id="cashier_name"
                           name="cashier_name" 
                           value="{{ old('cashier_name', $settings['cashier_name']) }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all"
                           required>
                </div>
                <div>
                    <label for="shift" class="block text-xs font-bold text-slate-700 mb-1">Shift Kerja</label>
                    <input type="text" 
                           id="shift"
                           name="shift" 
                           value="{{ old('shift', $settings['shift']) }}" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-all"
                           required>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-brand-dark hover:bg-brand-800 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition-all hover:shadow-md">
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
