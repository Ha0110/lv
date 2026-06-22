<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HangSanXuat extends Model
{
    protected $table = 'hangsanxuat';
    protected $primaryKey = 'maHangSanXuat';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maHangSanXuat',
        'tenHang'
    ];

    public function sanPhams()
    {
        return $this->hasMany(
            SanPham::class,
            'maHangSanXuat',
            'maHangSanXuat'
        );
    }
}