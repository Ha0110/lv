<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'diachi';

    protected $primaryKey = 'maDiaChi';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maDiaChi',
        'sdt',
        'tenNguoiNhan',
        'sdtNguoiNhan',
        'diaChi',
        'thanhPho',
        'isDefault'
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(
            NguoiDung::class,
            'sdt',
            'sdt'
        );
    }
}