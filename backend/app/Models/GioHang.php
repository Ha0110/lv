<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    protected $table = 'giohang';

    protected $primaryKey = 'maGioHang';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maGioHang',
        'sdt'
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
            ChiTietGioHang::class,
            'maGioHang',
            'maGioHang'
        );
    }
}