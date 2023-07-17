<?php

return [

    'base_url' => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),

    'api_key' => env('MONNIFY_API_KEY', ''),

    'secret_key' => env('MONNIFY_SECRET_KEY', ''),

    'contract_code' => env('MONNIFY_CONTRACT_CODE', ''),

    'source_account_number' => env('MONNIFY_SOURCE_ACCOUNT_NUMBER', ''),

    'default_split_percentage' => env('MONNIFY_DEFAULT_SPLIT_PERCENTAGE', 20),

    'default_currency_code' => env('MONNIFY_DEFAULT_CURRENCY_CODE', 'NGN'),

    'redirect_url' => env('MONNIFY_DEFAULT_REDIRECT_URL', env('APP_URL')),

];
