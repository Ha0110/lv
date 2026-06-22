<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    protected $table = 'thanhtoan';

    protected $primaryKey = 'maThanhToan';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maThanhToan',
        'maDonHang',
        'phuongThuc',
        'soTien',
        'maGiaoDich',
        'trangThai',
        'thoiGianThanhToan'
    ];

    public function donHang()
    {
        return $this->belongsTo(
            DonHang::class,
            'maDonHang',
            'maDonHang'
        );
    }
}