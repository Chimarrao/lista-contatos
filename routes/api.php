<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\GeocodeController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/reset', [AuthController::class, 'sendResetLinkEmail'])->name('password.reset');
Route::post('/delete-account', [AuthController::class, 'deleteAccount']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/cep', [CepController::class, 'lookup'])->name('cep.lookup');
    Route::post('/geocode', [GeocodeController::class, 'lookup'])->name('geocode.lookup');
});

Route::middleware([JwtMiddleware::class])->prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::put('/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
});
