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
            $table->char('maDanhMuc', 10)->primary();
            $table->string('tenDanhMuc', 100)->unique('uk_tenDanhMuc');
            $table->text('moTa')->nullable();
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danhmuc');
    }
};
