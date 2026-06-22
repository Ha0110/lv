<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    protected $table = 'sanpham';
    protected $primaryKey = 'maSanPham';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maSanPham',
        'tenSanPham',
        'maDanhMuc',
        'maHangSanXuat',
        'moTa'
    ];

    public function danhMuc()
    {
        return $this->belongsTo(
            DanhMuc::class,
            'maDanhMuc',
            'maDanhMuc'
        );
    }

    public function hangSanXuat()
    {
        return $this->belongsTo(
            HangSanXuat::class,
            'maHangSanXuat',
            'maHangSanXuat'
        );
    }

    public function bienThes()
    {
        return $this->hasMany(
            BienTheSanPham::class,
            'maSanPham',
            'maSanPham'
        );
    }
}