<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Build full URL for image
     */
    private function buildImageUrl($path)
    {
        if (!$path) {
            return 'https://images.pexels.com/photos/829455/pexels-photo-829455.jpeg?auto=compress&cs=tinysrgb&w=600';
        }
        
        // If already a full URL, return as-is
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        
        // Build full URL from relative path
        $appUrl = rtrim(config('app.url'), '/');
        return $appUrl . '/storage/' . $path;
    }

    /**
     * Transform variant (biến thế) to product format
     */
    private function transformBienThe($bienThe, $sanPham)
    {
        // Get images from variant
        $imagesPaths = $bienThe->anhs?->pluck('duongDan')->toArray() ?? [];
        $images = array_map(fn($path) => $this->buildImageUrl($path), $imagesPaths);
        $firstImage = $images[0] ?? $this->buildImageUrl(null);
        
        // Get specifications from chi tiết thuộc tính
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
                    // Collect spec values for full product name
                    $specsStrings[] = $chiTiet->giaTri;
                }
            }
        }

        // Build full product name: Manufacturer + Product + Specs
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
     * Get all products (flatten all variants as separate items)
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
     * Get single product by variant ID
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
     * Get products by category (flatten all variants)
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
     * Get all categories
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
     * Search products (flatten all variants)
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
