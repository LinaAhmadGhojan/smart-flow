<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI chat assistant (optional — free tier via Groq or Google Gemini)
    |--------------------------------------------------------------------------
    |
    | Without an API key the bot uses built-in rules + product search (free).
    | With a key it understands natural questions like "كيف أتواصل مع الدعم؟".
    |
    | Groq (recommended, free): https://console.groq.com → API Keys
    | Gemini (free tier): https://aistudio.google.com/apikey
    |
    */

    'ai_enabled' => env('CHAT_AI_ENABLED', false),

    'provider' => env('CHAT_AI_PROVIDER', 'groq'), // groq | gemini

    'api_key' => env('CHAT_AI_API_KEY'),

    'model' => env('CHAT_AI_MODEL', 'allam-2-7b'),

    'gemini_model' => env('CHAT_GEMINI_MODEL', 'gemini-2.0-flash'),

    'timeout' => (int) env('CHAT_AI_TIMEOUT', 25),

    'max_tokens' => (int) env('CHAT_AI_MAX_TOKENS', 600),

];
