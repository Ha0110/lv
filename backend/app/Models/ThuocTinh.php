<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThuocTinh extends Model
{
    protected $table = 'thuoctinh';
    protected $primaryKey = 'maTT';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maTT',
        'maDanhMuc',
        'tenThuocTinh'
    ];

    public function chiTietThuocTinhs()
    {
        return $this->hasMany(
            ChiTietThuocTinh::class,
            'maTT',
            'maTT'
        );
    }
}