<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NguoiDung extends Model
{
    protected $table = 'nguoidung';

    protected $primaryKey = 'sdt';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'sdt',
        'email',
        'matKhau',
        'hoTen',
        'maXacNhan',
        'thoiGianHetHanMaXacNhan',
        'role',
        'emailVerified',
        'diemTichLuy'
    ];
}