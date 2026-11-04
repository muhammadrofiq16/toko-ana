<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name'   => Setting::get('store_name', 'Toko Berkah Kelontong'),
            'store_status' => Setting::get('store_status', 'Buka (Operasional Normal)'),
            'cashier_name' => Setting::get('cashier_name', 'Budi Santoso'),
            'shift'        => Setting::get('shift', 'Kasir Shift 1 (Pagi)'),
        ];

        return view('pengaturan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name'   => 'required|string|max:255',
            'store_status' => 'required|string|in:Buka (Operasional Normal),Tutup Shift,Maintenance',
            'cashier_name' => 'required|string|max:255',
            'shift'        => 'required|string|max:255',
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_status', $request->store_status);
        Setting::set('cashier_name', $request->cashier_name);
        Setting::set('shift', $request->shift);

        return redirect()->route('pengaturan.index')->with('success', 'Pengaturan berhasil disimpan!');
    }
}
