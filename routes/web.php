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
use App\Http\Controllers\Kinerja\PenilaianPrestasiController;
use App\Http\Controllers\Kinerja\Penilaian360Controller;
use App\Http\Controllers\Rekrutmen\RekrutmenController;
use App\Http\Controllers\Training\TrainingController;
use App\Http\Controllers\Training\TrainingEksternalController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Pengaturan\UserController;
use App\Http\Controllers\Pengaturan\AtasanPegawaiController;

// ─── Redirect root ──────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ─── Auth routes (publik) ───────────────────────────────────────────────────────
Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',   [LoginController::class, 'login']);
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register',[RegisterController::class, 'register'])->name('register.post');

// ═══════════════════════════════════════════════════════════════════════════════
// GRUP 1 — Semua user login (auth saja, semua role)
// Termasuk: ESS, Dashboard (redirect per role), Cuti, Lembur, Training Eksternal
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // ── Dashboard — controller yang handle redirect per role ───────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── ESS Portal ─────────────────────────────────────────────────────────────
    Route::get('/ess',            [DashboardController::class, 'ess'])->name('ess.dashboard');
    Route::post('/ess/checkin',   [AbsensiController::class,   'checkIn'])->name('ess.checkin');
    Route::post('/ess/checkout',  [AbsensiController::class,   'checkOut'])->name('ess.checkout');
    Route::post('/ess/cuti',      [DashboardController::class, 'essStoreCuti'])->name('ess.cuti.store');
    Route::get('/ess/payroll/{slip}/pdf', [DashboardController::class, 'essSlipPdf'])->name('ess.payroll.pdf');

    // ── Cuti — semua role (karyawan submit, atasan/hrd approve; controller filter) ─
    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/',                         [CutiController::class, 'index'])->name('index');
        Route::get('/buat',                     [CutiController::class, 'create'])->name('create');
        Route::post('/',                        [CutiController::class, 'store'])->name('store');
        Route::get('/saldo',                    [CutiController::class, 'saldo'])->name('saldo');
        Route::get('/{cuti}',                   [CutiController::class, 'show'])->name('show');
        Route::post('/{cuti}/approve-atasan',   [CutiController::class, 'approveAtasan'])->name('approve.atasan');
        Route::post('/{cuti}/tolak-atasan',     [CutiController::class, 'tolakAtasan'])->name('tolak.atasan');
        Route::post('/{cuti}/approve-hrd',      [CutiController::class, 'approveHrd'])->name('approve.hrd');
        Route::post('/{cuti}/tolak-hrd',        [CutiController::class, 'tolakHrd'])->name('tolak.hrd');
        Route::get('/{cuti}/cetak',             [CutiController::class, 'cetak'])->name('cetak');
    });

    // ── Lembur — semua role (karyawan submit; controller filter) ──────────────
    Route::prefix('lembur')->name('lembur.')->group(function () {
        Route::get('/',                         [LemburController::class, 'index'])->name('index');
        Route::get('/buat',                     [LemburController::class, 'create'])->name('create');
        Route::post('/',                        [LemburController::class, 'store'])->name('store');
        Route::get('/rekap',                    [LemburController::class, 'rekap'])->name('rekap');
        Route::get('/setting',                  [LemburController::class, 'setting'])->name('setting');
        Route::post('/setting',                 [LemburController::class, 'updateSetting'])->name('setting.update');
        Route::get('/{lembur}',                 [LemburController::class, 'show'])->name('show');
        Route::post('/{lembur}/approve-atasan', [LemburController::class, 'approveAtasan'])->name('approve.atasan');
        Route::post('/{lembur}/tolak-atasan',   [LemburController::class, 'tolakAtasan'])->name('tolak.atasan');
        Route::post('/{lembur}/approve-hrd',    [LemburController::class, 'approveHrd'])->name('approve.hrd');
        Route::post('/{lembur}/tolak-hrd',      [LemburController::class, 'tolakHrd'])->name('tolak.hrd');
    });

    // ── Training Eksternal — semua role (controller filter per role) ───────────
    Route::prefix('training/eksternal')->name('training.eksternal.')->group(function () {
        Route::get('/',                                     [TrainingEksternalController::class, 'index'])->name('index');
        Route::get('/buat',                                 [TrainingEksternalController::class, 'create'])->name('create');
        Route::post('/',                                    [TrainingEksternalController::class, 'store'])->name('store');
        Route::get('/{eksternal}',                          [TrainingEksternalController::class, 'show'])->name('show');
        Route::post('/{eksternal}/approve-atasan',          [TrainingEksternalController::class, 'approveAtasan'])->name('approve.atasan');
        Route::post('/{eksternal}/tolak-atasan',            [TrainingEksternalController::class, 'tolakAtasan'])->name('tolak.atasan');
        Route::post('/{eksternal}/approve-hrd',             [TrainingEksternalController::class, 'approveHrd'])->name('approve.hrd');
        Route::post('/{eksternal}/tolak-hrd',               [TrainingEksternalController::class, 'tolakHrd'])->name('tolak.hrd');
        Route::post('/{eksternal}/upload-sertifikat',       [TrainingEksternalController::class, 'uploadSertifikat'])->name('upload.sertifikat');
        Route::post('/{eksternal}/validasi',                [TrainingEksternalController::class, 'validasi'])->name('validasi');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// GRUP 2 — HRD & Admin saja
// Master Karyawan, Absensi, Shift, Payroll, Kinerja, Rekrutmen, Training IHT
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:hrd,admin'])->group(function () {

    // ── Master Karyawan ────────────────────────────────────────────────────────
    Route::prefix('karyawan')->name('karyawan.')->group(function () {
        Route::resource('/', MasterKaryawanController::class)
            ->parameters(['' => 'karyawan'])
            ->names(['index'=>'index','create'=>'create','store'=>'store',
                     'show'=>'show','edit'=>'edit','update'=>'update','destroy'=>'destroy']);

        Route::prefix('{karyawan}/berkas')->name('berkas.')->group(function () {
            Route::get('/',               [BerkasPegawaiController::class, 'index'])->name('index');
            Route::post('/',              [BerkasPegawaiController::class, 'store'])->name('store');
            Route::get('/{berkas}/download',[BerkasPegawaiController::class, 'download'])->name('download');
            Route::delete('/{berkas}',    [BerkasPegawaiController::class, 'destroy'])->name('destroy');
        });
    });

    // ── Absensi ────────────────────────────────────────────────────────────────
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/',                   [AbsensiController::class, 'index'])->name('index');
        Route::get('/buat',               [AbsensiController::class, 'create'])->name('create');
        Route::post('/',                  [AbsensiController::class, 'store'])->name('store');
        Route::get('/rekap',              [AbsensiController::class, 'rekap'])->name('rekap');
        Route::post('/rekap/generate',    [AbsensiController::class, 'generateRekap'])->name('rekap.generate');
        Route::get('/export',             [AbsensiController::class, 'export'])->name('export');
        Route::get('/karyawan/{karyawan}',[AbsensiController::class, 'show'])->name('show');
        Route::get('/{absensi}/edit',     [AbsensiController::class, 'edit'])->name('edit');
        Route::put('/{absensi}',          [AbsensiController::class, 'update'])->name('update');

        Route::prefix('lokasi')->name('lokasi.')->group(function () {
            Route::get('/',               [LokasiAbsensiController::class, 'index'])->name('index');
            Route::post('/',              [LokasiAbsensiController::class, 'store'])->name('store');
            Route::put('/{lokasi}',       [LokasiAbsensiController::class, 'update'])->name('update');
            Route::delete('/{lokasi}',    [LokasiAbsensiController::class, 'destroy'])->name('destroy');
            Route::patch('/{lokasi}/toggle',[LokasiAbsensiController::class, 'toggle'])->name('toggle');
        });
    });

    // ── Shift Kerja ────────────────────────────────────────────────────────────
    Route::prefix('shift')->name('shift.')->group(function () {
        Route::get('/',                           [ShiftController::class, 'index'])->name('index');
        Route::get('/karyawan/{karyawan}/edit',   [ShiftController::class, 'edit'])->name('edit');
        Route::put('/karyawan/{karyawan}',        [ShiftController::class, 'update'])->name('update');
        Route::get('/karyawan/{karyawan}',        [ShiftController::class, 'show'])->name('show');
        Route::post('/massal',                    [ShiftController::class, 'inputMassal'])->name('massal');
        Route::post('/copy-bulan-lalu',           [ShiftController::class, 'copyBulanLalu'])->name('copy');
    });

    // ── Payroll ────────────────────────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                                    [PayrollController::class, 'index'])->name('index');
        Route::get('/export',                              [PayrollController::class, 'export'])->name('export');
        Route::post('/generate',                           [PayrollController::class, 'generateSlips'])->name('generate');
        Route::get('/master',                              [PayrollController::class, 'master'])->name('master');
        Route::post('/master/umk',                         [PayrollController::class, 'storeUmk'])->name('master.umk.store');
        Route::delete('/master/umk/{umk}',                 [PayrollController::class, 'destroyUmk'])->name('master.umk.destroy');
        Route::post('/master/gaji',                        [PayrollController::class, 'storeMasterGaji'])->name('master.gaji.store');
        Route::delete('/master/gaji/{masterGaji}',         [PayrollController::class, 'destroyMasterGaji'])->name('master.gaji.destroy');
        Route::post('/master/komponen',                    [PayrollController::class, 'storeKomponen'])->name('master.komponen.store');
        Route::patch('/master/komponen/{komponen}/toggle', [PayrollController::class, 'toggleKomponen'])->name('master.komponen.toggle');
        Route::delete('/master/komponen/{komponen}',       [PayrollController::class, 'destroyKomponen'])->name('master.komponen.destroy');
        Route::post('/master/config',                      [PayrollController::class, 'updateConfig'])->name('master.config.update');
        Route::post('/master/pegawai',                     [PayrollController::class, 'savePegawaiPayroll'])->name('master.pegawai.save');
        Route::get('/slip/{slip}',                         [PayrollController::class, 'showSlip'])->name('slip.show');
        Route::put('/slip/{slip}',                         [PayrollController::class, 'updateSlip'])->name('slip.update');
        Route::post('/slip/{slip}/finalize',               [PayrollController::class, 'finalizeSlip'])->name('slip.finalize');
        Route::post('/slip/{slip}/unfinalize',             [PayrollController::class, 'unFinalizeSlip'])->name('slip.unfinalize');
        Route::get('/slip/{slip}/pdf',                     [PayrollController::class, 'slipPdf'])->name('slip.pdf');
    });

    // ── Penilaian Kinerja ──────────────────────────────────────────────────────
    Route::prefix('kinerja')->name('kinerja.')->group(function () {
        Route::get('/', [KinerjaController::class, 'index'])->name('index');

        Route::get('/master',                                    [KinerjaController::class, 'master'])->name('master');
        Route::post('/master/kriteria',                          [KinerjaController::class, 'storeKriteria'])->name('master.kriteria.store');
        Route::post('/master/kriteria/bobot',                    [KinerjaController::class, 'updateBobot'])->name('master.kriteria.bobot');
        Route::patch('/master/kriteria/{kriteria}/toggle',       [KinerjaController::class, 'toggleKriteria'])->name('master.kriteria.toggle');
        Route::delete('/master/kriteria/{kriteria}',             [KinerjaController::class, 'destroyKriteria'])->name('master.kriteria.destroy');
        Route::post('/master/sub-indikator',                     [KinerjaController::class, 'storeSubIndikator'])->name('master.sub.store');
        Route::delete('/master/sub-indikator/{sub}',             [KinerjaController::class, 'destroySubIndikator'])->name('master.sub.destroy');
        Route::post('/master/aspek',                             [KinerjaController::class, 'storeAspek'])->name('master.aspek.store');
        Route::delete('/master/aspek/{aspek}',                   [KinerjaController::class, 'destroyAspek'])->name('master.aspek.destroy');
        Route::post('/master/bobot-rater',                       [KinerjaController::class, 'updateBobotRater'])->name('master.bobot.rater');

        Route::prefix('prestasi')->name('prestasi.')->group(function () {
            Route::get('/',                     [PenilaianPrestasiController::class, 'index'])->name('index');
            Route::get('/buat',                 [PenilaianPrestasiController::class, 'create'])->name('create');
            Route::post('/',                    [PenilaianPrestasiController::class, 'store'])->name('store');
            Route::get('/{penilaian}',          [PenilaianPrestasiController::class, 'show'])->name('show');
            Route::get('/{penilaian}/edit',     [PenilaianPrestasiController::class, 'edit'])->name('edit');
            Route::put('/{penilaian}',          [PenilaianPrestasiController::class, 'update'])->name('update');
            Route::post('/{penilaian}/finalize',[PenilaianPrestasiController::class, 'finalize'])->name('finalize');
            Route::get('/{penilaian}/pdf',      [PenilaianPrestasiController::class, 'pdf'])->name('pdf');
        });

        Route::prefix('360')->name('360.')->group(function () {
            Route::get('/',                 [Penilaian360Controller::class, 'index'])->name('index');
            Route::get('/buat',             [Penilaian360Controller::class, 'create'])->name('create');
            Route::post('/',                [Penilaian360Controller::class, 'store'])->name('store');
            Route::get('/{sesi}',           [Penilaian360Controller::class, 'show'])->name('show');
            Route::get('/{sesi}/isi',       [Penilaian360Controller::class, 'form'])->name('form');
            Route::post('/{sesi}/isi',      [Penilaian360Controller::class, 'submitForm'])->name('form.submit');
            Route::get('/{sesi}/rekap',     [Penilaian360Controller::class, 'rekap'])->name('rekap');
            Route::post('/{sesi}/tutup',    [Penilaian360Controller::class, 'tutup'])->name('tutup');
        });
    });

    // ── Rekrutmen ──────────────────────────────────────────────────────────────
    Route::prefix('rekrutmen')->name('rekrutmen.')->group(function () {
        Route::get('/',                                 [RekrutmenController::class, 'index'])->name('index');
        Route::get('/buat',                             [RekrutmenController::class, 'create'])->name('create');
        Route::post('/',                                [RekrutmenController::class, 'store'])->name('store');
        Route::get('/{rekrutmen}',                      [RekrutmenController::class, 'show'])->name('show');
        Route::get('/{rekrutmen}/edit',                 [RekrutmenController::class, 'edit'])->name('edit');
        Route::put('/{rekrutmen}',                      [RekrutmenController::class, 'update'])->name('update');
        Route::delete('/{rekrutmen}',                   [RekrutmenController::class, 'destroy'])->name('destroy');
        Route::post('/{rekrutmen}/pelamar',             [RekrutmenController::class, 'storePelamar'])->name('pelamar.store');
        Route::put('/{rekrutmen}/pelamar/{pelamar}/status',[RekrutmenController::class, 'updateStatusPelamar'])->name('pelamar.status');
        Route::get('/{rekrutmen}/pelamar/{pelamar}/cv', [RekrutmenController::class, 'downloadCv'])->name('pelamar.cv');
        Route::delete('/{rekrutmen}/pelamar/{pelamar}',[RekrutmenController::class, 'destroyPelamar'])->name('pelamar.destroy');
    });

    // ── Training IHT + Setting ─────────────────────────────────────────────────
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/setting',  [TrainingController::class, 'setting'])->name('setting');
        Route::post('/setting', [TrainingController::class, 'settingUpdate'])->name('setting.update');

        Route::prefix('iht')->name('iht.')->group(function () {
            Route::get('/',                                          [TrainingController::class, 'index'])->name('index');
            Route::get('/buat',                                      [TrainingController::class, 'create'])->name('create');
            Route::post('/',                                         [TrainingController::class, 'store'])->name('store');
            Route::get('/{iht}',                                     [TrainingController::class, 'show'])->name('show');
            Route::get('/{iht}/edit',                                [TrainingController::class, 'edit'])->name('edit');
            Route::put('/{iht}',                                     [TrainingController::class, 'update'])->name('update');
            Route::post('/{iht}/tutup',                              [TrainingController::class, 'tutup'])->name('tutup');
            Route::delete('/{iht}',                                  [TrainingController::class, 'destroy'])->name('destroy');
            Route::post('/{iht}/peserta',                            [TrainingController::class, 'storePeserta'])->name('peserta.store');
            Route::put('/{iht}/peserta/{peserta}/status',            [TrainingController::class, 'updateStatusPeserta'])->name('peserta.status');
            Route::delete('/{iht}/peserta/{peserta}',                [TrainingController::class, 'destroyPeserta'])->name('peserta.destroy');
            Route::post('/{iht}/peserta/{peserta}/sertifikat',       [TrainingController::class, 'generateSertifikat'])->name('peserta.sertifikat.generate');
            Route::get('/{iht}/peserta/{peserta}/sertifikat/download',[TrainingController::class, 'downloadSertifikat'])->name('peserta.sertifikat.download');
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// GRUP 3 — Admin saja
// ═══════════════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::prefix('pengaturan/users')->name('pengaturan.users.')->group(function () {
        Route::get('/',                       [UserController::class, 'index'])->name('index');
        Route::get('/buat',                   [UserController::class, 'create'])->name('create');
        Route::post('/',                      [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit',            [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}',                 [UserController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}',              [UserController::class, 'destroy'])->name('destroy');
    });
});

// ── Setting Atasan (HRD & Admin) ───────────────────────────────────────────────
Route::middleware(['auth', 'role:hrd,admin'])
    ->prefix('pengaturan/atasan')
    ->name('pengaturan.atasan.')
    ->group(function () {
        Route::get('/',        [AtasanPegawaiController::class, 'index'])->name('index');
        Route::post('/',       [AtasanPegawaiController::class, 'store'])->name('store');
        Route::post('/bulk',   [AtasanPegawaiController::class, 'storeBulk'])->name('bulk');
        Route::delete('/',     [AtasanPegawaiController::class, 'destroy'])->name('destroy');
    });
