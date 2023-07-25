<?php

namespace App\Http\Controllers;
use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Mail;

class UserController extends Controller {
    //
    public function UserRegistration(Request $request) {
        try {
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName'  => $request->input('lastName'),
                'email'     => $request->input('email'),
                'mobile'    => $request->input('mobile'),
                'password'  => $request->input('password'),
            ]);
            return response()->json([
                'status'  => 'success',
                'message' => 'User Registation Successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'User Registation Failed',
            ], 401);
        }

    }
    public function UserLogin(Request $request) {
        $count = User::where('email', '=', $request->input('email'))->where('password', '=', $request->input('password'))->count();
        if ($count == 1) {
            // $token = JWTToken::CreateToken($request->input('email'));
            $token = JWTToken::CreateToken($request->input('email'));
            return response()->json([
                'status'  => 'Success',
                'message' => 'User Login Successful',
                'token'   => $token,

            ], 200);

        } else {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Unauthorized',
            ], 401);
        }

    }

    public function SendOTPCode(Request $request) {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $count = User::where('email', '=', $email)->count();
        if ($count == 1) {
            Mail::to($email)->send(new OTPMail($otp));
            User::where('email', '=', $email)->update(['otp' => $otp]);
            return response()->json([
                'status'  => 'success',
                'message' => '4 Digit OTP Code send your email please check your email',
            ]);

        } else {
            return response()->json([
                'status'  => 'failed',
                'message' => 'unauthorized',
            ], 401);
        }

    }

    public function VerifyOTP(Request $request) {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)->where('otp', '=', $otp)->count();
        if ($count == 1) {
            // Database OTP Update
            User::where('email', '=', $email)->update(['otp' => '0']);
            // Password Reset Token
            $token = JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
                'status'  => 'success',
                'message' => 'OTP Verificatio Successfully',
                'token'   => $token,
            ]);
        } else {
            return response()->json([
                'status'  => 'failed',
                'message' => 'unauthorized',
            ], 401);
        }
    }

    public function PasswordReet(Request $request) {
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', '=', $email)->update(['password' => $password]);
            return response()->json([
                'status'  => 'success',
                'message' => 'Request Successfull',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Something Wrong',
            ]);
        }
    }

}
