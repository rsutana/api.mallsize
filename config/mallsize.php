<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    |
    | "fake" settles every payment instantly (local dev and tests).
    | "paymongo" uses PayMongo Payment Intents for GCash, Maya, QR Ph,
    | ShopeePay and cards.
    |
    */

    'payments' => [
        'driver' => env('PAYMENTS_DRIVER', 'fake'),
        'currency' => 'PHP',
        'min_partial_amount' => 100_000,
        'paymongo' => [
            'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
            'public_key' => env('PAYMONGO_PUBLIC_KEY'),
            'secret_key' => env('PAYMONGO_SECRET_KEY'),
            'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS
    |--------------------------------------------------------------------------
    |
    | "log" writes messages to the application log. "semaphore" sends them
    | through the Semaphore SMS API (https://semaphore.co).
    |
    */

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'semaphore' => [
            'base_url' => env('SEMAPHORE_BASE_URL', 'https://api.semaphore.co/api/v4'),
            'api_key' => env('SEMAPHORE_API_KEY'),
            'sender_name' => env('SEMAPHORE_SENDER_NAME', 'MALLSIZE'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enrollment
    |--------------------------------------------------------------------------
    */

    'pending_enrollment_ttl_minutes' => (int) env('PENDING_ENROLLMENT_TTL_MINUTES', 30),

    'reference_prefixes' => [
        'student' => 'MS',
        'receipt' => 'MS-RC',
    ],

    'staff_notification_email' => env('STAFF_NOTIFICATION_EMAIL', 'enroll@mallsize.ph'),

];
