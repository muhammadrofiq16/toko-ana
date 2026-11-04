<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar akun pengguna
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $roleFilter = $request->get('role', 'all');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        // Metric Counts
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $kasirCount = User::where('role', 'kasir')->count();

        return view('users.index', compact('users', 'search', 'roleFilter', 'totalUsers', 'adminCount', 'kasirCount'));
    }

    /**
     * Simpan akun pengguna baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', 'string', 'in:admin,kasir'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email tersebut sudah digunakan oleh akun lain.',
            'role.required'     => 'Peran/Role wajib dipilih.',
            'role.in'           => 'Role hanya boleh Admin atau Kasir.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 6 karakter.',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Akun ' . $validated['name'] . ' (' . ucfirst($validated['role']) . ') berhasil ditambahkan!');
    }

    /**
     * Perbarui akun pengguna (Nama, Email, Role, dan Password opsional)
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', 'string', 'in:admin,kasir'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email tersebut sudah digunakan oleh akun lain.',
            'role.required'     => 'Peran/Role wajib dipilih.',
            'role.in'           => 'Role hanya boleh Admin atau Kasir.',
            'password.min'      => 'Password minimal harus 6 karakter.',
        ]);

        // Keamanan: Jika mengubah role admin menjadi kasir, pastikan tidak menghilangkan admin terakhir
        if ($user->isAdmin() && $validated['role'] === 'kasir') {
            $totalAdmins = User::where('role', 'admin')->count();
            if ($totalAdmins <= 1) {
                return back()->with('error', 'Tidak dapat mengubah role akun ini menjadi Kasir karena merupakan satu-satunya Admin di sistem.');
            }
        }

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('pengguna.index')->with('success', 'Akun ' . $user->name . ' berhasil diperbarui!');
    }

    /**
     * Hapus akun pengguna
     */
    public function destroy(User $user)
    {
        // Keamanan 1: Admin tidak boleh menghapus akun dirinya sendiri yang sedang login
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        // Keamanan 2: Tidak boleh menghapus admin jika hanya tersisa 1 admin
        if ($user->isAdmin()) {
            $totalAdmins = User::where('role', 'admin')->count();
            if ($totalAdmins <= 1) {
                return back()->with('error', 'Tidak dapat menghapus akun ini karena merupakan satu-satunya Admin di sistem.');
            }
        }

        $deletedName = $user->name;
        $user->delete();

        return redirect()->route('pengguna.index')->with('success', 'Akun ' . $deletedName . ' berhasil dihapus dari sistem.');
    }
}
