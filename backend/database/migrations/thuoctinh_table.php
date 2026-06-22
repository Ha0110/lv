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
            $table->char('maTT', 10)->primary();
            $table->char('maDanhMuc', 10)->nullable();
            $table->string('tenThuocTinh', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thuoctinh');
    }
};
