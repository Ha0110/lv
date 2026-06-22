<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Load data
$sanPhams = \App\Models\SanPham::with([
    'danhMuc', 
    'hangSanXuat', 
    'bienThes.anhs',
    'bienThes.chiTietThuocTinhs.thuocTinh'
])->get();

$products = [];
foreach ($sanPhams as $sanPham) {
    foreach ($sanPham->bien_thes as $bienThe) {
        $products[] = [
            'id' => $bienThe->maBienThe,
            'name' => $sanPham->tenSanPham,
            'price' => (float) $bienThe->gia,
            'stock' => (int) $bienThe->soLuongTon,
            'specs' => collect($bienThe->chi_tiet_thuoc_tinhs ?? [])->map(function($ct) {
                return [
                    'name' => $ct->thuoc_tinh?->tenThuocTinh,
                    'value' => $ct->giaTri,
                ];
            })->toArray(),
        ];
    }
}

echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
