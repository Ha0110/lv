<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chitietdonhang')) {
            return;
        }

        Schema::create('chitietdonhang', function (Blueprint $table) {
            $table->char('maCTDH', 10)->primary();
            $table->char('maDonHang', 10);
            $table->char('maBienThe', 10);
            $table->string('tenSanPham');
            $table->string('tenBienThe')->nullable();
            $table->decimal('gia', 12, 2);
            $table->integer('soLuong');
            $table->decimal('thanhTien', 12, 2);
            $table->dateTime('createdAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietdonhang');
    }
};
