<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\ThuocTinh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    // Danh mục cho phép admin/owner xem, thêm, sửa; xóa chỉ dành cho owner.
    private const ADMIN_ROLES = ['admin', 'owner'];

    public function index(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return response()->json([
            'categories' => DanhMuc::withCount([
                'sanPhams as productCount',
            ])
                ->orderBy('tenDanhMuc')
                ->get()
                ->map(fn (DanhMuc $category) => [
                    'maDanhMuc' => $category->maDanhMuc,
                    'tenDanhMuc' => $category->tenDanhMuc,
                    'moTa' => $category->moTa,
                    'productCount' => (int) $category->productCount,
                    'attributeCount' => ThuocTinh::where('maDanhMuc', $category->maDanhMuc)->count(),
                    'createdAt' => $category->createdAt,
                    'updatedAt' => $category->updatedAt,
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
            'tenDanhMuc' => ['required', 'string', 'max:100', Rule::unique('danhmuc', 'tenDanhMuc')],
            'moTa' => ['nullable', 'string'],
        ]);

        $category = DanhMuc::create([
            'maDanhMuc' => $this->nextCode(DanhMuc::class, 'maDanhMuc', 'DM', 10),
            'tenDanhMuc' => $validated['tenDanhMuc'],
            'moTa' => $validated['moTa'] ?? null,
        ]);

        return response()->json([
            'message' => 'Đã thêm danh mục',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $category = DanhMuc::findOrFail($id);
        $validated = $request->validate([
            'tenDanhMuc' => [
                'required',
                'string',
                'max:100',
                Rule::unique('danhmuc', 'tenDanhMuc')->ignore($category->maDanhMuc, 'maDanhMuc'),
            ],
            'moTa' => ['nullable', 'string'],
        ]);

        $category->update([
            'tenDanhMuc' => $validated['tenDanhMuc'],
            'moTa' => $validated['moTa'] ?? null,
        ]);

        return response()->json([
            'message' => 'Đã cập nhật danh mục',
            'category' => $category,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $admin = $this->currentAdmin($request);

        if (!$admin) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        if ($admin->role !== 'owner') {
            return response()->json(['message' => 'Chỉ owner được xóa danh mục'], 403);
        }

        $category = DanhMuc::findOrFail($id);
        $productCount = SanPham::where('maDanhMuc', $category->maDanhMuc)->count();
        $attributeCount = ThuocTinh::where('maDanhMuc', $category->maDanhMuc)->count();

        // Danh mục còn sản phẩm hoặc thuộc tính thì không xóa để tránh lỗi khóa ngoại.
        if ($productCount > 0 || $attributeCount > 0) {
            return response()->json([
                'message' => 'Không thể xóa danh mục đang có sản phẩm hoặc thuộc tính',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Đã xóa danh mục']);
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
