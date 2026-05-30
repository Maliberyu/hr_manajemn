<?php

namespace App\Console\Commands;

use App\Models\HrNotification;
use App\Models\KontrakKerja;
use Illuminate\Console\Command;

class KontrakReminderCommand extends Command
{
    protected $signature   = 'kontrak:reminder';
    protected $description = 'Kirim notifikasi pengingat kontrak yang akan berakhir (H-30 dan H-7)';

    public function handle(): void
    {
        $this->prosesReminder(30);
        $this->prosesReminder(7);

        // Auto-update status kontrak yang sudah lewat tgl_selesai
        $expired = KontrakKerja::where('status', 'aktif')
            ->whereNotNull('tgl_selesai')
            ->where('tgl_selesai', '<', today())
            ->get();

        foreach ($expired as $kontrak) {
            $kontrak->update(['status' => 'berakhir']);
            $this->info("Kontrak #{$kontrak->id} ({$kontrak->nik}) ditandai berakhir.");
        }

        $this->info('Kontrak reminder selesai.');
    }

    private function prosesReminder(int $hari): void
    {
        $kontraks = KontrakKerja::with(['pegawai', 'jenis'])
            ->where('status', 'aktif')
            ->whereNotNull('tgl_selesai')
            ->whereDate('tgl_selesai', today()->addDays($hari))
            ->get();

        foreach ($kontraks as $kontrak) {
            $nama  = $kontrak->pegawai?->nama ?? $kontrak->nik;
            $jenis = $kontrak->jenis?->nama ?? '-';
            $tgl   = $kontrak->tgl_selesai->isoFormat('D MMMM Y');

            // Notif ke HRD
            HrNotification::kirimKeHrd(
                'kontrak_reminder',
                "Kontrak Akan Berakhir (H-{$hari})",
                "Kontrak {$jenis} {$nama} akan berakhir pada {$tgl}.",
                route('kontrak.show', $kontrak)
            );

            // Notif ke karyawan
            HrNotification::kirimKePegawai(
                $kontrak->nik,
                'kontrak_reminder',
                "Kontrak Anda Akan Berakhir (H-{$hari})",
                "Kontrak {$jenis} Anda akan berakhir pada {$tgl}. Hubungi HRD untuk informasi perpanjangan.",
                route('ess.kontrak.index')
            );

            $this->info("Reminder H-{$hari} dikirim untuk {$nama}.");
        }
    }
}
