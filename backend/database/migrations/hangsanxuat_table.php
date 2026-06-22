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
            $table->string('maHangSanXuat')->primary();
            $table->string('tenHang')->unique();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hangsanxuat');
    }
};
