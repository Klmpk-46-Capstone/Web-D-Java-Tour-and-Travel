<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Itinerary;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CmsTourSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'bambang@djavatour.com'],
            [
                'name' => 'Bambang Setiawan',
                'whatsapp' => '081234567890',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
            ]
        );

        // Additional admin for ops
        User::firstOrCreate(
            ['email' => 'admin@djavatour.com'],
            [
                'name' => 'Admin Ops Tour',
                'whatsapp' => '081234567891',
                'password' => Hash::make('password123'),
                'role' => 'admin_ops',
            ]
        );

        // Customer Demo User
        $customer = User::firstOrCreate(
            ['email' => 'rian.adi@gmail.com'],
            [
                'name' => 'Rian Adiputra',
                'whatsapp' => '081299881122',
                'password' => Hash::make('password123'),
                'role' => 'customer',
            ]
        );

        // 2. Packages
        $packagesData = [
            [
                'name' => 'Karimunjawa Paradise 3D2N',
                'slug' => 'karimunjawa-paradise-3d2n',
                'category' => 'Wisata Bahari & Island Hopping',
                'route_type' => 'bahari',
                'duration' => '3 Hari 2 Malam',
                'price' => 1190000,
                'image_url' => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1200&q=80',
                'badge' => 'BAHARI OPEN TRIP',
                'rating' => 4.90,
                'total_reviews' => 184,
                'is_featured' => true,
                'is_active' => true,
                'description' => 'Eksplorasi kepulauan Karimunjawa dengan keindahan bawah laut terumbu karang, penangkaran hiu, dan pulau-pulau eksotis seperti Menjangan Kecil dan Tanjung Gelam.',
                'itineraries' => [
                    ['day' => 1, 'title' => 'Penyeberangan & Sunset Tanjung Gelam', 'desc' => 'Tiba di Pelabuhan Kartini Jepara, naik KMP Express Bahari menuju Karimunjawa. Check-in resort, santai dan menikmati sunset spektakuler di Pantai Tanjung Gelam.'],
                    ['day' => 2, 'title' => 'Island Hopping & Snorkeling Hiu', 'desc' => 'Snorkeling di spot terumbu karang Nemo Menjangan Kecil, santap siang ikan bakar khas pulau Cemara Besar, dan kunjungan ke penangkaran hiu.'],
                    ['day' => 3, 'title' => 'Sunrise Bukit Love & Kembali ke Jepara', 'desc' => 'Trekking ringan menikmati sunrise dari Bukit Love Karimunjawa, berburu souvenir khas, dan persiapan pelayaran kembali ke Jepara.'],
                ],
            ],
            [
                'name' => 'Bromo Midnight Sunrise Express',
                'slug' => 'bromo-midnight-sunrise-express',
                'category' => 'Mountain Trekking & Sunrise',
                'route_type' => 'gunung',
                'duration' => 'Midnight (1 Hari)',
                'price' => 780000,
                'image_url' => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80',
                'badge' => 'MIDNIGHT EXPEDITION',
                'rating' => 4.90,
                'total_reviews' => 142,
                'is_featured' => true,
                'is_active' => true,
                'description' => 'Petualangan midnight jeep 4x4 menyaksikan golden sunrise legendaris Gunung Bromo dari Bukit Kingkong dan menjelajahi kawah aktif serta Pasir Berbisik.',
                'itineraries' => [
                    ['day' => 1, 'title' => 'Midnight Jeep Expedition & Golden Sunrise', 'desc' => 'Penjemputan tengah malam di Malang/Surabaya, konvoi jeep 4x4 menuju Penanjakan 1 / Bukit Kingkong menyambut golden sunrise.'],
                    ['day' => 1, 'title' => 'Kawah Aktif, Pasir Berbisik & Savana Teletubbies', 'desc' => 'Trekking menaiki tangga kawah Bromo, berkendara melintasi Lautan Pasir Berbisik, dan berfoto di padang savana Bukit Teletubbies.'],
                ],
            ],
            [
                'name' => 'Dieng Highland Escapade 2D1N',
                'slug' => 'dieng-highland-escapade-2d1n',
                'category' => 'Mountain Trekking & Sunrise',
                'route_type' => 'gunung',
                'duration' => '2 Hari 1 Malam',
                'price' => 850000,
                'image_url' => 'https://images.unsplash.com/photo-1596401057633-54a8fe8ef647?auto=format&fit=crop&w=1200&q=80',
                'badge' => 'NEGERI DI ATAS AWAN',
                'rating' => 4.80,
                'total_reviews' => 96,
                'is_featured' => true,
                'is_active' => true,
                'description' => 'Pesona dataran tinggi Dieng berhawa sejuk, Golden Sunrise Bukit Sikunir, Danau Telaga Warna yang memukau, dan letupan kawah alami Sikidang.',
                'itineraries' => [
                    ['day' => 1, 'title' => 'Candi Arjuna, Kawah Sikidang & Telaga Warna', 'desc' => 'Tiba di Dataran Tinggi Dieng, eksplorasi kompleks candi tertua di Jawa Candi Arjuna, Kawah Sikidang, dan Batu Ratapan Angin.'],
                    ['day' => 2, 'title' => 'Golden Sunrise Sikunir & Belanja Carica', 'desc' => 'Subuh trekking menuju Puncak Sikunir menyaksikan matahari terbit di atas awan, lanjut berbelanja manisan buah carica khas Dieng.'],
                ],
            ],
            [
                'name' => 'Jogja Heritage & Borobudur VIP',
                'slug' => 'jogja-heritage-borobudur-vip',
                'category' => 'Cultural, Heritage & City Tour',
                'route_type' => 'heritage',
                'duration' => '3 Hari 2 Malam',
                'price' => 1070000,
                'image_url' => 'https://images.unsplash.com/photo-1596402184320-417e7178b2cd?auto=format&fit=crop&w=1200&q=80',
                'badge' => 'HERITAGE VIP',
                'rating' => 4.90,
                'total_reviews' => 118,
                'is_featured' => false,
                'is_active' => true,
                'description' => 'Napak tilas kemegahan Candi Borobudur & Prambanan, Kraton Ngayogyakarta Hadiningrat, serta kehangatan budaya dan kuliner malam jalan Malioboro.',
                'itineraries' => [
                    ['day' => 1, 'title' => 'Kemegahan Borobudur & Desa Wisata Candirejo', 'desc' => 'Kunjungan eksklusif naik struktur Candi Borobudur dengan pemandu arkeolog, lanjut naik andong keliling desa wisata.'],
                    ['day' => 2, 'title' => 'Kraton Jogja, Taman Sari & Candi Prambanan Sunset', 'desc' => 'Eksplorasi budaya istana kesultanan, pemandian kuno Taman Sari, dan menyaksikan sunset di pelataran Candi Prambanan.'],
                    ['day' => 3, 'title' => 'Sentra Batik Tulis & Belanja Oleh-Oleh Bakpia', 'desc' => 'Workshop kilat batik tulis di Kotagede dan berbelanja bakpia kukus khas sebelum diantar ke Bandara YIA / Stasiun Tugu.'],
                ],
            ],
            [
                'name' => 'Kawah Ijen Blue Fire Private',
                'slug' => 'kawah-ijen-blue-fire-private',
                'category' => 'Mountain Trekking & Sunrise',
                'route_type' => 'gunung',
                'duration' => '2 Hari 1 Malam',
                'price' => 975000,
                'image_url' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1200&q=80',
                'badge' => 'PRIVATE EXPEDITION',
                'rating' => 4.85,
                'total_reviews' => 74,
                'is_featured' => false,
                'is_active' => true,
                'description' => 'Fenomena api biru abadi (Blue Fire) Kawah Ijen yang langka di dunia, danau toska asam terbesar, dan tradisi tangguh para penambang belerang.',
                'itineraries' => [
                    ['day' => 1, 'title' => 'Perjalanan ke Banyuwangi & Briefing Pos Paltuding', 'desc' => 'Tiba di Banyuwangi, istirahat di hotel lereng Ijen, dan persiapan perlengkapan gas mask sebelum pendakian malam.'],
                    ['day' => 2, 'title' => 'Pendakian Malam Blue Fire & Sunrise Kawah Ijen', 'desc' => 'Mulai mendaki jam 01.00 WIB, turun ke bibir kawah menyaksikan Blue Fire yang memesona dan danau kawah saat fajar menyingsing.'],
                ],
            ],
        ];

        foreach ($packagesData as $pkgInfo) {
            $itineraries = $pkgInfo['itineraries'] ?? [];
            unset($pkgInfo['itineraries']);

            $pkg = Package::updateOrCreate(
                ['slug' => $pkgInfo['slug']],
                $pkgInfo
            );

            // Seed itineraries
            $pkg->itineraries()->delete();
            foreach ($itineraries as $it) {
                Itinerary::create([
                    'package_id' => $pkg->id,
                    'day_number' => $it['day'],
                    'title' => $it['title'],
                    'description' => $it['desc'],
                ]);
            }
        }

        // 3. Trips & Batches (matching mockup indicators)
        $karimunPkg = Package::where('slug', 'karimunjawa-paradise-3d2n')->first();
        $bromoPkg = Package::where('slug', 'bromo-midnight-sunrise-express')->first();
        $diengPkg = Package::where('slug', 'dieng-highland-escapade-2d1n')->first();
        $jogjaPkg = Package::where('slug', 'jogja-heritage-borobudur-vip')->first();
        $ijenPkg = Package::where('slug', 'kawah-ijen-blue-fire-private')->first();

        $trip1 = Trip::updateOrCreate(
            ['package_id' => $karimunPkg->id, 'batch_name' => 'Karimunjawa Open Trip (24 Mei 2025)'],
            [
                'departure_date' => Carbon::parse('2025-05-24'),
                'return_date' => Carbon::parse('2025-05-26'),
                'quota' => 20,
                'booked_seats' => 18, // 90% penuh
                'status' => 'almost_full',
            ]
        );

        $trip2 = Trip::updateOrCreate(
            ['package_id' => $bromoPkg->id, 'batch_name' => 'Bromo Midnight Sunrise (25 Mei 2025)'],
            [
                'departure_date' => Carbon::parse('2025-05-25'),
                'return_date' => Carbon::parse('2025-05-25'),
                'quota' => 15,
                'booked_seats' => 14, // 93% penuh
                'status' => 'almost_full',
            ]
        );

        $trip3 = Trip::updateOrCreate(
            ['package_id' => $diengPkg->id, 'batch_name' => 'Dieng Negeri di Atas Awan (30 Mei 2025)'],
            [
                'departure_date' => Carbon::parse('2025-05-30'),
                'return_date' => Carbon::parse('2025-05-31'),
                'quota' => 20,
                'booked_seats' => 12, // 60% kuota
                'status' => 'available',
            ]
        );

        $trip4 = Trip::updateOrCreate(
            ['package_id' => $jogjaPkg->id, 'batch_name' => 'Jogja Heritage VIP (01 Jun 2025)'],
            [
                'departure_date' => Carbon::parse('2025-06-01'),
                'return_date' => Carbon::parse('2025-06-03'),
                'quota' => 15,
                'booked_seats' => 6,
                'status' => 'available',
            ]
        );

        $trip5 = Trip::updateOrCreate(
            ['package_id' => $ijenPkg->id, 'batch_name' => 'Kawah Ijen Blue Fire Private (02 Jun 2025)'],
            [
                'departure_date' => Carbon::parse('2025-06-02'),
                'return_date' => Carbon::parse('2025-06-03'),
                'quota' => 10,
                'booked_seats' => 2,
                'status' => 'available',
            ]
        );

        // 4. Bookings matching mockup
        $bookingsData = [
            [
                'code' => 'DJT-8921',
                'user' => $customer,
                'name' => 'Rian Adiputra',
                'email' => 'rian.adi@gmail.com',
                'phone' => '+62 812-9988-1122',
                'package' => $karimunPkg,
                'trip' => $trip1,
                'pax' => 2,
                'total' => 2450000,
                'method' => 'BCA Virtual Account',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ],
            [
                'code' => 'DJT-8920',
                'user' => null,
                'name' => 'Dewi Lestari M.',
                'email' => 'dewi.lestari@gmail.com',
                'phone' => '+62 812-4491-092',
                'package' => $bromoPkg,
                'trip' => $trip2,
                'pax' => 4,
                'total' => 3120000,
                'method' => 'Mandiri VA',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ],
            [
                'code' => 'DJT-8919',
                'user' => null,
                'name' => 'Agung Prakoso',
                'email' => 'agung.prk@corpo.co.id',
                'phone' => '+62 813-8822-4411',
                'package' => $diengPkg,
                'trip' => $trip3,
                'pax' => 1,
                'total' => 850000,
                'method' => 'QRIS Dinamis',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ],
            [
                'code' => 'DJT-8918',
                'user' => null,
                'name' => 'Fiona Sitanggang',
                'email' => 'fiona.s@yahoo.com',
                'phone' => '+62 821-7788-3344',
                'package' => $jogjaPkg,
                'trip' => $trip4,
                'pax' => 6,
                'total' => 6420000,
                'method' => 'BRI VA',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ],
            [
                'code' => 'DJT-8917',
                'user' => null,
                'name' => 'Hendra Kurnia',
                'email' => 'hendra.k@outlook.com',
                'phone' => '+62 878-1920-331',
                'package' => $ijenPkg,
                'trip' => $trip5,
                'pax' => 2,
                'total' => 1950000,
                'method' => 'BCA Virtual Account',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ],
        ];

        foreach ($bookingsData as $bData) {
            $booking = Booking::updateOrCreate(
                ['booking_code' => $bData['code']],
                [
                    'user_id' => $bData['user'] ? $bData['user']->id : null,
                    'trip_id' => $bData['trip']->id,
                    'package_id' => $bData['package']->id,
                    'customer_name' => $bData['name'],
                    'customer_email' => $bData['email'],
                    'customer_phone' => $bData['phone'],
                    'pax' => $bData['pax'],
                    'total_price' => $bData['total'],
                    'payment_method' => $bData['method'],
                    'payment_status' => $bData['payment_status'],
                    'booking_status' => $bData['booking_status'],
                ]
            );

            Transaction::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'transaction_code' => 'TRX-' . str_replace('-', '', $booking->booking_code),
                    'amount' => $booking->total_price,
                    'payment_method' => $booking->payment_method,
                    'payment_channel' => 'Online Gateway',
                    'paid_at' => Carbon::now()->subHours(rand(1, 48)),
                    'status' => 'success',
                ]
            );
        }

        // 5. Refund Disputed Entries (3 Tiket Ditahan, Rp 3.850.000)
        $refundData = [
            [
                'code' => 'DJT-8910',
                'refund_code' => 'REF-8910',
                'name' => 'Dimas Arya Putra',
                'email' => 'dimas.arya@gmail.com',
                'phone' => '+62 856-7890-1234',
                'package' => $karimunPkg,
                'trip' => $trip1,
                'pax' => 1,
                'amount' => 1250000,
                'reason' => 'Perubahan jadwal dinas mendadak ke luar kota dan tidak dapat mengikuti trip.',
            ],
            [
                'code' => 'DJT-8908',
                'refund_code' => 'REF-8908',
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nur@gmail.com',
                'phone' => '+62 813-4455-6677',
                'package' => $bromoPkg,
                'trip' => $trip2,
                'pax' => 2,
                'amount' => 1800000,
                'reason' => 'Anggota rombongan sakit, mengajukan refund 80% atau pertukaran voucher.',
            ],
            [
                'code' => 'DJT-8905',
                'refund_code' => 'REF-8905',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@yahoo.com',
                'phone' => '+62 822-1133-5577',
                'package' => $diengPkg,
                'trip' => $trip3,
                'pax' => 1,
                'amount' => 800000,
                'reason' => 'Penerbangan lanjutan menuju Jawa Tengah mengalami pembatalan dari maskapai.',
            ],
        ];

        foreach ($refundData as $r) {
            $refundBooking = Booking::updateOrCreate(
                ['booking_code' => $r['code']],
                [
                    'user_id' => null,
                    'trip_id' => $r['trip']->id,
                    'package_id' => $r['package']->id,
                    'customer_name' => $r['name'],
                    'customer_email' => $r['email'],
                    'customer_phone' => $r['phone'],
                    'pax' => $r['pax'],
                    'total_price' => $r['amount'],
                    'payment_method' => 'BCA Virtual Account',
                    'payment_status' => 'paid',
                    'booking_status' => 'refund_requested',
                ]
            );

            Refund::updateOrCreate(
                ['booking_id' => $refundBooking->id],
                [
                    'refund_code' => $r['refund_code'],
                    'reason' => $r['reason'],
                    'amount' => $r['amount'],
                    'status' => 'pending',
                ]
            );
        }

        // 6. Reviews
        $reviews = [
            [
                'package_id' => $karimunPkg->id,
                'customer_name' => 'Bambang Kusuma',
                'rating' => 5,
                'comment' => 'Paket Karimunjawa sangat memuaskan! Tour leader ramah, makanan lautnya segar banget dan spot snorkelingnya luar biasa jernih.',
                'is_approved' => true,
                'is_hidden' => false,
            ],
            [
                'package_id' => $karimunPkg->id,
                'customer_name' => 'Anisa Rahmawati',
                'rating' => 5,
                'comment' => 'Penyeberangan tertib dan penginapannya bersih. Anak-anak sangat senang melihat penangkaran hiu.',
                'is_approved' => true,
                'is_hidden' => false,
            ],
            [
                'package_id' => $bromoPkg->id,
                'customer_name' => 'Reza Pratama',
                'rating' => 5,
                'comment' => 'Driver jeep sangat handal di medan pasir. View sunrise di Bukit Kingkong benar-benar magis!',
                'is_approved' => true,
                'is_hidden' => false,
            ],
            [
                'package_id' => $diengPkg->id,
                'customer_name' => 'Maya Indah',
                'rating' => 4,
                'comment' => 'Hawa Dieng dingin sekali tapi pemandangan Telaga Warna dari bukit terbayar lunas. Carica oleh-olehnya enak.',
                'is_approved' => true,
                'is_hidden' => false,
            ],
            [
                'package_id' => $karimunPkg->id,
                'customer_name' => 'Spam Bot 99',
                'rating' => 1,
                'comment' => 'Promo pinjaman kilat tanpa jaminan hubungi wa 08999999',
                'is_approved' => false,
                'is_hidden' => true,
            ],
        ];

        foreach ($reviews as $rev) {
            Review::create($rev);
        }
    }
}
