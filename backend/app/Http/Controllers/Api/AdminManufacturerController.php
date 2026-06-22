<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HangSanXuat;
use App\Models\NguoiDung;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminManufacturerController extends Controller
{
    // Hãng sản xuất dùng chung quyền với catalog: admin/owner được sửa, owner được xóa.
    private const ADMIN_ROLES = ['admin', 'owner'];

    public function index(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return response()->json([
            'manufacturers' => HangSanXuat::withCount([
                'sanPhams as productCount',
            ])
                ->orderBy('tenHang')
                ->get()
                ->map(fn (HangSanXuat $manufacturer) => [
                    'maHangSanXuat' => $manufacturer->maHangSanXuat,
                    'tenHang' => $manufacturer->tenHang,
                    'productCount' => (int) $manufacturer->productCount,
                    'createdAt' => $manufacturer->createdAt,
                    'updatedAt' => $manufacturer->updatedAt,
                ])
                ->values(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $validated = $request->validate([
            'tenHang' => ['required', 'string', 'max:150', Rule::unique('hangsanxuat', 'tenHang')],
        ]);

        $manufacturer = HangSanXuat::create([
            'maHangSanXuat' => $this->nextCode(HangSanXuat::class, 'maHangSanXuat', 'HSX', 10),
            'tenHang' => $validated['tenHang'],
        ]);

        return response()->json([
            'message' => 'Đã thêm hãng sản xuất',
            'manufacturer' => $manufacturer,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $manufacturer = HangSanXuat::findOrFail($id);
        $validated = $request->validate([
            'tenHang' => [
                'required',
                'string',
                'max:150',
                Rule::unique('hangsanxuat', 'tenHang')->ignore(
                    $manufacturer->maHangSanXuat,
                    'maHangSanXuat'
                ),
            ],
        ]);

        $manufacturer->update([
            'tenHang' => $validated['tenHang'],
        ]);

        return response()->json([
            'message' => 'Đã cập nhật hãng sản xuất',
            'manufacturer' => $manufacturer,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $admin = $this->currentAdmin($request);

        if (!$admin) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        if ($admin->role !== 'owner') {
            return response()->json(['message' => 'Chỉ owner được xóa hãng sản xuất'], 403);
        }

        $manufacturer = HangSanXuat::findOrFail($id);

        // Hãng là thông tin tùy chọn, nên gỡ liên kết khỏi sản phẩm trước khi xóa hãng.
        SanPham::where('maHangSanXuat', $manufacturer->maHangSanXuat)->update([
            'maHangSanXuat' => null,
        ]);
        $manufacturer->delete();

        return response()->json(['message' => 'Đã xóa hãng sản xuất']);
    }

    private function nextCode(string $modelClass, string $column, string $prefix, int $length): string
    {
        $lastCode = $modelClass::where($column, 'like', "{$prefix}%")
            ->orderByDesc($column)
            ->value($column);

        $number = $lastCode ? ((int) substr($lastCode, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $number, $length - strlen($prefix), '0', STR_PAD_LEFT);
    }

    private function currentAdmin(Request $request): ?NguoiDung
    {
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
