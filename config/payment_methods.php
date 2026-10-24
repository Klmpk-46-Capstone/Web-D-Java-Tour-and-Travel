<?php

return [
    'groups' => [
        'va' => [
            'label' => 'Transfer Bank / Virtual Account',
            'icon' => '🏦',
            'methods' => [
                'BCA' => 'BCA Virtual Account',
                'BNI' => 'BNI Virtual Account',
                'BRI' => 'BRI Virtual Account',
                'MANDIRI' => 'Mandiri Virtual Account',
                'PERMATA' => 'Permata Virtual Account',
            ],
        ],

        'qris' => [
            'label' => 'Pembayaran QR',
            'icon' => '▦',
            'methods' => [
                'QRIS' => 'QRIS',
            ],
        ],

        'ewallet' => [
            'label' => 'E-Wallet',
            'icon' => '📱',
            'methods' => [
                'DANA' => 'DANA',
                'OVO' => 'OVO',
                'SHOPEEPAY' => 'ShopeePay',
            ],
        ],

        'card' => [
            'label' => 'Kartu Kredit / Debit',
            'icon' => '💳',
            'methods' => [
                'CARDS' => 'Kartu Kredit / Debit',
            ],
        ],

        'retail' => [
            'label' => 'Outlet Ritel',
            'icon' => '🏪',
            'methods' => [
                'ALFAMART' => 'Alfamart',
                'INDOMARET' => 'Indomaret',
            ],
        ],

        /*
         * Direct Debit memerlukan channel yang diaktifkan pada akun.
         * Jangan menambahkan kode channel berdasarkan tebakan.
         * Isi daftar ini setelah mencocokkan channel code di dashboard
         * atau dokumentasi Xendit untuk akunmu.
         */
        // 'debit' => [
        //     'label' => 'Direct Debit',
        //     'icon' => '🏦',
        //     'methods' => [],
        // ],
    ],
];
