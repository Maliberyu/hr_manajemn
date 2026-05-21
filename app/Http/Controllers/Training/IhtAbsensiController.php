<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\IHT;
use App\Models\IHTPeserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class IhtAbsensiController extends Controller
{
    // ── Generate signed URL untuk QR (dipanggil dari IHT show) ───────────────

    public function generateUrl(IHT $iht, string $jenis)
    {
        abort_unless(in_array($jenis, ['masuk', 'selesai']), 404);
        abort_unless(auth()->user()->hasRole(['hrd', 'admin']), 403);

        // Signed URL berlaku 24 jam
        $url = URL::signedRoute('iht.hadir.form', [
            'iht'   => $iht->id,
            'jenis' => $jenis,
        ], now()->addHours(24));

        return response()->json(['url' => $url]);
    }

    // ── Halaman konfirmasi (peserta buka dari HP setelah scan QR) ─────────────

    public function form(Request $request, IHT $iht, string $jenis)
    {
        abort_unless($request->hasValidSignature(), 403, 'Link tidak valid atau sudah kadaluarsa.');
        abort_unless(in_array($jenis, ['masuk', 'selesai']), 404);

        // Jika belum login → simpan intended URL lalu redirect ke login
        if (!auth()->check()) {
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu untuk mencatat absensi training.');
        }

        $user    = auth()->user();
        $pegawai = $user->pegawai;

        // User belum terhubung ke data pegawai
        if (!$pegawai) {
            return view('training.iht.hadir-error', [
                'iht'   => $iht,
                'jenis' => $jenis,
                'pesan' => 'Akun Anda belum terhubung ke data pegawai. Hubungi HRD.',
            ]);
        }

        // Cek apakah pegawai terdaftar sebagai peserta IHT ini
        $peserta = IHTPeserta::where('iht_id', $iht->id)
            ->where('pegawai_id', $pegawai->id)
            ->first();

        if (!$peserta) {
            return view('training.iht.hadir-error', [
                'iht'   => $iht,
                'jenis' => $jenis,
                'pesan' => 'Anda tidak terdaftar sebagai peserta training ini.',
            ]);
        }

        // Cek apakah sudah scan sebelumnya
        $sudahScan = $jenis === 'masuk'
            ? !is_null($peserta->check_in_at)
            : !is_null($peserta->check_out_at);

        if ($sudahScan) {
            $jam = $jenis === 'masuk'
                ? $peserta->check_in_at->format('H:i')
                : $peserta->check_out_at->format('H:i');
            return view('training.iht.hadir-error', [
                'iht'      => $iht,
                'jenis'    => $jenis,
                'pesan'    => "Anda sudah absensi " . ($jenis === 'masuk' ? 'masuk' : 'selesai') . " pukul {$jam}.",
                'sudah'    => true,
            ]);
        }

        // Jika scan selesai tapi belum masuk → warning tapi tetap boleh
        $belumMasuk = $jenis === 'selesai' && is_null($peserta->check_in_at);

        return view('training.iht.hadir', compact('iht', 'peserta', 'jenis', 'belumMasuk', 'request'));
    }

    // ── POST: catat absensi ───────────────────────────────────────────────────

    public function simpan(Request $request, IHT $iht, string $jenis)
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless(in_array($jenis, ['masuk', 'selesai']), 404);
        abort_unless(auth()->check(), 401);

        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403, 'Akun belum terhubung ke data pegawai.');

        $peserta = IHTPeserta::where('iht_id', $iht->id)
            ->where('pegawai_id', $pegawai->id)
            ->firstOrFail();

        if ($jenis === 'masuk') {
            abort_if(!is_null($peserta->check_in_at), 422, 'Sudah absensi masuk.');
            $peserta->update([
                'check_in_at' => now(),
                'status'      => 'hadir',
            ]);
            $jam = now()->format('H:i');
            $pesan = "Absensi masuk berhasil dicatat pukul {$jam}.";
        } else {
            abort_if(!is_null($peserta->check_out_at), 422, 'Sudah absensi selesai.');
            $peserta->update([
                'check_out_at' => now(),
                'status'       => 'selesai',
            ]);
            $jam = now()->format('H:i');
            $pesan = "Absensi selesai berhasil dicatat pukul {$jam}.";
        }

        return view('training.iht.hadir-sukses', compact('iht', 'peserta', 'jenis', 'jam', 'pesan'));
    }
}
