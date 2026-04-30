<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingPeserta;
use App\Models\Sertifikasi;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:training.view')->only(['index', 'show', 'sertifikasi']);
        $this->middleware('permission:training.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
        $this->middleware('permission:training.peserta')->only(['storePeserta', 'updateStatusPeserta', 'destroyPeserta']);
    }

    // ─── Index semua training ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $training = Training::with('dibuatOleh')
            ->withCount('peserta')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->jenis, fn($q, $j) => $q->where('jenis', $j))
            ->when($request->q, fn($q, $s) => $q->where('nama_training', 'like', "%{$s}%"))
            ->orderByDesc('tanggal_mulai')
            ->paginate(20)->withQueryString();

        return view('training.index', compact('training'));
    }

    // ─── Tambah training baru ─────────────────────────────────────────────────

    public function create()
    {
        return view('training.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_training'   => 'required|max:200',
            'penyelenggara'   => 'required|max:100',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi'          => 'required|max:150',
            'jenis'           => 'required|in:' . implode(',', Training::JENIS),
            'biaya'           => 'nullable|numeric|min:0',
            'kuota'           => 'nullable|integer|min:1',
            'deskripsi'       => 'nullable|max:2000',
        ]);

        Training::create([...$validated, 'status' => 'rencana', 'dibuat_oleh' => auth()->id()]);

        return redirect()->route('training.index')
            ->with('success', "Training {$validated['nama_training']} berhasil ditambahkan.");
    }

    // ─── Detail training + peserta ────────────────────────────────────────────

    public function show(Training $training)
    {
        $training->load('dibuatOleh');

        $peserta = TrainingPeserta::with('pegawai.departemenRef')
            ->where('training_id', $training->id)
            ->orderBy('created_at')
            ->get();

        $pegawaiTerdaftar = $peserta->pluck('pegawai_id');
        $pegawaiBelum = Pegawai::aktif()
            ->whereNotIn('id', $pegawaiTerdaftar)
            ->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn']);

        return view('training.show', compact('training', 'peserta', 'pegawaiBelum'));
    }

    // ─── Edit & Update training ───────────────────────────────────────────────

    public function edit(Training $training)
    {
        return view('training.edit', compact('training'));
    }

    public function update(Request $request, Training $training)
    {
        $validated = $request->validate([
            'nama_training'   => 'required|max:200',
            'penyelenggara'   => 'required|max:100',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi'          => 'required|max:150',
            'jenis'           => 'required|in:' . implode(',', Training::JENIS),
            'status'          => 'required|in:' . implode(',', Training::STATUS),
            'biaya'           => 'nullable|numeric|min:0',
            'kuota'           => 'nullable|integer|min:1',
            'deskripsi'       => 'nullable|max:2000',
        ]);

        $training->update($validated);
        return redirect()->route('training.show', $training)->with('success', 'Data training diperbarui.');
    }

    // ─── Daftarkan pegawai sebagai peserta ────────────────────────────────────

    public function storePeserta(Request $request, Training $training)
    {
        $request->validate([
            'pegawai_ids'   => 'required|array',
            'pegawai_ids.*' => 'exists:pegawai,id',
        ]);

        // Cek kuota
        if ($training->kuota) {
            $terdaftar = TrainingPeserta::where('training_id', $training->id)->count();
            $sisa      = $training->kuota - $terdaftar;
            if (count($request->pegawai_ids) > $sisa) {
                return back()->withErrors(['kuota' => "Kuota training hanya tersisa {$sisa} slot."]);
            }
        }

        foreach ($request->pegawai_ids as $pegawaiId) {
            TrainingPeserta::firstOrCreate([
                'training_id' => $training->id,
                'pegawai_id'  => $pegawaiId,
            ], ['status' => 'terdaftar']);
        }

        return back()->with('success', count($request->pegawai_ids) . ' peserta berhasil didaftarkan.');
    }

    // ─── Update status peserta (terdaftar/hadir/selesai) ────────────────────

    public function updateStatusPeserta(Request $request, Training $training, TrainingPeserta $peserta)
    {
        $request->validate([
            'status'          => 'required|in:terdaftar,hadir,selesai',
            'nilai'           => 'nullable|numeric|min:0|max:100',
            'sertifikat_file' => 'nullable|file|mimes:pdf|max:3072',
        ]);

        $data = $request->only('status', 'nilai');

        if ($request->hasFile('sertifikat_file')) {
            if ($peserta->sertifikat_file) {
                Storage::disk('public')->delete($peserta->sertifikat_file);
            }
            $data['sertifikat_file'] = $request->file('sertifikat_file')
                ->store("training/sertifikat/{$training->id}", 'public');
        }

        $peserta->update($data);

        // Jika selesai + ada nilai, otomatis buat record sertifikasi
        if ($request->status === 'selesai' && $request->hasFile('sertifikat_file')) {
            Sertifikasi::updateOrCreate(
                [
                    'pegawai_id'     => $peserta->pegawai_id,
                    'nama_sertifikat'=> $training->nama_training,
                ],
                [
                    'lembaga'        => $training->penyelenggara,
                    'tanggal_terbit' => $training->tanggal_selesai,
                    'file_sertifikat'=> $data['sertifikat_file'] ?? null,
                    'status'         => 'aktif',
                ]
            );
        }

        return back()->with('success', "Status peserta {$peserta->pegawai->nama} diperbarui.");
    }

    // ─── Hapus peserta ────────────────────────────────────────────────────────

    public function destroyPeserta(Training $training, TrainingPeserta $peserta)
    {
        if ($peserta->sertifikat_file) Storage::disk('public')->delete($peserta->sertifikat_file);
        $peserta->delete();
        return back()->with('success', 'Peserta dihapus dari training.');
    }

    // ─── Halaman sertifikasi semua pegawai ────────────────────────────────────

    public function sertifikasi(Request $request)
    {
        $sertifikasi = Sertifikasi::with('pegawai.departemenRef')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) =>
                $q->where('nama_sertifikat', 'like', "%{$s}%")
                  ->orWhereHas('pegawai', fn($p) => $p->cari($s)))
            ->orderByDesc('tanggal_terbit')
            ->paginate(25)->withQueryString();

        return view('training.sertifikasi', compact('sertifikasi'));
    }

    // ─── Download sertifikat ─────────────────────────────────────────────────

    public function downloadSertifikat(TrainingPeserta $peserta)
    {
        abort_unless($peserta->sertifikat_file, 404);
        return Storage::disk('public')->download(
            $peserta->sertifikat_file,
            "Sertifikat_{$peserta->pegawai->nama}_{$peserta->training->nama_training}.pdf"
        );
    }

    public function destroy(Training $training)
    {
        $training->update(['status' => 'dibatalkan']);
        return redirect()->route('training.index')->with('success', 'Training dibatalkan.');
    }
}
