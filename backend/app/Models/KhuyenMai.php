<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhuyenMai extends Model
{
    protected $table = 'khuyenmai';
    protected $primaryKey = 'maKhuyenMai';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maKhuyenMai',
        'tenKhuyenMai',
        'giaTriToiThieu',
        'maBienTheQuaTang',
        'ngayBatDau',
        'ngayKetThuc'
    ];

    public function bienTheQuaTang()
    {
        return $this->belongsTo(
            BienTheSanPham::class,
            'maBienTheQuaTang',
            'maBienThe'
        );
    }
}
