<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
DB::table('lokers')->insert([
            [
                'judul' => 'Staff Administrasi',
                'deskripsi' => 'Mengelola data dan dokumen administrasi perusahaan.',
                'lokasi' => 'Jakarta Selatan',
                'gaji' => 5000000,
                'deadline' => '2025-12-30',
                'user_id' => 1,
            ],
            [
                'judul' => 'Desainer Grafis',
                'deskripsi' => 'Membuat desain visual untuk promosi dan media sosial.',
                'lokasi' => 'Bandung',
                'gaji' => 4500000,
                'deadline' => '2025-12-25',
                'user_id' => 1,
            ],
            [
                'judul' => 'Front-End Developer',
                'deskripsi' => 'Membangun tampilan web responsif dengan Vue.js.',
                'lokasi' => 'Yogyakarta',
                'gaji' => 8000000,
                'deadline' => '2026-01-10',
                'user_id' => 1,
            ],
            [
                'judul' => 'Back-End Developer',
                'deskripsi' => 'Membuat API dan logika bisnis menggunakan Laravel.',
                'lokasi' => 'Surabaya',
                'gaji' => 9000000,
                'deadline' => '2026-02-01',
                'user_id' => 1,
            ],
            [
                'judul' => 'Digital Marketing',
                'deskripsi' => 'Mengelola iklan online dan strategi SEO.',
                'lokasi' => 'Jakarta Barat',
                'gaji' => 6000000,
                'deadline' => '2025-12-15',
                'user_id' => 1,
            ],
            [
                'judul' => 'Customer Service',
                'deskripsi' => 'Menangani keluhan dan pertanyaan pelanggan.',
                'lokasi' => 'Bekasi',
                'gaji' => 4000000,
                'deadline' => '2025-11-30',
                'user_id' => 1,
            ],
            [
                'judul' => 'Content Writer',
                'deskripsi' => 'Menulis artikel dan konten promosi untuk web.',
                'lokasi' => 'Malang',
                'gaji' => 4500000,
                'deadline' => '2026-01-05',
                'user_id' => 1,
            ],
            [
                'judul' => 'HRD Staff',
                'deskripsi' => 'Mengelola proses rekrutmen dan administrasi karyawan.',
                'lokasi' => 'Tangerang',
                'gaji' => 5500000,
                'deadline' => '2025-12-20',
                'user_id' => 1,
            ],
            [
                'judul' => 'Video Editor',
                'deskripsi' => 'Mengedit video promosi dan dokumentasi event.',
                'lokasi' => 'Denpasar',
                'gaji' => 7000000,
                'deadline' => '2026-01-12',
                'user_id' => 1,
            ],
            [
                'judul' => 'Sales Executive',
                'deskripsi' => 'Menawarkan produk ke pelanggan baru dan menjaga relasi.',
                'lokasi' => 'Medan',
                'gaji' => 6000000,
                'deadline' => '2025-12-10',
                'user_id' => 1,
            ],
            [
                'judul' => 'UI/UX Designer',
                'deskripsi' => 'Merancang pengalaman pengguna yang intuitif.',
                'lokasi' => 'Semarang',
                'gaji' => 8500000,
                'deadline' => '2026-01-20',
                'user_id' => 1,
            ],
            [
                'judul' => 'Project Manager',
                'deskripsi' => 'Mengatur timeline dan koordinasi tim proyek.',
                'lokasi' => 'Jakarta Pusat',
                'gaji' => 12000000,
                'deadline' => '2026-02-15',
                'user_id' => 1,
            ],
            [
                'judul' => 'Network Engineer',
                'deskripsi' => 'Menjaga jaringan komputer agar tetap stabil dan aman.',
                'lokasi' => 'Surabaya',
                'gaji' => 9500000,
                'deadline' => '2026-01-25',
                'user_id' => 1,
            ],
            [
                'judul' => 'Accounting Staff',
                'deskripsi' => 'Mengelola laporan keuangan dan pembukuan perusahaan.',
                'lokasi' => 'Jakarta Timur',
                'gaji' => 5500000,
                'deadline' => '2025-12-28',
                'user_id' => 1,
            ],
            [
                'judul' => 'Warehouse Staff',
                'deskripsi' => 'Mengatur stok dan distribusi barang di gudang.',
                'lokasi' => 'Bekasi',
                'gaji' => 4500000,
                'deadline' => '2025-11-25',
                'user_id' => 1,
            ],
            [
                'judul' => 'IT Support',
                'deskripsi' => 'Memberikan dukungan teknis kepada pengguna.',
                'lokasi' => 'Depok',
                'gaji' => 6000000,
                'deadline' => '2026-01-08',
                'user_id' => 1,
            ],
            [
                'judul' => 'Security Officer',
                'deskripsi' => 'Menjaga keamanan lingkungan kantor.',
                'lokasi' => 'Cikarang',
                'gaji' => 4000000,
                'deadline' => '2025-12-18',
                'user_id' => 1,
            ],
            [
                'judul' => 'Driver Operasional',
                'deskripsi' => 'Mengantar dokumen dan barang perusahaan.',
                'lokasi' => 'Bogor',
                'gaji' => 4500000,
                'deadline' => '2025-12-05',
                'user_id' => 1,
            ],
            [
                'judul' => 'Office Boy',
                'deskripsi' => 'Membersihkan dan menjaga kebersihan area kantor.',
                'lokasi' => 'Jakarta Barat',
                'gaji' => 3500000,
                'deadline' => '2025-11-28',
                'user_id' => 1,
            ],
            [
                'judul' => 'Data Analyst',
                'deskripsi' => 'Menganalisis data dan membuat laporan performa bisnis.',
                'lokasi' => 'Yogyakarta',
                'gaji' => 10000000,
                'deadline' => '2026-01-30',
                'user_id' => 1,
            ],
        ]);

    }
}
