<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the settings configuration page.
     */
    public function index()
    {
        $settings = Setting::getAll();

        return view('pengaturan.index', compact('settings'));
    }

    /**
     * Update store and cashier settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_status' => 'required|string|max:100',
            'cashier_name' => 'required|string|max:255',
            'work_shift' => 'required|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
        ], [
            'store_name.required' => 'Nama toko wajib diisi.',
            'store_status.required' => 'Status operasional toko wajib dipilih.',
            'cashier_name.required' => 'Nama kasir aktif wajib diisi.',
            'work_shift.required' => 'Shift kerja wajib diisi.',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan toko berhasil diperbarui!');
    }
}
