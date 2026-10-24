<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionMockupSeeder extends Seeder
{
    public function run(): void
    {
        $hiace = Vehicle::where('slug', 'toyota-hiace-premio-luxury')->first() ?? Vehicle::first();
        $zenix = Vehicle::where('slug', 'toyota-kijang-innova-zenix-hybrid')->first() ?? Vehicle::first();
        $bromo = Package::where('slug', 'like', '%bromo%')->first() ?? Package::first();
        $ijen = Package::where('slug', 'like', '%ijen%')->first() ?? Package::first();

        $samples = [
            [
                'booking_code' => 'BK-EJT-0192',
                'customer_name' => 'Hartono Kusumo',
                'customer_email' => 'hartono.k@gmail.com',
                'customer_phone' => '+62 811-9876-221',
                'pax' => 4,
                'total_price' => 3850000,
                'down_payment' => 1500000,
                'package_id' => $bromo?->id,
                'pickup_location' => 'Malang Pick-up',
                'transaction_code' => 'INV/EJT/2026/10/0192',
                'amount' => 1500000,
                'payment_method' => 'BCA Virtual Account',
                'payment_channel' => 'BCA VA Escrow',
                'payment_stage' => 'dp',
                'status' => 'success',
                'paid_at' => Carbon::now()->subHours(2),
                'notes' => 'Pax: 4 Orang (Malang Pick-up)',
            ],
            [
                'booking_code' => 'BK-EJT-0191',
                'customer_name' => 'Eleanor Vance (SG)',
                'customer_email' => 'eleanor.v@sg-corpo.com',
                'customer_phone' => '+65 9123 4489',
                'pax' => 5,
                'total_price' => 8700000,
                'down_payment' => 3000000,
                'vehicle_id' => $hiace?->id,
                'selected_armada' => 'HiAce Premio Luxury',
                'duration' => 3,
                'rental_option' => 'with_driver',
                'pickup_location' => 'Surabaya -> Banyuwangi Drop',
                'transaction_code' => 'INV/EJT/2026/10/0191',
                'amount' => 3000000,
                'payment_method' => 'Visa / Mastercard',
                'payment_channel' => 'Credit Card (Xendit)',
                'payment_stage' => 'dp',
                'status' => 'success',
                'paid_at' => Carbon::now()->subHours(5),
                'notes' => 'Surabaya -> Banyuwangi Drop',
            ],
            [
                'booking_code' => 'BK-EJT-0190',
                'customer_name' => 'Dr. Anindya Laksmi',
                'customer_email' => 'anindya.laksmi@rs-medika.co.id',
                'customer_phone' => '+62 812-3344-9008',
                'pax' => 2,
                'total_price' => 2450000,
                'down_payment' => 1000000,
                'package_id' => $ijen?->id,
                'pickup_location' => 'Banyuwangi Kota',
                'transaction_code' => 'INV/EJT/2026/10/0190',
                'amount' => 1000000,
                'payment_method' => 'QRIS Instant',
                'payment_channel' => 'QRIS Mandiri Instant',
                'payment_stage' => 'dp',
                'status' => 'success',
                'paid_at' => Carbon::now()->subHours(12),
                'notes' => 'Pax: 2 Orang + Gas Masker Pro',
            ],
            [
                'booking_code' => 'BK-EJT-0189',
                'customer_name' => 'Rian Firmansyah',
                'customer_email' => 'rian.firmansyah@gmail.com',
                'customer_phone' => '+62 878-1090-3321',
                'pax' => 4,
                'total_price' => 2100000,
                'down_payment' => 1500000,
                'vehicle_id' => $zenix?->id,
                'selected_armada' => 'Innova Zenix Hybrid',
                'duration' => 2,
                'rental_option' => 'with_driver',
                'transaction_code' => 'INV/EJT/2026/10/0189',
                'amount' => 1500000,
                'payment_method' => 'BCA Virtual Account',
                'payment_channel' => 'BCA VA Escrow',
                'payment_stage' => 'dp',
                'status' => 'refunded',
                'paid_at' => Carbon::now()->subDays(1),
                'notes' => 'Dibatalkan Tamu (Sakit)',
            ],
            [
                'booking_code' => 'BK-EJT-0188',
                'customer_name' => 'Chen Wei (MY)',
                'customer_email' => 'chen.wei@kualalumpur.my',
                'customer_phone' => '+60 16-778-9011',
                'pax' => 6,
                'total_price' => 12500000,
                'down_payment' => 4000000,
                'package_id' => $bromo?->id,
                'pickup_location' => 'Bandara Juanda T2',
                'transaction_code' => 'INV/EJT/2026/10/0188',
                'amount' => 4000000,
                'payment_method' => 'Mandiri VA',
                'payment_channel' => 'Mandiri VA Escrow',
                'payment_stage' => 'dp',
                'status' => 'pending',
                'paid_at' => null,
                'notes' => 'Pax: 6 Orang + HiAce Commuter',
            ],
        ];

        foreach ($samples as $data) {
            $booking = Booking::firstOrCreate(
                ['booking_code' => $data['booking_code']],
                [
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'],
                    'customer_phone' => $data['customer_phone'],
                    'pax' => $data['pax'],
                    'total_price' => $data['total_price'],
                    'down_payment' => $data['down_payment'],
                    'package_id' => $data['package_id'] ?? null,
                    'vehicle_id' => $data['vehicle_id'] ?? null,
                    'selected_armada' => $data['selected_armada'] ?? null,
                    'duration' => $data['duration'] ?? null,
                    'rental_option' => $data['rental_option'] ?? null,
                    'pickup_location' => $data['pickup_location'] ?? null,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => $data['status'] === 'success' ? 'paid' : ($data['status'] === 'refunded' ? 'refunded' : 'unpaid'),
                    'booking_status' => $data['status'] === 'refunded' ? 'cancelled' : 'confirmed',
                    'notes' => $data['notes'] ?? null,
                ]
            );

            Transaction::firstOrCreate(
                ['transaction_code' => $data['transaction_code']],
                [
                    'booking_id' => $booking->id,
                    'amount' => $data['amount'],
                    'payment_method' => $data['payment_method'],
                    'payment_channel' => $data['payment_channel'],
                    'payment_stage' => $data['payment_stage'],
                    'status' => $data['status'],
                    'paid_at' => $data['paid_at'],
                ]
            );
        }
    }
}
