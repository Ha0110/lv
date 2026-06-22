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
            $table->char('maBienThe', 10)->primary();
            $table->char('maSanPham', 10);
            $table->decimal('gia', 12, 2);
            $table->integer('soLuongTon')->default(0);
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienthesanpham');
    }
};
