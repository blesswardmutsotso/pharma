<?php

return [
    'name'       => env('COMPANY_NAME',       'LeafLight Client'),
    'legal_name' => env('COMPANY_LEGAL_NAME', ''),
    'address'    => env('COMPANY_ADDRESS',    ''),
    'phone'      => env('COMPANY_PHONE',      ''),
    'tin'        => env('COMPANY_TIN',        ''),
    'vat_reg'    => env('COMPANY_VAT_REG',    ''),
    'email'      => env('COMPANY_EMAIL',      ''),

    'phone_sales'  => env('COMPANY_PHONE_SALES', ''),
    'phone_office' => env('COMPANY_PHONE_OFFICE', ''),
    'phone_mobile' => env('COMPANY_PHONE_MOBILE', ''),
    'email_sales'  => env('COMPANY_EMAIL_SALES', ''),

    'vendor_number' => env('COMPANY_VENDOR_NUMBER', ''),

    'bank_name'           => env('COMPANY_BANK_NAME', ''),
    'bank_branch'         => env('COMPANY_BANK_BRANCH', ''),
    'bank_branch_code'    => env('COMPANY_BANK_BRANCH_CODE', ''),
    'bank_swift_code'     => env('COMPANY_BANK_SWIFT_CODE', ''),
    'bank_address'        => env('COMPANY_BANK_ADDRESS', ''),
    'bank_account_name'   => env('COMPANY_BANK_ACCOUNT_NAME', ''),
    'bank_account_number' => env('COMPANY_BANK_ACCOUNT_NUMBER', ''),

    'zig_bank_account_number' => env('COMPANY_ZIG_BANK_ACCOUNT_NUMBER', ''),
];
