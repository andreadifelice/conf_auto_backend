<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request){
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if(!$user){
            return response()->json([
                'success' => true,
                'message' => 'If the provided email address exists you\'ll receive a password reset link',
            ]);
        }

        Password::sendResetLink([
            'email' => $data['email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'If the provided email address exists you\'ll receive a password reset link'
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request){
        $data = $request->validated();
        $user = User::whereEmail($data['email'])->first();

        if(!$user){
            return response()->json([
                'success' => true,
                'message' => 'Invalid token or unhauthorized request'
            ], 400);
        }

        $status = Password::reset(
            $data,
            function (User $user, string $password){
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return match($status) {
            Password::PASSWORD_RESET => response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]),
            default => response()->json([
                'success' => false,
                'message' => 'Invalid token or unhautorized request'
            ])
        };
    }
}
