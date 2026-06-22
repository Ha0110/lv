<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chitietgiohang', function (Blueprint $table) {
            $table->string('maChiTiet')->primary();
            $table->string('maGioHang');
            $table->string('maBienThe');
            $table->integer('soLuong');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('maGioHang')->references('maGioHang')->on('giohang')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('maBienThe')->references('maBienThe')->on('bienthesanpham')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietgiohang');
    }
};
