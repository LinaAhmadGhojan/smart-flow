<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = App\Models\Admin::where('email', 'admin@smartflow.com')->first();
if (!$admin) {
    echo "admin missing\n";
    exit(1);
}

$admin->password = Illuminate\Support\Facades\Hash::make('Smartflow@2026');
$admin->save();
echo "password reset ok\n";
