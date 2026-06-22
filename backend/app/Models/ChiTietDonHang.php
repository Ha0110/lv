<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDonHang extends Model
{
    protected $table = 'chitietdonhang';

    protected $primaryKey = 'maCTDH';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maCTDH',
        'maDonHang',
        'maBienThe',
        'tenSanPham',
        'tenBienThe',
        'gia',
        'soLuong',
        'thanhTien'
    ];

    public function donHang()
    {
        return $this->belongsTo(
            DonHang::class,
            'maDonHang',
            'maDonHang'
        );
    }

    public function bienThe()
    {
        return $this->belongsTo(
            BienTheSanPham::class,
            'maBienThe',
            'maBienThe'
        );
    }
}