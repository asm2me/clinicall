<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return response()->json([
        'service' => 'clinicall-backend',
        'status' => 'ok',
    ]);
});