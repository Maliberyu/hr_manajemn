<?php

namespace App\Http\Controllers;

use App\Models\AtasanPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user    = auth()->user();
        $pegawai = $user->pegawai;

        $atasan = null;
        if ($user->nik) {
            $atasan = AtasanPegawai::where('nik', $user->nik)
                ->with('atasan')
                ->first()
                ?->atasan;
        }

        // Daftar calon atasan: semua user aktif kecuali diri sendiri
        $calonAtasan = User::where('id', '!=', $user->id)
            ->whereIn('role', ['atasan', 'hrd', 'admin'])
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'jabatan', 'role']);

        return view('profil.index', compact('user', 'pegawai', 'atasan', 'calonAtasan'));
    }

    // ── Cari pegawai SIK via AJAX ─────────────────────────────────────────────

    public function searchPegawai(Request $request)
    {
        $q = $request->q;
        $pegawai = Pegawai::aktif()
            ->where(fn($query) =>
                $query->where('nama', 'like', "%{$q}%")
                      ->orWhere('nik',  'like', "%{$q}%")
            )
            ->limit(10)
            ->get(['id', 'nik', 'nama', 'jbtn', 'departemen', 'photo']);

        return response()->json($pegawai->map(fn($p) => [
            'nik'    => $p->nik,
            'nama'   => $p->nama,
            'jbtn'   => $p->jbtn,
            'foto'   => $p->foto_url,
        ]));
    }

    // ── Link ke pegawai SIK ───────────────────────────────────────────────────

    public function linkPegawai(Request $request)
    {
        $request->validate(['nik' => 'required|exists:pegawai,nik']);

        // Cek apakah NIK sudah dipakai user lain
        $existing = User::where('nik', $request->nik)
            ->where('id', '!=', auth()->id())
            ->exists();

        if ($existing) {
            return back()->withErrors(['nik' => 'NIK ini sudah terhubung ke akun lain.']);
        }

        auth()->user()->update(['nik' => $request->nik]);

        return back()->with('success', 'Data pegawai berhasil dihubungkan.');
    }

    // ── Upload / ganti foto profil ────────────────────────────────────────────

    public function updateFoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user    = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return back()->withErrors(['photo' => 'Akun belum terhubung ke data pegawai.']);
        }

        // Hapus foto lama
        if ($pegawai->photo && Storage::disk('public')->exists($pegawai->photo)) {
            Storage::disk('public')->delete($pegawai->photo);
        }

        // Simpan foto baru
        $filename = 'pegawai/foto/' . $pegawai->nik . '_' . time() . '.jpg';

        if (!extension_loaded('gd')) {
            Storage::disk('public')->put($filename, file_get_contents($request->file('photo')->getRealPath()));
        } else {
            $file = $request->file('photo');
            $mime = $file->getMimeType();
            $src  = match ($mime) {
                'image/png'  => \imagecreatefrompng($file->getRealPath()),
                'image/webp' => \imagecreatefromwebp($file->getRealPath()),
                default      => \imagecreatefromjpeg($file->getRealPath()),
            };
            $srcW = \imagesx($src);
            $srcH = \imagesy($src);
            $size = 400;
            $cropX = $srcW > $srcH ? (int)(($srcW - $srcH) / 2) : 0;
            $cropY = $srcH > $srcW ? (int)(($srcH - $srcW) / 2) : 0;
            $cropS = min($srcW, $srcH);
            $dst = \imagecreatetruecolor($size, $size);
            \imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $size, $size, $cropS, $cropS);
            ob_start();
            \imagejpeg($dst, null, 85);
            $data = ob_get_clean();
            \imagedestroy($src);
            \imagedestroy($dst);
            Storage::disk('public')->put($filename, $data);
        }

        $pegawai->update(['photo' => $filename]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    // ── Set / ganti atasan langsung ───────────────────────────────────────────

    public function updateAtasan(Request $request)
    {
        $request->validate([
            'atasan_user_id' => 'required|exists:users_hr,id|different:id',
        ]);

        $user    = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return back()->withErrors(['atasan_user_id' => 'Akun belum terhubung ke data pegawai.']);
        }

        AtasanPegawai::updateOrCreate(
            ['nik' => $pegawai->nik],
            ['user_id' => $request->atasan_user_id, 'keterangan' => 'Diset sendiri oleh pegawai']
        );

        return back()->with('success', 'Atasan langsung berhasil disimpan.');
    }

    // ── Ganti password ────────────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|confirmed',
        ], [
            'password_baru.min'       => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => bcrypt($request->password_baru)]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
