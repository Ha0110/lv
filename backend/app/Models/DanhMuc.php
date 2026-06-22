<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    protected $table = 'danhmuc';
    protected $primaryKey = 'maDanhMuc';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'maDanhMuc',
        'tenDanhMuc',
        'moTa'
    ];

    public function sanPhams()
    {
        return $this->hasMany(
            SanPham::class,
            'maDanhMuc',
            'maDanhMuc'
        );
    }
}