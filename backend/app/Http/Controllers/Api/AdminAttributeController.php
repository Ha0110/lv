<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChiTietThuocTinh;
use App\Models\NguoiDung;
use App\Models\ThuocTinh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAttributeController extends Controller
{
    // Thuộc tính mô tả thông số biến thể, ví dụ socket, dung lượng, tốc độ.
    private const ADMIN_ROLES = ['admin', 'owner'];

    public function index(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return response()->json([
            'attributes' => ThuocTinh::with('danhMuc')
                ->orderBy('maDanhMuc')
                ->orderBy('tenThuocTinh')
                ->get()
                ->map(fn (ThuocTinh $attribute) => [
                    'maTT' => $attribute->maTT,
                    'maDanhMuc' => $attribute->maDanhMuc,
                    'tenDanhMuc' => $attribute->danhMuc?->tenDanhMuc,
                    'tenThuocTinh' => $attribute->tenThuocTinh,
                    'usageCount' => ChiTietThuocTinh::where('maTT', $attribute->maTT)->count(),
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
            'maDanhMuc' => ['required', Rule::exists('danhmuc', 'maDanhMuc')],
            'tenThuocTinh' => [
                'required',
                'string',
                'max:100',
                Rule::unique('thuoctinh', 'tenThuocTinh')
                    ->where(fn ($query) => $query->where('maDanhMuc', $request->maDanhMuc)),
            ],
        ]);

        $attribute = ThuocTinh::create([
            'maTT' => $this->nextCode(ThuocTinh::class, 'maTT', 'TT', 10),
            'maDanhMuc' => $validated['maDanhMuc'],
            'tenThuocTinh' => $validated['tenThuocTinh'],
        ]);

        return response()->json([
            'message' => 'Đã thêm thuộc tính',
            'attribute' => $attribute,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $attribute = ThuocTinh::findOrFail($id);
        $validated = $request->validate([
            'maDanhMuc' => ['required', Rule::exists('danhmuc', 'maDanhMuc')],
            'tenThuocTinh' => [
                'required',
                'string',
                'max:100',
                Rule::unique('thuoctinh', 'tenThuocTinh')
                    ->where(fn ($query) => $query->where('maDanhMuc', $request->maDanhMuc))
                    ->ignore($attribute->maTT, 'maTT'),
            ],
        ]);

        $usageCount = ChiTietThuocTinh::where('maTT', $attribute->maTT)->count();

        if ($usageCount > 0 && $attribute->maDanhMuc !== $validated['maDanhMuc']) {
            return response()->json([
                'message' => 'Không thể đổi danh mục của thuộc tính đang được dùng',
            ], 422);
        }

        $attribute->update([
            'maDanhMuc' => $validated['maDanhMuc'],
            'tenThuocTinh' => $validated['tenThuocTinh'],
        ]);

        return response()->json([
            'message' => 'Đã cập nhật thuộc tính',
            'attribute' => $attribute,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $admin = $this->currentAdmin($request);

        if (!$admin) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        if ($admin->role !== 'owner') {
            return response()->json(['message' => 'Chỉ owner được xóa thuộc tính'], 403);
        }

        $attribute = ThuocTinh::findOrFail($id);
        $usageCount = ChiTietThuocTinh::where('maTT', $attribute->maTT)->count();

        // Thuộc tính đã gắn vào biến thể thì không xóa để tránh mất thông số sản phẩm.
        if ($usageCount > 0) {
            return response()->json([
                'message' => 'Không thể xóa thuộc tính đang được dùng trong biến thể',
            ], 422);
        }

        $attribute->delete();

        return response()->json(['message' => 'Đã xóa thuộc tính']);
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
