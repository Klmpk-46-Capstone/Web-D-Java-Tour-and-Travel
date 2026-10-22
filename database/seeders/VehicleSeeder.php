<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Armada contoh yang dipakai pada halaman katalog armada.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'name' => 'Toyota All New Avanza',
                'slug' => 'toyota-all-new-avanza',
                'category' => 'mpv',
                'badge' => 'Favorit Keluarga',
                'seats' => 6,
                'luggage_capacity' => 2,
                'transmission' => 'Matic / Manual',
                'price_per_day' => 550000,
                'price_with_driver' => 550000,
                'price_without_driver' => 400000,
                'includes_driver' => true,
                'description' => 'Lincah dan hemat bahan bakar untuk keliling Surabaya & Malang.',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBizpsB8qWcrXuSHjdXl7a4RXJIAxypGwlInIV2x2qx18ULGvqtnhucJpOBo-EtEMt2m_XX1HZxrrxxEBZ_z4-aOpwfwzOB1LZuhStlxCySWu3vzMX7UdyFaFjNOUoZzQRz8oYpIuWApZV9_u9xBwMwIPPsJzA5c4JT6NhEr5TjadGnhvzxp2ZELCiiTJXpGq2s0b1YTvi3ivHdQQaSBfHuLfSjIMJpRte22U7cX2dtz21ZiF3mv0U',
            ],
            [
                'name' => 'Suzuki All New Ertiga',
                'slug' => 'suzuki-all-new-ertiga',
                'category' => 'mpv',
                'badge' => 'Ekonomis Nyaman',
                'seats' => 6,
                'luggage_capacity' => 2,
                'transmission' => 'Matic',
                'price_per_day' => 550000,
                'price_with_driver' => 550000,
                'price_without_driver' => 400000,
                'includes_driver' => true,
                'description' => 'Kabin senyap dengan suspensi lembut untuk rute tol panjang Jawa Timur.',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC5mmL1bzToLb3j5b7moGOZ0VMUAilG2v78rDfmL_-qIo2w5NMQvw7z0FJfCrd07UHd9ypF4LoJ4t6FPkFQRL_O-obe4nLxTRCOFhhhSO_uJFSpBqjpsvm31UoYdVEGQsOWGtcgzPG8O1Zeh90TMm6vUQiwLPQEohkqd60SnMyNj_Ki5pK5ZESvRcIyJzYlx8Iq8qY-w3_RWsrB47V5eXSm17OTy6Lk-lIH6ryoUqFC4Ekc3QqWZR0',
            ],
            [
                'name' => 'Toyota Innova Reborn',
                'slug' => 'toyota-innova-reborn',
                'category' => 'mpv',
                'badge' => 'Tangguh Tanjakan',
                'seats' => 7,
                'luggage_capacity' => 4,
                'transmission' => 'Automatic',
                'price_per_day' => 850000,
                'price_with_driver' => 850000,
                'price_without_driver' => 650000,
                'includes_driver' => true,
                'description' => 'Tenaga diesel bertenaga, pilihan utama rute Bromo, Batu, dan Ijen.',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBto9_zWE_gCdpNRII5Kh-trg7aF_ZI2uilnNJm75DyaBKTMRVIkvEGe_sYOpcfyyKRRfgoGtrALrSMwOp3iC9eWfw7u_mcBHgN_6tXrP4TE2Khq83o9KVAfqM7D-gIS3HSXJohcbHYwGZQU8jhY1PjaxysJDXTcr_CDOZCwu_uWlNHlNJRoXd5hMR1S-nO-9-bYc11c9jCg0OckDo1q153IBrxjDe6y08OmCYWjqwSH74duV7W1_o',
            ],
            [
                'name' => 'Toyota Innova Zenix Hybrid',
                'slug' => 'toyota-innova-zenix-hybrid',
                'category' => 'premium',
                'badge' => 'Captain Seat',
                'seats' => 6,
                'luggage_capacity' => 4,
                'transmission' => 'Automatic',
                'price_per_day' => 1100000,
                'price_with_driver' => 1100000,
                'price_without_driver' => 900000,
                'includes_driver' => true,
                'description' => 'Kenyamanan eksekutif dengan sunroof panorama dan kabin ultra hening.',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDRovVCNRENRdLXboy3Qe0074-FzIgFuTFc2pX0n_u1GkTglDWUL0NA212JT5h5GsRHX1RbMSHa5jundON6onhHrX7eLI5stAUpZovOxUvE87dPA-ytmZ0JUoDknnrC7bGpyxETPG4zMJ21vyXkKIOE4wXp58H_9riW5dD33wUI9ThQSy0SyFd9RZKnHSZoLD-kFMuykFbYstk-VAIoM3trHy6Bbt_B8V-T_qRH6KV2nIeSyNdLY_0',
            ],
            [
                'name' => 'Toyota HiAce Commuter',
                'slug' => 'toyota-hiace-commuter',
                'category' => 'minibus',
                'badge' => 'Kapasitas Maksimal',
                'seats' => 14,
                'luggage_capacity' => 6,
                'transmission' => 'Manual',
                'price_per_day' => 1300000,
                'price_with_driver' => 1300000,
                'price_without_driver' => 1050000,
                'includes_driver' => true,
                'description' => 'Solusi terbaik rombongan keluarga besar, kantor, dan komunitas wisata.',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuC1g0LscfbzS832c-XimvDmaSoehFqTauYtWUyiYTnie2JQcNYunpIRyUB41oudLXec1JxV6CVK5GJTwVtym2PwtB2Tmr6UdXqomHgvtalqVOul0J3pE9ZfTHPPizkRBtK_kj0MPCtjqW5gr_OSoFSV3DxzFk_yPtihS4kRiSTcy0uxjTU38Sw4re3V4F3YmOkOKufvt8sZulwPJy1_NXxHwL7zvnt6rM-ekAvUqvXk_PnhlnbDVsI',
            ],
            [
                'name' => 'HiAce Premio Luxury',
                'slug' => 'hiace-premio-luxury',
                'category' => 'premium',
                'badge' => 'VIP Pilot Seat',
                'seats' => 12,
                'luggage_capacity' => 5,
                'transmission' => 'Automatic',
                'price_per_day' => 1600000,
                'price_with_driver' => 1600000,
                'price_without_driver' => 1350000,
                'includes_driver' => true,
                'description' => 'Interior custom VIP dengan jok captain recliner, port USB, dan audio bioskop.',
                'image_path' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCi_HNiS1ZB3HUt47LEaI4xrHppjXh5s8VMgnHGSXl7IhJEx8XCXZVogQ7nGCoe2S2v1ERQEPE2J-5XM-aFuVDockMv_602ejaFqCNeBU_ol8KW7odEWa1DKzfLz2c65Pqo-Nc28cOU3ZK_qFs_4-QpI1QxXkiyTPcHCsaJQC28686oodkt0VGYvp554VAzLTe0WMLVCrAauobFOejwROI-4BMMPCGL1gRVxVebdL83tmGKPMYj1uA',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['slug' => $vehicle['slug']],
                $vehicle
            );
        }
    }
}
