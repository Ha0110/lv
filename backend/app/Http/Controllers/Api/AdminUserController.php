<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    // Trang admin chỉ dành cho hai vai trò nội bộ này.
    private const ADMIN_ROLES = ['admin', 'owner'];

    public function index(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        // Chỉ trả các trường cần cho bảng quản trị, không trả mật khẩu hay OTP.
        $query = NguoiDung::query()
            ->select([
                'sdt',
                'email',
                'hoTen',
                'role',
                'emailVerified',
                'diemTichLuy',
                'createdAt',
                'updatedAt',
            ])
            ->orderByDesc('createdAt');

        if ($request->filled('role') && in_array($request->role, ['customer', 'admin', 'owner'], true)) {
            $query->where('role', $request->role);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($query) use ($keyword) {
                $query->where('hoTen', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('sdt', 'like', "%{$keyword}%");
            });
        }

        return response()->json([
            'users' => $query->get(),
            'summary' => [
                'total' => NguoiDung::count(),
                'customer' => NguoiDung::where('role', 'customer')->count(),
                'admin' => NguoiDung::where('role', 'admin')->count(),
                'owner' => NguoiDung::where('role', 'owner')->count(),
            ],
        ]);
    }

    public function updateRole(Request $request, string $sdt)
    {
        $admin = $this->currentAdmin($request);

        if (!$admin) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        // Chỉ owner được phân quyền để tránh admin tự nâng quyền lẫn nhau.
        if ($admin->role !== 'owner') {
            return response()->json(['message' => 'Chỉ owner được đổi vai trò'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['customer', 'admin', 'owner'])],
        ]);

        $user = NguoiDung::findOrFail($sdt);

        // Owner đang đăng nhập không được tự hạ quyền của chính mình.
        if ($admin->sdt === $user->sdt && $validated['role'] !== 'owner') {
            return response()->json(['message' => 'Owner không thể tự hạ quyền của chính mình'], 422);
        }

        // Hệ thống luôn phải còn ít nhất một owner để quản lý quyền.
        if ($user->role === 'owner' && $validated['role'] !== 'owner' && NguoiDung::where('role', 'owner')->count() <= 1) {
            return response()->json(['message' => 'Không thể hạ quyền owner cuối cùng'], 422);
        }

        $user->update([
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Đã cập nhật vai trò',
            'user' => $user->only([
                'sdt',
                'email',
                'hoTen',
                'role',
                'emailVerified',
                'diemTichLuy',
                'createdAt',
                'updatedAt',
            ]),
        ]);
    }

    private function currentAdmin(Request $request): ?NguoiDung
    {
        // Header từ frontend phải khớp người dùng thật trong CSDL.
        $email = $request->header('X-Admin-Email');
        $role = $request->header('X-Admin-Role');

        if (!$email || !in_array($role, self::ADMIN_ROLES, true)) {
            return null;
        }

        $user = NguoiDung::where('email', $email)->first();

        if (!$user || $user->role !== $role || !in_array($user->role, self::ADMIN_ROLES, true)) {
            return null;
        }

        return $user;
    }
}
