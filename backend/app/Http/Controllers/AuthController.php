<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'hoTen' => 'required',
            'email' => 'required|email|unique:nguoidung,email',
            'sdt' => 'required|unique:nguoidung,sdt',
            'matKhau' => 'required|min:6'
        ]);

        $otp = rand(100000, 999999);

        NguoiDung::create([
            'hoTen' => $request->hoTen,
            'email' => $request->email,
            'sdt' => $request->sdt,
            'matKhau' => Hash::make($request->matKhau),

            'maXacNhan' => $otp,

            'thoiGianHetHanMaXacNhan'
                => now()->addMinutes(5),

            'emailVerified' => false
        ]);

        Mail::to($request->email)
            ->send(new OtpMail($otp));

        return response()->json([
            'message' => 'Đã gửi OTP'
        ]);
    }
    
    public function verifyOtp(Request $request)
    {
        $user = NguoiDung::where(
            'email',
            $request->email
        )->first();

        if (!$user) {
            return response()->json([
                'message' => 'Không tìm thấy tài khoản'
            ], 404);
        }

        if ($user->maXacNhan != $request->otp) {
            return response()->json([
                'message' => 'OTP không đúng'
            ], 400);
        }

        if (
            now()->gt(
                $user->thoiGianHetHanMaXacNhan
            )
        ) {
            return response()->json([
                'message' => 'OTP hết hạn'
            ], 400);
        }

        $user->update([
            'emailVerified' => true,
            'maXacNhan' => null,
            'thoiGianHetHanMaXacNhan' => null
        ]);

        return response()->json([
            'message' => 'Xác thực thành công'
        ]);
    }

    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'matKhau' => 'required'
    ]);

    $user = NguoiDung::where(
        'email',
        $request->email
    )->first();

    if (!$user) {
        return response()->json([
            'message' => 'Email không tồn tại'
        ], 404);
    }

    if (!$user->emailVerified) {
        return response()->json([
            'message' => 'Email chưa được xác thực'
        ], 400);
    }

    if (!Hash::check(
        $request->matKhau,
        $user->matKhau
    )) {
        return response()->json([
            'message' => 'Sai mật khẩu'
        ], 400);
    }

    return response()->json([
        'message' => 'Đăng nhập thành công',
        'user' => [
            'email' => $user->email,
            'hoTen' => $user->hoTen,
            'role' => $user->role
        ]
    ]);
}
}
