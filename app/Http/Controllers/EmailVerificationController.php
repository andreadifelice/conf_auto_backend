<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $frontend_url = Config::get('app.frontend_url');

        $request->validate([
            'otp' => 'required|numeric|digits:6'
        ]);

        $user = $request->user();

        if(!$user){
            $message = 'Utente non autenticato';
            if($request->expectsJson()){
                return response()->json(['success' => false, 'message' => $message], 401);
            }
            return $this->redirectFrontend($frontend_url, 'invalid', $message);
        }

        if($user->otp_code !== $request->otp || now()->greaterThan($user->otp_expires_at)){
            $message = 'Il codice OTP non è valido o è scaduto';

            if($request->expectsJson()){
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }
            return $this->redirectFrontend($frontend_url, 'invalid', $message);
        }

        if($user->hasVerifiedEmail()){
            $message = 'Email già verificata';

            if($request->expectsJson()){
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return $this->redirectFrontend($frontend_url, 'success', $message);
        }

        $user->email_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;

        if($user->save()){
            event(new Verified($user));

            if($request->expectsJson()){
                return response()->json([
                    'success' => true,
                    'message' => 'Email verificata con successo'
                ]);
            }
        }

        return $this->redirectFrontend($frontend_url, 'success', 'Email verificata con successo');
    }

    public function resend(Request $request) 
    {
        $user = $request->user();

        if($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email già verificata'
            ], 422);
        }

        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->send(new SendOtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'Codice OTP inviato con successo'
        ]);
    }

    private function redirectFrontend(string $base, string $status, string $message) 
    {
        $sep = str_contains($base, '?') ? '&' : '?';

        $queryString = http_build_query([
            'status' => $status,
            'message' => $message
        ]);

        return redirect()->away("{$base}{$sep}{$queryString}");
    }
}
