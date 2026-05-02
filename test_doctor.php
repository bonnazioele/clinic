<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$doctor = \App\Models\User::where('is_doctor', true)->with('clinics')->first();
if ($doctor) {
    echo "Email: {$doctor->email}\n";
    echo "Clinics: " . $doctor->clinics->pluck('name')->join(', ') . "\n";
} else {
    echo "No doctor found\n";
}
