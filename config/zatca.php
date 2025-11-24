<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ZATCA Environment
    |--------------------------------------------------------------------------
    | sandbox | production
    */
    'environment' => env('ZATCA_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Seller Information (default)
    |--------------------------------------------------------------------------
    */
    'seller' => [
        'name' => env('ZATCA_SELLER_NAME', 'Your Company Name'),
        'vat'  => env('ZATCA_SELLER_VAT', '000000000000003'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Credentials (Phase 2)
    |--------------------------------------------------------------------------
    */
    'token' => env('ZATCA_TOKEN', null),

    'endpoints' => [
        'sandbox' => [
            'clearance' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core/clearance',
            'reporting' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core/reporting',
        ],
        'production' => [
            'clearance' => 'https://gw.apis.zatca.gov.sa/e-invoicing/core/clearance',
            'reporting' => 'https://gw.apis.zatca.gov.sa/e-invoicing/core/reporting',
        ],
    ],
];
