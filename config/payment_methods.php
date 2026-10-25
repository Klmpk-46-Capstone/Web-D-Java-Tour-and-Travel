<?php

return [
    'groups' => [
        'va' => [
            'label' => 'Transfer Bank / Virtual Account',
            'label_en' => 'Bank Transfer / Virtual Account',
            'icon' => '🏦',
            'methods' => [
                'BCA_VIRTUAL_ACCOUNT' => 'BCA Virtual Account',
                'BNI_VIRTUAL_ACCOUNT' => 'BNI Virtual Account',
                'BRI_VIRTUAL_ACCOUNT' => 'BRI Virtual Account',
                'MANDIRI_VIRTUAL_ACCOUNT' => 'Mandiri Virtual Account',
                'PERMATA_VIRTUAL_ACCOUNT' => 'Permata Virtual Account',
            ],
        ],

        'qris' => [
            'label' => 'Pembayaran QR',
            'label_en' => 'QR Payment',
            'icon' => '▦',
            'methods' => [
                'QRIS' => 'QRIS',
            ],
        ],

        'ewallet' => [
            'label' => 'E-Wallet',
            'label_en' => 'E-Wallet',
            'icon' => '📱',
            'methods' => [
                'DANA' => 'DANA',
                'OVO' => 'OVO',
                'SHOPEEPAY' => 'ShopeePay',
            ],
        ],

        // 'card' => [
        //     'label' => 'Kartu Kredit / Debit',
        //     'icon' => '💳',
        //     'methods' => [
        //         'CARDS' => 'Kartu Kredit / Debit',
        //     ],
        // ],

        // 'retail' => [
        //     'label' => 'Outlet Ritel',
        //     'icon' => '🏪',
        //     'methods' => [
        //         'ALFAMART' => 'Alfamart',
        //         'INDOMARET' => 'Indomaret',
        //     ],
        // ],

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
