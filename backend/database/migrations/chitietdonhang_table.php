<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chitietdonhang', function (Blueprint $table) {
            $table->string('maCTDH')->primary();
            $table->string('maDonHang');
            $table->string('maBienThe');
            $table->string('tenSanPham');
            $table->string('tenBienThe')->nullable();
            $table->decimal('gia', 12, 2);
            $table->integer('soLuong');
            $table->decimal('thanhTien', 12, 2);
            $table->timestamp('createdAt')->useCurrent();

            $table->foreign('maDonHang')->references('maDonHang')->on('donhang')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('maBienThe')->references('maBienThe')->on('bienthesanpham')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietdonhang');
    }
};
