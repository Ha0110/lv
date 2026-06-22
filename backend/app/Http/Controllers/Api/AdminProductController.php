<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anh;
use App\Models\BienTheSanPham;
use App\Models\ChiTietThuocTinh;
use App\Models\DanhMuc;
use App\Models\HangSanXuat;
use App\Models\KhuyenMai;
use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Models\ThuocTinh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminProductController extends Controller
{
    // Admin được quản lý sản phẩm, còn một số thao tác nguy hiểm chỉ owner được làm.
    private const ADMIN_ROLES = ['admin', 'owner'];

    public function index(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        // Nạp đủ quan hệ để bảng admin hiển thị sản phẩm, biến thể, ảnh và thuộc tính.
        $query = SanPham::with([
            'danhMuc',
            'hangSanXuat',
            'bienThes.anhs',
            'bienThes.chiTietThuocTinhs.thuocTinh',
        ])->orderByDesc('createdAt');

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($query) use ($keyword) {
                $query->where('tenSanPham', 'like', "%{$keyword}%")
                    ->orWhere('moTa', 'like', "%{$keyword}%")
                    ->orWhere('maSanPham', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('maDanhMuc')) {
            $query->where('maDanhMuc', $request->maDanhMuc);
        }

        return response()->json([
            'products' => $query->get()->map(fn (SanPham $product) => $this->transformProduct($product))->values(),
            'summary' => [
                'total' => SanPham::count(),
                'variants' => BienTheSanPham::count(),
                'stock' => BienTheSanPham::sum('soLuongTon'),
                'outOfStock' => BienTheSanPham::where('soLuongTon', '<=', 0)->count(),
            ],
        ]);
    }

    public function meta(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        return response()->json([
            'categories' => DanhMuc::orderBy('tenDanhMuc')->get(['maDanhMuc', 'tenDanhMuc']),
            'manufacturers' => HangSanXuat::orderBy('tenHang')->get(['maHangSanXuat', 'tenHang']),
            'attributes' => ThuocTinh::orderBy('tenThuocTinh')->get(['maTT', 'maDanhMuc', 'tenThuocTinh']),
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $validated = $this->validateProduct($request);

        // Tạo sản phẩm và toàn bộ biến thể trong một transaction để tránh lưu dở dữ liệu.
        $product = DB::transaction(function () use ($validated) {
            $product = SanPham::create([
                'maSanPham' => $this->nextCode(SanPham::class, 'maSanPham', 'SP', 10),
                'tenSanPham' => $validated['tenSanPham'],
                'maDanhMuc' => $validated['maDanhMuc'],
                'maHangSanXuat' => $validated['maHangSanXuat'] ?? null,
                'moTa' => $validated['moTa'] ?? null,
            ]);

            $this->syncVariants($product, $validated['bienThes']);

            return $product->load([
                'danhMuc',
                'hangSanXuat',
                'bienThes.anhs',
                'bienThes.chiTietThuocTinhs.thuocTinh',
            ]);
        });

        return response()->json([
            'message' => 'Đã thêm sản phẩm',
            'product' => $this->transformProduct($product),
        ], 201);
    }

    public function uploadImage(Request $request)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $path = $this->storeResizedImage($validated['image']);

        return response()->json([
            'path' => $path,
            'url' => $this->imageUrl($path),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        if (!$this->currentAdmin($request)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        $product = SanPham::findOrFail($id);
        $validated = $this->validateProduct($request);

        // Cập nhật sản phẩm và đồng bộ lại danh sách biến thể cùng lúc.
        $product = DB::transaction(function () use ($product, $validated) {
            $product->update([
                'tenSanPham' => $validated['tenSanPham'],
                'maDanhMuc' => $validated['maDanhMuc'],
                'maHangSanXuat' => $validated['maHangSanXuat'] ?? null,
                'moTa' => $validated['moTa'] ?? null,
            ]);

            $this->syncVariants($product, $validated['bienThes']);

            return $product->fresh([
                'danhMuc',
                'hangSanXuat',
                'bienThes.anhs',
                'bienThes.chiTietThuocTinhs.thuocTinh',
            ]);
        });

        return response()->json([
            'message' => 'Đã cập nhật sản phẩm',
            'product' => $this->transformProduct($product),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $admin = $this->currentAdmin($request);

        if (!$admin) {
            return response()->json(['message' => 'Bạn không có quyền truy cập'], 403);
        }

        if ($admin->role !== 'owner') {
            return response()->json(['message' => 'Chỉ owner được xóa sản phẩm'], 403);
        }

        $product = SanPham::with('bienThes')->findOrFail($id);

        // Xóa thủ công dữ liệu con để chủ động xử lý thuộc tính, ảnh và quà tặng khuyến mãi.
        DB::transaction(function () use ($product) {
            foreach ($product->bienThes as $variant) {
                ChiTietThuocTinh::where('maBienThe', $variant->maBienThe)->delete();
                Anh::where('maBienThe', $variant->maBienThe)->delete();
                KhuyenMai::where('maBienTheQuaTang', $variant->maBienThe)->update([
                    'maBienTheQuaTang' => null,
                ]);
                $variant->delete();
            }

            $product->delete();
        });

        return response()->json(['message' => 'Đã xóa sản phẩm']);
    }

    private function validateProduct(Request $request): array
    {
        // Payload sản phẩm luôn phải có ít nhất một biến thể để có giá và tồn kho.
        return $request->validate([
            'tenSanPham' => ['required', 'string', 'max:255'],
            'maDanhMuc' => ['required', Rule::exists('danhmuc', 'maDanhMuc')],
            'maHangSanXuat' => ['nullable', Rule::exists('hangsanxuat', 'maHangSanXuat')],
            'moTa' => ['nullable', 'string'],
            'bienThes' => ['required', 'array', 'min:1'],
            'bienThes.*.maBienThe' => ['nullable', 'string'],
            'bienThes.*.gia' => ['required', 'numeric', 'min:0'],
            'bienThes.*.soLuongTon' => ['required', 'integer', 'min:0'],
            'bienThes.*.duongDanAnh' => ['nullable', 'string', 'max:255'],
            'bienThes.*.thongSo' => ['nullable', 'array'],
            'bienThes.*.thongSo.*.maTT' => ['required_with:bienThes.*.thongSo', Rule::exists('thuoctinh', 'maTT')],
            'bienThes.*.thongSo.*.giaTri' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function transformProduct(SanPham $product): array
    {
        // Gom dữ liệu từ các biến thể để frontend hiển thị khoảng giá và tổng tồn kho.
        $variants = $product->bienThes ?? collect();
        $firstVariant = $variants->sortBy('maBienThe')->first();
        $prices = $variants->pluck('gia')->map(fn ($price) => (float) $price)->values();
        $image = $firstVariant?->anhs?->first()?->duongDan;

        return [
            'maSanPham' => $product->maSanPham,
            'tenSanPham' => $product->tenSanPham,
            'maDanhMuc' => $product->maDanhMuc,
            'tenDanhMuc' => $product->danhMuc?->tenDanhMuc,
            'maHangSanXuat' => $product->maHangSanXuat,
            'tenHang' => $product->hangSanXuat?->tenHang,
            'moTa' => $product->moTa,
            'gia' => $firstVariant ? (float) $firstVariant->gia : 0,
            'giaThapNhat' => $prices->min() ?? 0,
            'giaCaoNhat' => $prices->max() ?? 0,
            'soLuongTon' => $firstVariant ? (int) $firstVariant->soLuongTon : 0,
            'tongTonKho' => (int) $variants->sum('soLuongTon'),
            'soBienThe' => $variants->count(),
            'duongDanAnh' => $image,
            'duongDanAnhUrl' => $this->imageUrl($image),
            'thongSo' => $firstVariant?->chiTietThuocTinhs?->map(fn ($detail) => [
                'maTT' => $detail->maTT,
                'tenThuocTinh' => $detail->thuocTinh?->tenThuocTinh,
                'giaTri' => $detail->giaTri,
            ])->values() ?? [],
            'bienThes' => $variants->sortBy('maBienThe')->map(fn ($variant) => [
                'maBienThe' => $variant->maBienThe,
                'gia' => (float) $variant->gia,
                'soLuongTon' => (int) $variant->soLuongTon,
                'duongDanAnh' => $variant->anhs?->first()?->duongDan,
                'duongDanAnhUrl' => $this->imageUrl($variant->anhs?->first()?->duongDan),
                'hinhAnh' => $variant->anhs?->pluck('duongDan')->values() ?? [],
                'thongSo' => $variant->chiTietThuocTinhs?->map(fn ($detail) => [
                    'maTT' => $detail->maTT,
                    'tenThuocTinh' => $detail->thuocTinh?->tenThuocTinh,
                    'giaTri' => $detail->giaTri,
                ])->values() ?? [],
            ])->values(),
            'createdAt' => $product->createdAt,
            'updatedAt' => $product->updatedAt,
        ];
    }

    private function syncVariants(SanPham $product, array $variants): void
    {
        // Cập nhật biến thể cũ, tạo biến thể mới và xóa biến thể không còn trong form.
        $keptVariantIds = [];

        foreach ($variants as $variantData) {
            $variant = null;

            if (!empty($variantData['maBienThe'])) {
                $variant = $product->bienThes()
                    ->where('maBienThe', $variantData['maBienThe'])
                    ->first();
            }

            if (!$variant) {
                $variant = BienTheSanPham::create([
                    'maBienThe' => $this->nextCode(BienTheSanPham::class, 'maBienThe', 'BT', 10),
                    'maSanPham' => $product->maSanPham,
                    'gia' => $variantData['gia'],
                    'soLuongTon' => $variantData['soLuongTon'],
                ]);
            } else {
                $variant->update([
                    'gia' => $variantData['gia'],
                    'soLuongTon' => $variantData['soLuongTon'],
                ]);
            }

            $keptVariantIds[] = $variant->maBienThe;
            // Mỗi biến thể đang dùng một ảnh chính và nhiều dòng thuộc tính chi tiết.
            $this->syncImages($variant, $variantData['duongDanAnh'] ?? null);
            $this->syncSpecs($variant, $variantData['thongSo'] ?? []);
        }

        $product->bienThes()
            ->whereNotIn('maBienThe', $keptVariantIds)
            ->get()
            ->each(function (BienTheSanPham $variant): void {
                ChiTietThuocTinh::where('maBienThe', $variant->maBienThe)->delete();
                Anh::where('maBienThe', $variant->maBienThe)->delete();
                KhuyenMai::where('maBienTheQuaTang', $variant->maBienThe)->update([
                    'maBienTheQuaTang' => null,
                ]);
                $variant->delete();
            });
    }

    private function syncImages(BienTheSanPham $variant, ?string $imagePath): void
    {
        // Form admin chỉ lưu một ảnh đại diện cho mỗi biến thể, nên xóa ảnh cũ trước.
        Anh::where('maBienThe', $variant->maBienThe)->delete();

        if (!$imagePath) {
            return;
        }

        Anh::create([
            'maAnh' => $this->nextCode(Anh::class, 'maAnh', 'A', 10),
            'maBienThe' => $variant->maBienThe,
            'duongDan' => $imagePath,
        ]);
    }

    private function imageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function storeResizedImage($file): string
    {
        // Ảnh upload được crop giữa và resize về 400x400 để thumbnail luôn đồng nhất.
        $source = $this->createImageResource($file->getRealPath(), $file->getMimeType());

        if (!$source) {
            throw ValidationException::withMessages([
                'image' => 'Không thể xử lý ảnh đã tải lên',
            ]);
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropSize = min($sourceWidth, $sourceHeight);
        $sourceX = (int) (($sourceWidth - $cropSize) / 2);
        $sourceY = (int) (($sourceHeight - $cropSize) / 2);
        $targetSize = 400;
        $target = imagecreatetruecolor($targetSize, $targetSize);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefilledrectangle(
            $target,
            0,
            0,
            $targetSize,
            $targetSize,
            imagecolorallocatealpha($target, 0, 0, 0, 127)
        );

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetSize,
            $targetSize,
            $cropSize,
            $cropSize
        );

        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };
        $path = 'products/' . uniqid('product_', true) . '.' . $extension;

        Storage::disk('public')->makeDirectory('products');

        $fullPath = Storage::disk('public')->path($path);

        match ($extension) {
            'png' => imagepng($target, $fullPath, 8),
            'webp' => imagewebp($target, $fullPath, 85),
            'gif' => imagegif($target, $fullPath),
            default => imagejpeg($target, $fullPath, 85),
        };

        imagedestroy($source);
        imagedestroy($target);

        return $path;
    }

    private function createImageResource(string $path, ?string $mimeType)
    {
        // Chọn hàm đọc ảnh tương ứng với định dạng mà người quản trị upload.
        return match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif' => imagecreatefromgif($path),
            default => null,
        };
    }

    private function syncSpecs(BienTheSanPham $variant, array $specs): void
    {
        // Thuộc tính biến thể được thay thế theo form hiện tại để tránh dữ liệu cũ bị sót.
        ChiTietThuocTinh::where('maBienThe', $variant->maBienThe)->delete();

        foreach ($specs as $spec) {
            if (empty($spec['maTT']) || !array_key_exists('giaTri', $spec)) {
                continue;
            }

            ChiTietThuocTinh::create([
                'maBienThe' => $variant->maBienThe,
                'maTT' => $spec['maTT'],
                'giaTri' => $spec['giaTri'],
            ]);
        }
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
        // Kiểm tra role trong header phải khớp với role thật trong CSDL.
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
