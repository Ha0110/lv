<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietThuocTinh extends Model
{
    protected $table = 'chitietthuoctinh';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'maBienThe',
        'maTT',
        'giaTri'
    ];

    public function bienThe()
    {
        return $this->belongsTo(
            BienTheSanPham::class,
            'maBienThe',
            'maBienThe'
        );
    }

    public function thuocTinh()
    {
        return $this->belongsTo(
            ThuocTinh::class,
            'maTT',
            'maTT'
        );
    }
}