<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hangsanxuat')) {
            return;
        }

        Schema::create('hangsanxuat', function (Blueprint $table) {
            $table->char('maHangSanXuat', 10)->primary();
            $table->string('tenHang', 150)->unique('uk_tenHang');
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hangsanxuat');
    }
};
