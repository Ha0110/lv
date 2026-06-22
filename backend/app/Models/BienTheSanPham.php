<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BienTheSanPham extends Model
{
    protected $table = 'bienthesanpham';
    protected $primaryKey = 'maBienThe';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maBienThe',
        'maSanPham',
        'gia',
        'soLuongTon'
    ];

    public function sanPham()
    {
        return $this->belongsTo(
            SanPham::class,
            'maSanPham',
            'maSanPham'
        );
    }

    public function anhs()
    {
        return $this->hasMany(
            Anh::class,
            'maBienThe',
            'maBienThe'
        );
    }

    public function chiTietThuocTinhs()
    {
        return $this->hasMany(
            ChiTietThuocTinh::class,
            'maBienThe',
            'maBienThe'
        );
    }

    public function khuyenMaiQuaTangs()
    {
        return $this->hasMany(
            KhuyenMai::class,
            'maBienTheQuaTang',
            'maBienThe'
        );
    }
}
