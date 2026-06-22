<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bienthesanpham')) {
            return;
        }

        Schema::create('bienthesanpham', function (Blueprint $table) {
            $table->string('maBienThe')->primary();
            $table->string('maSanPham');
            $table->decimal('gia', 12, 2)->default(0);
            $table->integer('soLuongTon')->default(0);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('maSanPham')->references('maSanPham')->on('sanpham')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienthesanpham');
    }
};
