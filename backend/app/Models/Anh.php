<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anh extends Model
{
    protected $table = 'anh';
    protected $primaryKey = 'maAnh';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'maAnh',
        'maBienThe',
        'duongDan'
    ];

    public function bienThe()
    {
        return $this->belongsTo(
            BienTheSanPham::class,
            'maBienThe',
            'maBienThe'
        );
    }
}