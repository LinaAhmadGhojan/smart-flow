<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>SmartFlow - حلول المنزل الذكي في دبي، الإمارات | Smart Home Solutions Dubai UAE</title>
    <meta name="description" content="أفضل حلول المنزل الذكي في دبي والإمارات. SmartFlow - شريكك الموثوق في الأتمتة المنزلية والأنظمة الذكية. Smart home automation solutions in Dubai, UAE.">
    <meta name="keywords" content="منزل ذكي, دبي, الإمارات, أتمتة منزلية, smart home, Dubai, UAE, home automation, IoT, smart solutions">
    <meta name="author" content="SmartFlow">
    <meta name="robots" content="index, follow">
    
    <!-- Geo Tags -->
    <meta name="geo.region" content="AE-DU">
    <meta name="geo.placename" content="Dubai">
    <meta name="geo.position" content="25.2048;55.2708">
    <meta name="ICBM" content="25.2048, 55.2708">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="SmartFlow - حلول المنزل الذكي في دبي | Smart Home Dubai">
    <meta property="og:description" content="أفضل حلول المنزل الذكي في دبي والإمارات - Best smart home solutions in Dubai, UAE">
    <meta property="og:image" content="{{ asset('logo.jpeg') }}">
    <meta property="og:locale" content="ar_AE">
    <meta property="og:locale:alternate" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SmartFlow - Smart Home Dubai UAE">
    <meta name="twitter:description" content="أفضل حلول المنزل الذكي في دبي والإمارات">
    <meta name="twitter:image" content="{{ asset('logo.jpeg') }}">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpeg') }}">
    
    <!-- Language Alternates -->
    <link rel="alternate" hreflang="ar" href="{{ url('/') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/') }}">
    <link rel="alternate" hreflang="ar-ae" href="{{ url('/') }}">
    <link rel="alternate" hreflang="en-ae" href="{{ url('/') }}">
    
    @vite(['resources/css/app.css', 'resources/js/main.ts'])
    
    <!-- JSON-LD Schema for Local Business -->
    <script type="application/ld+json">
    @php
        echo json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'SmartFlow',
            'alternateName' => 'سمارت فلو',
            'description' => 'Smart Home Solutions Provider in Dubai, UAE',
            'image' => asset('logo.jpeg'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Dubai',
                'addressCountry' => 'AE'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '25.2048',
                'longitude' => '55.2708'
            ],
            'url' => url('/'),
            'telephone' => '+971562566232',
            'priceRange' => '$$',
            'openingHours' => 'Su-Th 09:00-18:00',
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.8',
                'reviewCount' => '127'
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    @endphp
    </script>
</head>
<body>
    <div id="app"></div>
</body>
</html>
