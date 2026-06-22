<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('diachi')) {
            return;
        }

        Schema::create('diachi', function (Blueprint $table) {
            $table->char('maDiaChi', 10)->primary();
            $table->char('sdt', 10);
            $table->string('tenNguoiNhan', 200)->nullable();
            $table->char('sdtNguoiNhan', 10)->nullable();
            $table->text('diaChi')->nullable();
            $table->string('thanhPho')->nullable();
            $table->boolean('isDefault')->default(false);
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diachi');
    }
};
