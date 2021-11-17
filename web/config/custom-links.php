<?php

return [
    'links' => [

        /*
         * '{TEXT}' => [
         *      '_icon'   => '{ICON}',
         *      '_url'    => '{URL}',    # Optional if _links is present
         *      '_target' => '{TARGET}',
         *      '_links'  => [           # Optional if _url is present
         *          '{TEXT}' => [
         *              '_url'    => '{URL}',
         *              '_target' => '{TARGET}',
         *          ]
         *          '{TEXT}' => [
         *              '_url'    => '{URL}',
         *              '_target' => '{TARGET}',
         *          ]
         *      ]
         * ]
         */

        'Memberships' => [
            '_icon'   => '<img src="/icons/user.png" alt="user icon">',
            '_links'  => [
                'Users' => [
                    '_url'    => '/admin/resources/users',
                ],
                'Partners' => [
                    '_url'    => '/admin/resources/partners',
                ],
                'Couriers' => [
                    '_url'    => '/admin/resources/couriers',
                ],
                'Cities' => [
                    '_url'    => '/admin/resources/cities',
                ],
                'Countries' => [
                    '_url'    => '/admin/resources/countries',
                ],
            ],
        ],
        'Orders' => [
            '_icon'   =>  '<img src="/icons/order1.png" alt="user icon">',
            '_links'  => [
                'Orders' => [
                    '_url'    => '/admin/resources/orders',
                ],
                'Albums' => [
                    '_url'    => '/admin/resources/albums',
                ],
                // 'Receipts' => [
                //     '_url'    => '/admin/resources/receipts',
                // ],
                // 'Shipping Prices' => [
                //     '_url'    => '/admin/shipping',
                // ],
                'Products' => [
                    '_url'    => '/admin/resources/products',
                ],
                // 'Product Price' => [
                //     '_url'    => '/admin/productprice',
                // ],
            ],
        ],
        'Payments' => [
            '_icon'   =>  '<img src="/icons/payment.png" alt="user icon">',
            '_links'  => [
                'Payments' => [
                    '_url'    => '/admin/resources/payments',
                ],
                'Transactions' => [
                    '_url'    => '/admin/resources/transactions',
                ],
                'Packages' => [
                    '_url'    => '/admin/resources/packages',
                ],
                'Refunds' => [
                    '_url'    => '/admin/resources/refunds',
                ],
                'Coupons' => [
                    '_url'    => '/admin/resources/coupons',
                ],
            ],
        ],
        'Analytics' => [
            '_icon'   => '<img src="/icons/monitor.png" alt="user icon">',
            '_links'  => [
                'Coupons Reports' => [
                    '_url'    => '/admin/resources/coupons-reports',
                ],
                'Customer Activities' => [
                    '_url'    => '/admin/resources/customer-activities',
                ],
                'Failed Payments' => [
                    '_url'    => '/admin/resources/failed-payments',
                ],
                'Payment Reports' => [
                    '_url'    => '/admin/resources/payment-reports',
                ],
                'Sales Reports' => [
                    '_url'    => '/admin/resources/sales-reports',
                ],
            ],
        ],
        'System Customization' => [
            '_icon'   => '<img src="/icons/system.png" alt="user icon">',
            '_links'  => [
                'Languages' => [
                    '_url'    => '/admin/resources/languages',
                ],
                'Emails' => [
                    '_url'    => '/admin/resources/emails',
                ],
            ],
        ],



    ]
];
