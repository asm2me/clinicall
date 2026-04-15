<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([ResolveTenant::class])->group(static function (): void {
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
});