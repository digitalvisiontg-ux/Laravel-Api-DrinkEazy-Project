<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\ConfirmEmailNotification;
use App\Notifications\WelcomeUserNotification;
use App\Providers\PhoneNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\OtpCode;
use App\Providers\TwilioService;

class AuthController extends Controller
{
    // --- REGISTER ---
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'nullable|string|unique:users,phone',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if (!$request->phone && !$request->email) {
                return response()->json(['message' => 'Email ou téléphone requis'], 422);
            }

            $user = new User();
            $user->name = $request->name;
            $user->password = Hash::make($request->password);

            if ($request->phone) {
                $normalizedPhone = PhoneNormalizer::normalizeTg($request->phone) ?? PhoneNormalizer::normalizeGab($request->phone);
                if (!$normalizedPhone) {
                    return response()->json(['message' => 'Numéro invalide'], 422);
                }
                $user->phone = $normalizedPhone;
            }

            if ($request->email) {
                $user->email = $request->email;
            }

            $user->save();

            // 🔹 Notification bienvenue
            if ($user->phone) {
                $twilio = new TwilioService();
                $twilio->sendSms($user->phone, "Bienvenue sur DrinkEazy 🍹 ! Votre compte a été créé avec succès.");
            }

            if ($user->email) {
                $user->notify(new WelcomeUserNotification());
            }

            return response()->json([
                'message' => 'Compte créé avec succès',
                'user' => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
        }
    }

    // --- SEND OTP SMS ---
    // public function sendOtp(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'phone' => 'nullable|string',
    //             'email' => 'nullable|email',
    //         ]);

    //         // Vérifie qu’au moins un champ est présent
    //         if (!$request->phone && !$request->email) {
    //             return response()->json(['message' => 'Veuillez fournir un numéro de téléphone ou un email'], 422);
    //         }

    //         // --- CAS 1 : ENVOI PAR TÉLÉPHONE ---
    //         if ($request->phone) {
    //             $phone = PhoneNormalizer::normalizeTg($request->phone)
    //                 ?? PhoneNormalizer::normalizeGab($request->phone);

    //             if (!$phone) {
    //                 return response()->json(['message' => 'Numéro invalide'], 422);
    //             }

    //             $user = User::where('phone', $phone)->first();
    //             if (!$user) {
    //                 return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    //             }

    //             // Empêcher le renvoi trop rapide
    //             $recentOtp = OtpCode::where('phone', $phone)
    //                 ->where('expires_at', '>', Carbon::now())
    //                 ->latest()->first();

    //             if ($recentOtp && Carbon::now()->diffInSeconds($recentOtp->created_at) < 60) {
    //                 return response()->json(['message' => 'Veuillez attendre avant de renvoyer un OTP'], 429);
    //             }

    //             // Génération OTP
    //             $otpData = OtpCode::generateForPhone($phone, 6, 15, $user->id, 'sms');
    //             $twilio = new TwilioService();
    //             $twilio->sendSms($user->phone, "Votre code OTP DrinkEazy est : {$otpData['otp']}. Si vous n'avez pas demandé ce code, ignorez ce message.");

    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'OTP envoyé par SMS',
    //                 'data' => [
    //                     'user_id' => $user->id,
    //                     'otp' => $otpData['otp'] // utile pour test
    //                 ]
    //             ]);
    //         }

    //         // --- CAS 2 : ENVOI PAR EMAIL ---
    //         if ($request->email) {
    //             $email = $request->email;
    //             $user = User::where('email', $email)->first();

    //             if (!$user) {
    //                 return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    //             }

    //             // Empêcher le renvoi trop rapide
    //             $recentOtp = OtpCode::where('email', $email)
    //                 ->where('expires_at', '>', Carbon::now())
    //                 ->latest()->first();

    //             if ($recentOtp && Carbon::now()->diffInSeconds($recentOtp->created_at) < 60) {
    //                 return response()->json(['message' => 'Veuillez attendre avant de renvoyer un OTP'], 429);
    //             }

    //             // Génération OTP
    //             $otpData = OtpCode::generateForEmail($email, 6, 15, $user->id, 'email');

