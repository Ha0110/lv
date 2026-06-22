<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('thuoctinh')) {
            return;
        }

        Schema::create('thuoctinh', function (Blueprint $table) {
            $table->string('maTT')->primary();
            $table->string('maDanhMuc')->nullable();
            $table->string('tenThuocTinh');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thuoctinh');
    }
};
