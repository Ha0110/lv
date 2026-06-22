<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    protected $table = 'donhang';

    protected $primaryKey = 'maDonHang';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maDonHang',
        'sdt',
        'tenNguoiNhan',
        'sdtNguoiNhan',
        'email',
        'diaChiGiaoHang',
        'tongTien',
        'diemDaSuDung',
        'diemDuocTich',
        'trangThai'
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(
            NguoiDung::class,
            'sdt',
            'sdt'
        );
    }

    public function chiTiets()
    {
        return $this->hasMany(
            ChiTietDonHang::class,
            'maDonHang',
            'maDonHang'
        );
    }

    public function thanhToans()
    {
        return $this->hasMany(
            ThanhToan::class,
            'maDonHang',
            'maDonHang'
        );
    }
}