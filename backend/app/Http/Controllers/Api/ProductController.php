<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tạo URL đầy đủ cho ảnh sản phẩm.
     */
    private function buildImageUrl($path)
    {
        if (!$path) {
            return 'https://images.pexels.com/photos/829455/pexels-photo-829455.jpeg?auto=compress&cs=tinysrgb&w=600';
        }

        // Nếu dữ liệu đã là URL đầy đủ thì giữ nguyên.
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        
        // Đường dẫn còn lại là file trong storage public của Laravel.
        return Storage::disk('public')->url($path);
    }

    /**
     * Chuyển một biến thể thành dữ liệu sản phẩm cho frontend.
     */
    private function transformBienThe($bienThe, $sanPham)
    {
        // Lấy toàn bộ ảnh của biến thể, ảnh đầu tiên là ảnh đại diện.
        $imagesPaths = $bienThe->anhs?->pluck('duongDan')->toArray() ?? [];
        $images = array_map(fn($path) => $this->buildImageUrl($path), $imagesPaths);
        $firstImage = $images[0] ?? $this->buildImageUrl(null);
        
        // Thuộc tính vừa dùng để hiển thị bảng thông số, vừa ghép vào tên đầy đủ.
        $specs = [];
        $specsStrings = [];
        if ($bienThe->chiTietThuocTinhs) {
            foreach ($bienThe->chiTietThuocTinhs as $chiTiet) {
                if ($chiTiet->thuocTinh) {
                    $specs[] = [
                        'id' => $chiTiet->thuocTinh->maTT,
                        'name' => $chiTiet->thuocTinh->tenThuocTinh,
                        'value' => $chiTiet->giaTri,
                    ];
                    // Lưu giá trị thuộc tính để ghép tên biến thể dễ phân biệt.
                    $specsStrings[] = $chiTiet->giaTri;
                }
            }
        }

        // Tên hiển thị gồm hãng + tên sản phẩm + thông số của biến thể.
        $manufacturer = $sanPham->hangSanXuat?->tenHang ?? '';
        $fullName = $sanPham->tenSanPham;
        if ($manufacturer) {
            $fullName = $manufacturer . ' ' . $fullName;
        }
        if (!empty($specsStrings)) {
            $fullName .= ' ' . implode(' ', $specsStrings);
        }

        return [
            'id' => $bienThe->maBienThe,
            'name' => $fullName,
            'category' => $sanPham->danhMuc?->tenDanhMuc ?? 'Unknown',
            'manufacturer' => $manufacturer ?: 'Unknown',
            'price' => (float) $bienThe->gia,
            'description' => $sanPham->moTa,
            'stock' => (int) $bienThe->soLuongTon,
            'image' => $firstImage,
            'images' => $images,
            'specs' => $specs,
            'featured' => false,
            'createdAt' => $bienThe->createdAt,
            'updatedAt' => $bienThe->updatedAt,
        ];
    }

    /**
     * Lấy tất cả sản phẩm, mỗi biến thể được trả như một sản phẩm riêng.
     */
    public function index()
    {
        $sanPhams = SanPham::with([
            'danhMuc', 
            'hangSanXuat', 
            'bienThes.anhs',
            'bienThes.chiTietThuocTinhs.thuocTinh'
        ])->get();

        $products = [];
        foreach ($sanPhams as $sanPham) {
            $bienThes = $sanPham->bienThes ?? [];
            foreach ($bienThes as $bienThe) {
                $products[] = $this->transformBienThe($bienThe, $sanPham);
            }
        }

        return response()->json($products);
    }

    /**
     * Lấy chi tiết sản phẩm theo mã biến thể.
     */
    public function show($id)
    {
        $bienThe = \App\Models\BienTheSanPham::with([
            'sanPham.danhMuc',
            'sanPham.hangSanXuat',
            'anhs',
            'chiTietThuocTinhs.thuocTinh'
        ])->find($id);

        if (!$bienThe) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($this->transformBienThe($bienThe, $bienThe->sanPham));
    }

    /**
     * Lấy sản phẩm theo danh mục, vẫn tách từng biến thể thành item riêng.
     */
    public function byCategory($categoryId)
    {
        $sanPhams = SanPham::with([
            'danhMuc', 
            'hangSanXuat', 
            'bienThes.anhs',
            'bienThes.chiTietThuocTinhs.thuocTinh'
        ])
            ->where('maDanhMuc', $categoryId)
            ->get();

        $products = [];
        foreach ($sanPhams as $sanPham) {
            $bienThes = $sanPham->bienThes ?? [];
            foreach ($bienThes as $bienThe) {
                $products[] = $this->transformBienThe($bienThe, $sanPham);
            }
        }

        return response()->json($products);
    }

    /**
     * Lấy danh mục cho bộ lọc ngoài trang cửa hàng.
     */
    public function categories()
    {
        $categories = \App\Models\DanhMuc::all()->map(function($cat) {
            return [
                'id' => $cat->maDanhMuc,
                'name' => $cat->tenDanhMuc,
                'description' => $cat->moTa,
            ];
        });

        return response()->json($categories);
    }

    /**
     * Tìm sản phẩm theo tên hoặc mô tả.
     */
    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json(['message' => 'Search query is required'], 400);
        }

        $sanPhams = SanPham::with([
            'danhMuc', 
            'hangSanXuat', 
            'bienThes.anhs',
            'bienThes.chiTietThuocTinhs.thuocTinh'
        ])
            ->where('tenSanPham', 'like', "%{$query}%")
            ->orWhere('moTa', 'like', "%{$query}%")
            ->get();

        $products = [];
        foreach ($sanPhams as $sanPham) {
            $bienThes = $sanPham->bienThes ?? [];
            foreach ($bienThes as $bienThe) {
                $products[] = $this->transformBienThe($bienThe, $sanPham);
            }
        }

        return response()->json($products);
    }
}
