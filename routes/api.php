<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\TranslationController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $data['email'])->first();
    if (! $user || ! Hash::check($data['password'], $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 422);
    }

    $token = $user->createToken('api')->plainTextToken;
    return response()->json(['token' => $token]);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/translations', [TranslationController::class, 'index']);
    Route::post('/translations', [TranslationController::class, 'store']);
    Route::get('/translations/{id}', [TranslationController::class, 'show']);
    Route::put('/translations/{id}', [TranslationController::class, 'update']);

    Route::get('/export', [ExportController::class, 'index']);
});
