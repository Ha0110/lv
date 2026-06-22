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
            $table->char('maSanPham', 10)->primary();
            $table->string('tenSanPham');
            $table->char('maDanhMuc', 10);
            $table->char('maHangSanXuat', 10)->nullable();
            $table->text('moTa')->nullable();
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanpham');
    }
};
