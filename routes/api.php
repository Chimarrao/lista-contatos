<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
Route::post('/password/reset', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/delete-account', [AuthController::class, 'deleteAccount']);

Route::middleware([JwtMiddleware::class])->prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::put('/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::post('/delete-account', function (Request $request) {
        //
    })->name('account.delete');

    Route::post('/cep', function (Request $request) {
        $cep = $request->input('cep');
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
        return $response->json();
    })->name('cep.lookup');

    Route::post('/geocode', function (Request $request) {
        $address = $request->input('address');
        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $address,
            'key' => env('GOOGLE_MAPS_API_KEY')
        ]);
        return $response->json();
    })->name('geocode.lookup');

});
