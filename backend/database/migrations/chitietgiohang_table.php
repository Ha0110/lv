<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chitietgiohang')) {
            return;
        }

        Schema::create('chitietgiohang', function (Blueprint $table) {
            $table->char('maChiTiet', 10)->primary();
            $table->char('maGioHang', 10);
            $table->char('maBienThe', 10);
            $table->integer('soLuong');
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietgiohang');
    }
};
