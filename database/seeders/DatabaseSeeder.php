<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Users
        $fadhilId = DB::table('users')->insertGetId([
            'name' => 'Fadhil Rahmatullah',
            'email' => env('SEED_STUDENT_FADHIL_EMAIL', 'student1@example.com'),
            'nim' => '2024102004',
            'role' => 'Mahasiswa',
            'faculty' => 'Fakultas Teknik',
            'prodi' => 'S1 Teknik Informatika',
            'phone' => '081234567890',
            'wishlist' => json_encode(['unu-002', 'unu-007']),
            'password' => Hash::make(env('SEED_STUDENT_FADHIL_PASSWORD', 'placeholder_password')),
            'password_plain' => env('SEED_STUDENT_FADHIL_PASSWORD', 'placeholder_password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        DB::table('users')->insert([
            [
                'name' => 'Ahmad Fauzi',
                'email' => env('SEED_STUDENT_AHMAD_EMAIL', 'student2@example.com'),
                'nim' => '2024102015',
                'role' => 'Mahasiswa',
                'faculty' => 'Fakultas Kesehatan',
                'prodi' => 'S1 Farmasi',
                'phone' => '087855544432',
                'wishlist' => '[]',
                'password' => Hash::make(env('SEED_STUDENT_AHMAD_PASSWORD', 'placeholder_password')),
                'password_plain' => env('SEED_STUDENT_AHMAD_PASSWORD', 'placeholder_password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Siti Aisyah',
                'email' => env('SEED_STUDENT_SITI_EMAIL', 'student3@example.com'),
                'nim' => '2024103022',
                'role' => 'Mahasiswa',
                'faculty' => 'Fakultas Pendidikan',
                'prodi' => 'S1 Pendidikan Guru Sekolah Dasar (PGSD)',
                'phone' => '085966677788',
                'wishlist' => '[]',
                'password' => Hash::make(env('SEED_STUDENT_SITI_PASSWORD', 'placeholder_password')),
                'password_plain' => env('SEED_STUDENT_SITI_PASSWORD', 'placeholder_password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Budi Santoso',
                'email' => env('SEED_STUDENT_BUDI_EMAIL', 'student4@example.com'),
                'nim' => '2024102009',
                'role' => 'Mahasiswa',
                'faculty' => 'Fakultas Ekonomi',
                'prodi' => 'S1 Ekonomi Islam',
                'phone' => '082188899900',
                'wishlist' => '[]',
                'password' => Hash::make(env('SEED_STUDENT_BUDI_PASSWORD', 'placeholder_password')),
                'password_plain' => env('SEED_STUDENT_BUDI_PASSWORD', 'placeholder_password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => env('SEED_STUDENT_DEWI_EMAIL', 'student5@example.com'),
                'nim' => '2024105001',
                'role' => 'Mahasiswa',
                'faculty' => 'Fakultas Hukum',
                'prodi' => 'S1 Hukum Bisnis',
                'phone' => '081988877766',
                'wishlist' => '[]',
                'password' => Hash::make(env('SEED_STUDENT_DEWI_PASSWORD', 'placeholder_password')),
                'password_plain' => env('SEED_STUDENT_DEWI_PASSWORD', 'placeholder_password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        $adminId = DB::table('users')->insertGetId([
            'name' => 'Pustakawan UNU NTB',
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'nim' => null,
            'role' => 'Pustakawan',
            'faculty' => null,
            'prodi' => null,
            'phone' => '081122334455',
            'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'placeholder_password')),
            'password_plain' => env('SEED_ADMIN_PASSWORD', 'placeholder_password'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // 2. Seed Books
        $books = [
            [
                'id' => 'unu-001',
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'genre' => 'Kesusastraan',
                'class_number' => '813',
                'description' => 'Roman sejarah luar biasa yang berlatar belakang kebangkitan nasional Indonesia pada awal abad ke-20. Mengisahkan perjuangan Minke melawan ketidakadilan kolonial.',
                'stock' => 2,
                'total_stock' => 3,
                'published_year' => 1980,
                'popularity' => 98,
                'cover_style' => json_encode([
                    'bgStart' => '#4A2E2B',
                    'bgEnd' => '#2D1B19',
                    'text' => '#E9D5C4',
                    'accent' => '#C19A6B',
                    'pattern' => 'border'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-002',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'genre' => 'Kesusastraan',
                'class_number' => '813',
                'description' => 'Kisah inspiratif tentang perjuangan sepuluh anak di Belitung dari keluarga miskin untuk mendapatkan pendidikan yang layak di sekolah dasar Muhammadiyah yang terancam ditutup.',
                'stock' => 5,
                'total_stock' => 5,
                'published_year' => 2005,
                'popularity' => 95,
                'cover_style' => json_encode([
                    'bgStart' => '#398E8E',
                    'bgEnd' => '#1D5F5F',
                    'text' => '#F4F4F2',
                    'accent' => '#E2B842',
                    'pattern' => 'stripes'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-003',
                'title' => 'Saman',
                'author' => 'Ayu Utami',
                'genre' => 'Kesusastraan',
                'class_number' => '813',
                'description' => 'Sebuah novel penting dalam sastra Indonesia modern yang mengeksplorasi tema seksualitas, politik, persahabatan, dan kebebasan berekspresi di masa akhir Orde Baru.',
                'stock' => 0,
                'total_stock' => 1,
                'published_year' => 1998,
                'popularity' => 88,
                'cover_style' => json_encode([
                    'bgStart' => '#2D4263',
                    'bgEnd' => '#1C2D46',
                    'text' => '#F4F4F2',
                    'accent' => '#D96B27',
                    'pattern' => 'circle'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-004',
                'title' => 'Madilog',
                'author' => 'Tan Malaka',
                'genre' => 'Filsafat & Psikologi',
                'class_number' => '100',
                'description' => 'Karya magnum opus Tan Malaka yang memadukan Materialisme, Dialektika, dan Logika sebagai jalan keluar dari cara berpikir mistis menuju pemikiran ilmiah modern bagi bangsa Indonesia.',
                'stock' => 1,
                'total_stock' => 2,
                'published_year' => 1943,
                'popularity' => 90,
                'cover_style' => json_encode([
                    'bgStart' => '#1E2522',
                    'bgEnd' => '#0C100E',
                    'text' => '#E5E5E2',
                    'accent' => '#C15C3D',
                    'pattern' => 'grid'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-005',
                'title' => 'Laut Bercerita',
                'author' => 'Leila S. Chudori',
                'genre' => 'Kesusastraan',
                'class_number' => '813',
                'description' => 'Sebuah novel yang menceritakan tentang hilangnya para aktivis mahasiswa di masa Orde Baru dan bagaimana keluarga serta sahabat yang ditinggalkan bertahan menghadapi kehilangan.',
                'stock' => 0,
                'total_stock' => 2,
                'published_year' => 2017,
                'popularity' => 97,
                'cover_style' => json_encode([
                    'bgStart' => '#1A3A3A',
                    'bgEnd' => '#0A1F1F',
                    'text' => '#F4F4F2',
                    'accent' => '#8AAEA5',
                    'pattern' => 'waves'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-006',
                'title' => 'Cantik itu Luka',
                'author' => 'Eka Kurniawan',
                'genre' => 'Kesusastraan',
                'class_number' => '813',
                'description' => 'Mahakarya yang menggabungkan sejarah perjuangan kemerdekaan dengan dongeng lokal, tragedi keluarga, dan realisme magis, dimulai dari kebangkitan seorang pelacur legendaris dari kubur.',
                'stock' => 3,
                'total_stock' => 3,
                'published_year' => 2002,
                'popularity' => 92,
                'cover_style' => json_encode([
                    'bgStart' => '#722F37',
                    'bgEnd' => '#4A151B',
                    'text' => '#F7D6D0',
                    'accent' => '#D4AF37',
                    'pattern' => 'floral'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-007',
                'title' => 'Introduction to Algorithms',
                'author' => 'Thomas H. Cormen',
                'genre' => 'Teknologi & Ilmu Terapan',
                'class_number' => '005.1',
                'description' => 'Buku panduan komprehensif mengenai algoritma komputer, mencakup analisis efisiensi, desain algoritma, struktur data, dan topik-topik lanjutan untuk pengembang profesional.',
                'stock' => 2,
                'total_stock' => 2,
                'published_year' => 2009,
                'popularity' => 85,
                'cover_style' => json_encode([
                    'bgStart' => '#2E3A4E',
                    'bgEnd' => '#1A2533',
                    'text' => '#FFFFFF',
                    'accent' => '#398E8E',
                    'pattern' => 'tech'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-008',
                'title' => 'Principles of Data Science',
                'author' => 'Sinan Ozdemir',
                'genre' => 'Teknologi & Ilmu Terapan',
                'class_number' => '005.1',
                'description' => 'Membahas konsep-konsep inti statistika, pemrosesan data, pemodelan machine learning, dan teknik evaluasi untuk membangun karir yang kuat di bidang sains data.',
                'stock' => 1,
                'total_stock' => 1,
                'published_year' => 2016,
                'popularity' => 80,
                'cover_style' => json_encode([
                    'bgStart' => '#1E3D59',
                    'bgEnd' => '#172F44',
                    'text' => '#F5F0E1',
                    'accent' => '#FF6E40',
                    'pattern' => 'tech'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-009',
                'title' => 'Philosophy of Science',
                'author' => 'Samir Okasha',
                'genre' => 'Filsafat & Psikologi',
                'class_number' => '100',
                'description' => 'Pengantar singkat namun mendalam mengenai sifat, metode, dan batas-batas pengetahuan ilmiah, mendiskusikan debat utama antara realisme dan antirealisme.',
                'stock' => 2,
                'total_stock' => 2,
                'published_year' => 2002,
                'popularity' => 78,
                'cover_style' => json_encode([
                    'bgStart' => '#424B54',
                    'bgEnd' => '#2E353B',
                    'text' => '#E6C280',
                    'accent' => '#E6C280',
                    'pattern' => 'border'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-010',
                'title' => 'A Brief History of Time',
                'author' => 'Stephen Hawking',
                'genre' => 'Sains & Matematika',
                'class_number' => '520',
                'description' => 'Karya populer fisikawan teoritis terkemuka yang menjelaskan konsep lubang hitam, waktu, ruang angkasa, dan asal-usul alam semesta kepada pembaca awam dengan bahasa yang mudah dicerna.',
                'stock' => 2,
                'total_stock' => 2,
                'published_year' => 1988,
                'popularity' => 94,
                'cover_style' => json_encode([
                    'bgStart' => '#131826',
                    'bgEnd' => '#090C14',
                    'text' => '#E5F0FF',
                    'accent' => '#76A2E8',
                    'pattern' => 'stars'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'unu-011',
                'title' => 'Cosmos',
                'author' => 'Carl Sagan',
                'genre' => 'Sains & Matematika',
                'class_number' => '520',
                'description' => 'Petualangan ilmiah kosmis yang mengeksplorasi hubungan antara evolusi kosmos, peradaban manusia, dan penjelajahan ruang angkasa dalam prosa sastra yang indah.',
                'stock' => 4,
                'total_stock' => 4,
                'published_year' => 1980,
                'popularity' => 93,
                'cover_style' => json_encode([
                    'bgStart' => '#2E1C47',
                    'bgEnd' => '#170C25',
                    'text' => '#FFDF9F',
                    'accent' => '#FFDF9F',
                    'pattern' => 'stars'
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        DB::table('books')->insert($books);

        // 3. Seed Loans
        DB::table('loans')->insert([
            [
                'book_id' => 'unu-001',
                'user_id' => $fadhilId,
                'borrow_date' => Carbon::create(2026, 6, 5, 10, 0, 0),
                'due_date' => Carbon::create(2026, 6, 19, 10, 0, 0),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'book_id' => 'unu-004',
                'user_id' => $fadhilId,
                'borrow_date' => Carbon::create(2026, 5, 20, 9, 0, 0),
                'due_date' => Carbon::create(2026, 6, 3, 9, 0, 0), // Already overdue!
                'status' => 'overdue',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        // 4. Seed Reservations
        DB::table('reservations')->insert([
            [
                'book_id' => 'unu-003',
                'user_id' => $fadhilId,
                'reserved_date' => Carbon::create(2026, 6, 10, 14, 30, 0),
                'queue_position' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);

        // 5. Seed Notifications
        DB::table('notifications')->insert([
            [
                'user_id' => $fadhilId,
                'title' => 'Peminjaman Berhasil',
                'message' => "Anda telah meminjam buku 'Bumi Manusia'. Harap kembalikan sebelum 19 Jun 2026.",
                'type' => 'hold',
                'is_read' => true,
                'created_at' => Carbon::create(2026, 6, 5, 10, 5, 0),
                'updated_at' => Carbon::create(2026, 6, 5, 10, 5, 0)
            ],
            [
                'user_id' => $fadhilId,
                'title' => 'PEMBERITAHUAN JATUH TEMPO',
                'message' => "Buku 'Madilog' sudah jatuh tempo pada 3 Jun 2026. Anda dikenakan status terlambat. Segera lakukan pengembalian ke pustakawan.",
                'type' => 'warning',
                'is_read' => false,
                'created_at' => Carbon::create(2026, 6, 3, 18, 0, 0),
                'updated_at' => Carbon::create(2026, 6, 3, 18, 0, 0)
            ]
        ]);

        // 6. Seed Circulation Logs
        DB::table('circulation_logs')->insert([
            [
                'activity' => 'Peminjaman Buku',
                'detail' => 'Mahasiswa Fadhil Rahmatullah (2024102004) meminjam \'Madilog\'.',
                'timestamp' => Carbon::create(2026, 5, 20, 9, 0, 0),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'activity' => 'Peminjaman Buku',
                'detail' => 'Mahasiswa Fadhil Rahmatullah (2024102004) meminjam \'Bumi Manusia\'.',
                'timestamp' => Carbon::create(2026, 6, 5, 10, 0, 0),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'activity' => 'Reservasi Buku',
                'detail' => 'Mahasiswa Fadhil Rahmatullah (2024102004) mengantre reservasi untuk \'Saman\'.',
                'timestamp' => Carbon::create(2026, 6, 10, 14, 30, 0),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}
