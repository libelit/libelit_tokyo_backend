<?php

return [
    /*
    |--------------------------------------------------------------------------
    | XRPL Network Configuration
    |--------------------------------------------------------------------------
    */
    'testnet' => env('XRPL_TESTNET', true),

    'rpc_url' => env('XRPL_RPC_URL', 'https://s.altnet.rippletest.net:51234'),

    /*
    |--------------------------------------------------------------------------
    | Server Wallet Configuration
    |--------------------------------------------------------------------------
    | These credentials are used for signing audit trail transactions.
    | Get testnet credentials from: https://xrpl.org/resources/dev-tools/xrp-faucets
    */
    'server_address' => env('XRPL_SERVER_ADDRESS'),
    'server_seed' => env('XRPL_SERVER_SEED'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */
    'default_fee' => env('XRPL_DEFAULT_FEE', 12), // in drops

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'max_attempts' => env('XRPL_MAX_ATTEMPTS', 5),
];
