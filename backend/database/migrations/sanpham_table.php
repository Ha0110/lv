<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sanpham')) {
            return;
        }

        Schema::create('sanpham', function (Blueprint $table) {
            $table->string('maSanPham')->primary();
            $table->string('tenSanPham');
            $table->string('maDanhMuc');
            $table->string('maHangSanXuat')->nullable();
            $table->text('moTa')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('maDanhMuc')->references('maDanhMuc')->on('danhmuc')->onUpdate('cascade');
            $table->foreign('maHangSanXuat')->references('maHangSanXuat')->on('hangsanxuat')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanpham');
    }
};
