<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Karyawan\MasterKaryawanController;
use App\Http\Controllers\Karyawan\BerkasPegawaiController;
use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Absensi\LokasiAbsensiController;
use App\Http\Controllers\Cuti\CutiController;
use App\Http\Controllers\Shift\ShiftController;
use App\Http\Controllers\Kinerja\KinerjaController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Lembur\LemburController;
use App\Http\Controllers\Rekrutmen\RekrutmenController;
use App\Http\Controllers\Training\TrainingController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Pengaturan\UserController;

// ─── Auth (Breeze) ─────────────────────────────────────────────────────────────
// require __DIR__ . '/auth.php'; dihapus karena kita custom login/logout sendiri

// ─── Redirect root ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ── LOGIN --------──────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// ── Register --------──────────────────────────────────────────────────────────────
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// ═══════════════════════════════════════════════════════════════════════════════
// SEMUA ROUTE BUTUH LOGIN
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── ESS Portal (karyawan mandiri) ──────────────────────────────────────────
    Route::get('/ess', [DashboardController::class, 'ess'])->name('ess.dashboard');
    Route::post('/ess/checkin',  [AbsensiController::class, 'checkIn'])->name('ess.checkin');
    Route::post('/ess/checkout', [AbsensiController::class, 'checkOut'])->name('ess.checkout');
    Route::post('/ess/cuti',     [DashboardController::class, 'essStoreCuti'])->name('ess.cuti.store');

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: MASTER KARYAWAN
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('karyawan')->name('karyawan.')->group(function () {

        Route::resource('/', MasterKaryawanController::class)
            ->parameters(['' => 'karyawan'])
            ->names([
                'index'   => 'index',
                'create'  => 'create',
                'store'   => 'store',
                'show'    => 'show',
                'edit'    => 'edit',
                'update'  => 'update',
                'destroy' => 'destroy',
            ]);

        // Berkas / dokumen pegawai
        Route::prefix('{karyawan}/berkas')->name('berkas.')->group(function () {
            Route::get('/',                          [BerkasPegawaiController::class, 'index'])->name('index');
            Route::post('/',                         [BerkasPegawaiController::class, 'store'])->name('store');
            Route::get('/{berkas}/download',         [BerkasPegawaiController::class, 'download'])->name('download');
            Route::delete('/{berkas}',               [BerkasPegawaiController::class, 'destroy'])->name('destroy');
        });
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: ABSENSI
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/',                              [AbsensiController::class, 'index'])->name('index');
        Route::get('/buat',                          [AbsensiController::class, 'create'])->name('create');
        Route::post('/',                             [AbsensiController::class, 'store'])->name('store');
        Route::get('/rekap',                         [AbsensiController::class, 'rekap'])->name('rekap');
        Route::post('/rekap/generate',               [AbsensiController::class, 'generateRekap'])->name('rekap.generate');
        Route::get('/export',                        [AbsensiController::class, 'export'])->name('export');
        Route::get('/karyawan/{karyawan}',           [AbsensiController::class, 'show'])->name('show');
        Route::get('/{absensi}/edit',                [AbsensiController::class, 'edit'])->name('edit');
        Route::put('/{absensi}',                     [AbsensiController::class, 'update'])->name('update');

        // Manajemen lokasi GPS
        Route::prefix('lokasi')->name('lokasi.')->group(function () {
            Route::get('/',                          [LokasiAbsensiController::class, 'index'])->name('index');
            Route::post('/',                         [LokasiAbsensiController::class, 'store'])->name('store');
            Route::put('/{lokasi}',                  [LokasiAbsensiController::class, 'update'])->name('update');
            Route::delete('/{lokasi}',               [LokasiAbsensiController::class, 'destroy'])->name('destroy');
            Route::patch('/{lokasi}/toggle',         [LokasiAbsensiController::class, 'toggle'])->name('toggle');
        });
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: CUTI  (2-level approval: Atasan → HRD)
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/',                              [CutiController::class, 'index'])->name('index');
        Route::get('/buat',                          [CutiController::class, 'create'])->name('create');
        Route::post('/',                             [CutiController::class, 'store'])->name('store');
        Route::get('/saldo',                         [CutiController::class, 'saldo'])->name('saldo');
        Route::get('/{cuti}',                        [CutiController::class, 'show'])->name('show');
        Route::post('/{cuti}/approve-atasan',        [CutiController::class, 'approveAtasan'])->name('approve.atasan');
        Route::post('/{cuti}/tolak-atasan',          [CutiController::class, 'tolakAtasan'])->name('tolak.atasan');
        Route::post('/{cuti}/approve-hrd',           [CutiController::class, 'approveHrd'])->name('approve.hrd');
        Route::post('/{cuti}/tolak-hrd',             [CutiController::class, 'tolakHrd'])->name('tolak.hrd');
        Route::get('/{cuti}/cetak',                  [CutiController::class, 'cetak'])->name('cetak');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: SHIFT KERJA
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('shift')->name('shift.')->group(function () {
        Route::get('/',                              [ShiftController::class, 'index'])->name('index');
        Route::get('/karyawan/{karyawan}/edit',      [ShiftController::class, 'edit'])->name('edit');
        Route::put('/karyawan/{karyawan}',           [ShiftController::class, 'update'])->name('update');
        Route::get('/karyawan/{karyawan}',           [ShiftController::class, 'show'])->name('show');
        Route::post('/massal',                       [ShiftController::class, 'inputMassal'])->name('massal');
        Route::post('/copy-bulan-lalu',              [ShiftController::class, 'copyBulanLalu'])->name('copy');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: PENILAIAN KINERJA
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('kinerja')->name('kinerja.')->group(function () {
        Route::get('/',                              [KinerjaController::class, 'index'])->name('index');
        Route::get('/input',                         [KinerjaController::class, 'create'])->name('create');
        Route::post('/evaluasi',                     [KinerjaController::class, 'storeEvaluasi'])->name('evaluasi.store');
        Route::post('/pencapaian',                   [KinerjaController::class, 'storePencapaian'])->name('pencapaian.store');
        Route::get('/laporan',                       [KinerjaController::class, 'laporan'])->name('laporan');
        Route::get('/karyawan/{karyawan}',           [KinerjaController::class, 'show'])->name('show');
        Route::get('/karyawan/{karyawan}/grafik',    [KinerjaController::class, 'grafik'])->name('grafik');

        // Master indikator
        Route::get('/master/evaluasi',               [KinerjaController::class, 'masterEvaluasi'])->name('master.evaluasi');
        Route::post('/master/evaluasi',              [KinerjaController::class, 'storeMasterEvaluasi'])->name('master.evaluasi.store');
        Route::delete('/master/evaluasi/{evaluasi}', [KinerjaController::class, 'destroyMasterEvaluasi'])->name('master.evaluasi.destroy');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: PAYROLL
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                              [PayrollController::class, 'index'])->name('index');
        Route::get('/proses',                        [PayrollController::class, 'proses'])->name('proses');
        Route::get('/export',                        [PayrollController::class, 'export'])->name('export');
        Route::get('/karyawan/{karyawan}',           [PayrollController::class, 'show'])->name('show');
        Route::get('/karyawan/{karyawan}/slip',      [PayrollController::class, 'slipPdf'])->name('slip');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: LEMBUR
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('lembur')->name('lembur.')->group(function () {
        Route::get('/',                              [LemburController::class, 'index'])->name('index');
        Route::get('/buat',                          [LemburController::class, 'create'])->name('create');
        Route::post('/',                             [LemburController::class, 'store'])->name('store');
        Route::get('/rekap',                         [LemburController::class, 'rekap'])->name('rekap');
        Route::get('/setting',                       [LemburController::class, 'setting'])->name('setting');
        Route::post('/setting',                      [LemburController::class, 'updateSetting'])->name('setting.update');
        Route::get('/{lembur}',                      [LemburController::class, 'show'])->name('show');
        Route::post('/{lembur}/approve',             [LemburController::class, 'approve'])->name('approve');
        Route::post('/{lembur}/tolak',               [LemburController::class, 'tolak'])->name('tolak');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: REKRUTMEN & ONBOARDING
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('rekrutmen')->name('rekrutmen.')->group(function () {
        Route::get('/',                                                          [RekrutmenController::class, 'index'])->name('index');
        Route::get('/buat',                                                      [RekrutmenController::class, 'create'])->name('create');
        Route::post('/',                                                         [RekrutmenController::class, 'store'])->name('store');
        Route::get('/{rekrutmen}',                                               [RekrutmenController::class, 'show'])->name('show');
        Route::get('/{rekrutmen}/edit',                                          [RekrutmenController::class, 'edit'])->name('edit');
        Route::put('/{rekrutmen}',                                               [RekrutmenController::class, 'update'])->name('update');
        Route::delete('/{rekrutmen}',                                            [RekrutmenController::class, 'destroy'])->name('destroy');

        // Pelamar
        Route::post('/{rekrutmen}/pelamar',                                      [RekrutmenController::class, 'storePelamar'])->name('pelamar.store');
        Route::put('/{rekrutmen}/pelamar/{pelamar}/status',                      [RekrutmenController::class, 'updateStatusPelamar'])->name('pelamar.status');
        Route::get('/{rekrutmen}/pelamar/{pelamar}/cv',                          [RekrutmenController::class, 'downloadCv'])->name('pelamar.cv');
        Route::delete('/{rekrutmen}/pelamar/{pelamar}',                          [RekrutmenController::class, 'destroyPelamar'])->name('pelamar.destroy');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // MODUL: TRAINING & SERTIFIKASI
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/',                                                           [TrainingController::class, 'index'])->name('index');
        Route::get('/buat',                                                       [TrainingController::class, 'create'])->name('create');
        Route::post('/',                                                          [TrainingController::class, 'store'])->name('store');
        Route::get('/sertifikasi',                                                [TrainingController::class, 'sertifikasi'])->name('sertifikasi');
        Route::get('/{training}',                                                 [TrainingController::class, 'show'])->name('show');
        Route::get('/{training}/edit',                                            [TrainingController::class, 'edit'])->name('edit');
        Route::put('/{training}',                                                 [TrainingController::class, 'update'])->name('update');
        Route::delete('/{training}',                                              [TrainingController::class, 'destroy'])->name('destroy');

        // Peserta
        Route::post('/{training}/peserta',                                        [TrainingController::class, 'storePeserta'])->name('peserta.store');
        Route::put('/{training}/peserta/{peserta}/status',                        [TrainingController::class, 'updateStatusPeserta'])->name('peserta.status');
        Route::delete('/{training}/peserta/{peserta}',                            [TrainingController::class, 'destroyPeserta'])->name('peserta.destroy');
        Route::get('/peserta/{peserta}/sertifikat',                               [TrainingController::class, 'downloadSertifikat'])->name('peserta.sertifikat');
    });

    // ═══════════════════════════════════════════════════════════════════════════
    // PENGATURAN: MANAJEMEN USER
    // ═══════════════════════════════════════════════════════════════════════════
    Route::prefix('pengaturan/users')->name('pengaturan.users.')->group(function () {
        Route::get('/',                            [UserController::class, 'index'])->name('index');
        Route::get('/buat',                        [UserController::class, 'create'])->name('create');
        Route::post('/',                           [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit',                 [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',                      [UserController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password',      [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}',                   [UserController::class, 'destroy'])->name('destroy');
    });
});
