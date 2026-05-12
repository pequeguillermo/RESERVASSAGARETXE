<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request();
$request->merge(['date' => '2026-05-12', 'shift' => 'morning']);

try {
    $controller = app(\App\Http\Controllers\Admin\SettingsController::class);
    $controller->quickCloseShift($request);
    echo "OK";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
