<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $frontend_url = Config::get('app.frontend_url');

        $user = User::query()->find($request->route('id'));

        if(!$user || !hash_equals(sha1($user->getEmailForVerification()), $request->route('hash'))){
            $message = 'Cannot verify email';

            if($request->expectsJson()){
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            return $this->redirectFrontend($frontend_url, 'invalid', $message);
        };

        if($request->hasValidSignature()){
            $message = 'URL signature is not valid';

            if($request->expectsJson()){
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            return $this->redirectFrontend($frontend_url, 'invalid', $message);
        }

        if($user->hasVerifiedEmail()) {
            $message = 'Email already verified';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            return $this->redirectFrontend($frontend_url, 'success', $message);
        }

        if($user->markEmailAsVerified()) {
            event(new Verified($user));
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully.'
                ]);
            }
        }

        return $this->redirectFrontend($frontend_url, 'success', 'Email verified successfully.');
    }

    public function resend(Request $request) {
        $user = $request->user();

        if($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.'
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification notification sent successfully.'
        ]);
    }

    private function redirectFrontend(string $base, string $status, string $message) {
        $sep = str_contains($base, '?') ? '&' : '?';

        $url = url()->query("{$base}{$sep}status={$status}", ["message" => $message]);

        return redirect()->away($url);
    }
}
