<?php

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use App\Notifications\ConfirmEmailNotification;
use App\Notifications\OtpSmsNotification;
use App\Providers\TwilioService;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('profile', [AuthController::class, 'updateProfile']);

    // Mot de passe oublié
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::middleware('auth:sanctum')->delete('delete-account', [AuthController::class, 'deleteAccount']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
});



// Route::post('/test-notification', function (\Illuminate\Http\Request $request) {
//     $request->validate([
//         'email' => 'nullable|email',
//         'phone' => 'nullable|string',
//     ]);

//     if (!$request->email && !$request->phone) {
//         return response()->json(['error' => 'Email ou téléphone requis'], 422);
//     }

//     // On crée un utilisateur temporaire pour tester
//     $user = new User();
//     $user->email = $request->email;
//     $user->phone = $request->phone;

//     // 🔹 Cas Email → envoi OTP par mail
//     if ($request->filled('email')) {
//         $otp = rand(100000, 999999);
//         $user->notify(new ConfirmEmailNotification($otp));
//         return response()->json([
//             'status' => 'OTP email envoyé',
//             'otp' => $otp // seulement pour test
//         ]);
//     }

//     // 🔹 Cas SMS → envoi OTP par Twilio
//     if ($request->filled('phone')) {
//         $otp = rand(100000, 999999);
//         $twilio = new TwilioService();
//         $twilio->sendSms($request->phone, "Votre code OTP DrinkEazy est : {$otp}");
//         return response()->json([
//             'status' => 'OTP SMS envoyé',
//             'otp' => $otp // seulement pour test
//         ]);
//     }
// });