    //             // Envoi email
    //             $user->notify(new ConfirmEmailNotification($otpData['otp']));

    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'OTP envoyé par email',
    //                 'data' => [
    //                     'user_id' => $user->id,
    //                     'otp' => $otpData['otp'] // utile pour test
    //                 ]
    //             ]);
    //         }
    //     } catch (\Throwable $th) {
    //         return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
    //     }
    // }


    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string', // email ou phone
                'otp' => 'required|digits:6',
            ]);

            // 🔹 Trouver l'utilisateur
            $user = User::where('email', $request->login)
                ->orWhere('phone', $request->login)
                ->first();

            if (!$user) {
                return response()->json(['message' => 'Utilisateur introuvable'], 404);
            }

            // 🔹 Chercher le code OTP actif
            $otpRecord = OtpCode::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$otpRecord) {
                return response()->json(['message' => 'Aucun OTP valide trouvé'], 404);
            }

            // 🔹 Vérifier le nombre d’essais
            if ($otpRecord->attempts >= 5) {
                return response()->json(['message' => 'Nombre maximum de tentatives atteint'], 429);
            }

            // 🔹 Incrémenter le compteur d’essais
            $otpRecord->attempts += 1;
            $otpRecord->save();

            // 🔹 Vérifier le code (hash)
            if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
                return response()->json(['message' => 'Code OTP invalide'], 422);
            }

            // 🔹 Validation réussie → on marque l'utilisateur comme vérifié
            if ($otpRecord->channel === 'sms' && !$user->phone_verified_at) {
                $user->phone_verified_at = now();
            }

            if ($otpRecord->channel === 'email' && !$user->email_verified_at) {
                $user->email_verified_at = now();
            }

            $user->save();


            // 🔹 Générer un token Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Vérification réussie ✅',
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
        }
    }

    // --- LOGIN ---
public function login(Request $request)
{
    try {
        $request->validate([
            'login' => 'required|string', // ou juste 'string' si login = email ou phone
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->login)
            ->orWhere('phone', $request->login)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Email ou numéro incorrect'], 401);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Mot de passe incorrect'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => $user,
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Format de l\'email invalide'], 422);
    } catch (\Throwable $th) {
        return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
    }
}

    public function resetPassword(Request $request)
    {
        try {
            // Validation des champs
            $request->validate([
                'login' => 'required|string', // email ou téléphone
                'otp' => 'required|string|size:6',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $login = $request->login;
            $user = null;

            // Détecter si email ou téléphone
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $login)->first();
            } else {
                $phone = PhoneNormalizer::normalizeTg($login) ?? PhoneNormalizer::normalizeGab($login);
                if (!$phone) {
                    return response()->json(['message' => 'Numéro invalide'], 422);
                }
                $user = User::where('phone', $phone)->first();
            }

            if (!$user) {
                return response()->json(['message' => 'Utilisateur introuvable'], 404);
            }

            // Vérifier que l'OTP existe et est valide
            $otpRecord = OtpCode::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (!$otpRecord) {
                return response()->json(['message' => 'OTP expiré ou introuvable'], 404);
            }

            // Vérification du code OTP
            if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
                return response()->json(['message' => 'OTP invalide'], 422);
            }

            // 🔹 Mise à jour du mot de passe
            $user->password = Hash::make($request->password);
            $user->save();

            // Supprimer l'OTP après succès
            $otpRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
        }
    }


    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string',
            ]);

            $login = $request->login;

            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $login)->first();
                if (!$user)
                    return response()->json(['message' => 'Utilisateur introuvable'], 404);

                $otpData = OtpCode::generateForEmail($user->email, 6, 5, $user->id, 'email');
                $user->notify(new ConfirmEmailNotification($otpData['otp']));

                return response()->json(['success' => true,'message' => 'OTP envoyé par email']);
            } else {
                $phone = PhoneNormalizer::normalizeTg($login) ?? PhoneNormalizer::normalizeGab($login);
                if (!$phone)
                    return response()->json(['message' => 'Numéro invalide'], 422);

                $user = User::where('phone', $phone)->first();
                if (!$user)
                    return response()->json(['message' => 'Utilisateur introuvable'], 404);

                $otpData = OtpCode::generateForPhone($phone, 6, 5, $user->id, 'sms');
                $twilio = new TwilioService();
                $twilio->sendSms($phone, "Votre code OTP DrinkEazy est : {$otpData['otp']}");

                return response()->json(['success' => true,'message' => 'OTP envoyé par SMS']);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user(); // récupère l'utilisateur connecté
            $user->delete(); // supprime l'utilisateur

            return response()->json([
                'message' => 'Compte supprimé avec succès',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
{
    try {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    } catch (\Throwable $th) {
        return response()->json(['message' => 'Erreur serveur', 'error' => $th->getMessage()], 500);
    }
}

}
