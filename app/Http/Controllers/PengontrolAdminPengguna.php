<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Pengontrol Manajemen Pengguna (Admin Panel)
 *
 * CRUD pengguna oleh Super Admin & Administrator.
 */
class PengontrolAdminPengguna extends Controller
{
    /**
     * Daftar semua pengguna aktif dan non-aktif.
     */
    public function indeks(Request $request)
    {
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->adalahAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator.');
        }

        $query = Pengguna::with('peran')->withTrashed();

        // Filter berdasarkan peran
        if ($request->filled('peran')) {
            $query->whereHas('peran', fn($q) => $q->where('slug', $request->peran));
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->whereNull('deleted_at')->where('status', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('status', false);
            } elseif ($request->status === 'dihapus') {
                $query->onlyTrashed();
            }
        }

        // Pencarian nama / email
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        $pengguna = $query->latest()->paginate(15)->withQueryString();
        $semuaPeran = Peran::orderBy('name')->get();

        return view('admin.pengguna.indeks', compact('pengguna', 'semuaPeran'));
    }

    /**
     * Tampilkan form tambah pengguna baru.
     */
    public function buat()
    {
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->adalahAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        $semuaPeran = Peran::orderBy('name')->get();
        return view('admin.pengguna.buat', compact('semuaPeran'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function simpan(Request $request)
    {
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->adalahAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:pengguna,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
            'peran_id' => 'required|exists:peran,id',
            'status'   => 'sometimes|boolean',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.unique'      => 'Email ini sudah digunakan oleh pengguna lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'peran_id.required' => 'Peran pengguna wajib dipilih.',
        ]);

        $pengguna = Pengguna::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'status'             => $request->boolean('status', true),
            'email_verified_at'  => now(),
        ]);

        $pengguna->peran()->attach($validated['peran_id'], ['ditetapkan_pada' => now()]);

        return redirect()->route('admin.pengguna.indeks')
            ->with('sukses', "Pengguna '{$pengguna->name}' berhasil ditambahkan ke sistem.");
    }

    /**
     * Tampilkan form edit pengguna.
     */
    public function edit(int $id)
    {
        $pengguna = Pengguna::with('peran')->withTrashed()->findOrFail($id);

        // Super Admin tidak bisa diedit oleh Admin biasa
        if ($pengguna->adalahSuperAdmin() && !Auth::user()->adalahSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit akun Super Administrator.');
        }

        $semuaPeran = Peran::orderBy('name')->get();
        return view('admin.pengguna.edit', compact('pengguna', 'semuaPeran'));
    }

    /**
     * Update data pengguna.
     */
    public function perbarui(Request $request, int $id)
    {
        $pengguna = Pengguna::withTrashed()->findOrFail($id);

        if ($pengguna->adalahSuperAdmin() && !Auth::user()->adalahSuperAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah akun Super Administrator.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => "required|email|unique:pengguna,email,{$id}",
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'peran_id' => 'required|exists:peran,id',
            'status'   => 'sometimes|boolean',
        ]);

        $data = [
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'status' => $request->boolean('status', true),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $pengguna->update($data);
        $pengguna->peran()->sync([$validated['peran_id'] => ['ditetapkan_pada' => now()]]);

        return redirect()->route('admin.pengguna.indeks')
            ->with('sukses', "Data pengguna '{$pengguna->name}' berhasil diperbarui.");
    }

    /**
     * Nonaktifkan (soft delete) pengguna.
     */
    public function hapus(int $id)
    {
        if (!Auth::user()->adalahSuperAdmin() && !Auth::user()->adalahAdmin()) {
            abort(403, 'Akses ditolak.');
        }
        if (Auth::id() === $id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $pengguna = Pengguna::findOrFail($id);

        if ($pengguna->adalahSuperAdmin() && !Auth::user()->adalahSuperAdmin()) {
            abort(403, 'Tidak dapat menghapus Super Administrator.');
        }

        $pengguna->update(['status' => false]);
        $pengguna->delete();

        return redirect()->route('admin.pengguna.indeks')
            ->with('sukses', "Pengguna '{$pengguna->name}' berhasil dinonaktifkan.");
    }

    /**
     * Pulihkan akun pengguna yang telah di-soft-delete.
     */
    public function pulihkan(int $id)
    {
        $pengguna = Pengguna::onlyTrashed()->findOrFail($id);
        $pengguna->restore();
        $pengguna->update(['status' => true]);

        return redirect()->route('admin.pengguna.indeks')
            ->with('sukses', "Akun '{$pengguna->name}' berhasil dipulihkan.");
    }
}
