<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietGioHang extends Model
{
    protected $table = 'chitietgiohang';

    protected $primaryKey = 'maChiTiet';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maChiTiet',
        'maGioHang',
        'maBienThe',
        'soLuong'
    ];

    public function gioHang()
    {
        return $this->belongsTo(
            GioHang::class,
            'maGioHang',
            'maGioHang'
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