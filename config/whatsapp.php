<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ultramsg WhatsApp API
    |--------------------------------------------------------------------------
    | Sign up at https://ultramsg.com, create an instance, scan the QR code
    | with the WhatsApp number you want to send from, then copy the
    | Instance ID and Token from your dashboard into the .env file.
    */
    'ultramsg' => [
        'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
        'token' => env('ULTRAMSG_TOKEN'),
        'base_url' => env('ULTRAMSG_BASE_URL', 'https://api.ultramsg.com'),
    ],

    // Master switch — set WHATSAPP_NOTIFICATIONS_ENABLED=true once the
    // Ultramsg instance above is authenticated and ready to send.
    'enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', false),
];
