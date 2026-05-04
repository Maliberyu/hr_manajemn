<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Bersihkan config BPJS yang hardcode — BPJS sekarang di master komponen
        DB::table('hr_payroll_config')
            ->whereIn('key', [
                'bpjs_kes_pekerja','bpjs_kes_perusahaan',
                'bpjs_jht_pekerja','bpjs_jht_perusahaan',
                'bpjs_jp_pekerja','bpjs_jp_perusahaan',
            ])->delete();

        // Tambah toggle PPh21
        DB::table('hr_payroll_config')->insertOrIgnore([
            ['key' => 'pph21_aktif', 'value' => '1', 'label' => 'Hitung PPh21 Otomatis', 'group' => 'pajak',
             'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed komponen default (bisa dihapus/ubah oleh HRD)
        DB::table('hr_komponen_gaji')->insertOrIgnore([
            ['nama'=>'Tunjangan Transport','jenis'=>'tambah','tipe'=>'tetap',       'nilai'=>150000,'urutan'=>20,'aktif'=>1,'keterangan'=>'Tunjangan transport tetap','created_at'=>now(),'updated_at'=>now()],
            ['nama'=>'Tunjangan Makan',    'jenis'=>'tambah','tipe'=>'tetap',       'nilai'=>100000,'urutan'=>30,'aktif'=>1,'keterangan'=>'Uang makan harian','created_at'=>now(),'updated_at'=>now()],
            ['nama'=>'BPJS Kesehatan',     'jenis'=>'kurang','tipe'=>'persen_gapok','nilai'=>1,     'urutan'=>60,'aktif'=>1,'keterangan'=>'1% dari gaji pokok','created_at'=>now(),'updated_at'=>now()],
            ['nama'=>'BPJS JHT',           'jenis'=>'kurang','tipe'=>'persen_gapok','nilai'=>2,     'urutan'=>61,'aktif'=>1,'keterangan'=>'2% dari gaji pokok','created_at'=>now(),'updated_at'=>now()],
            ['nama'=>'BPJS JP',            'jenis'=>'kurang','tipe'=>'persen_gapok','nilai'=>1,     'urutan'=>62,'aktif'=>1,'keterangan'=>'1% dari gaji pokok','created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void {}
};
