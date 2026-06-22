<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('danhmuc')) {
            return;
        }

        Schema::create('danhmuc', function (Blueprint $table) {
            $table->string('maDanhMuc')->primary();
            $table->string('tenDanhMuc')->unique();
            $table->text('moTa')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danhmuc');
    }
};
