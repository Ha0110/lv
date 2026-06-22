<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diachi', function (Blueprint $table) {
            $table->string('maDiaChi')->primary();
            $table->char('sdt', 10);
            $table->string('tenNguoiNhan')->nullable();
            $table->char('sdtNguoiNhan', 10)->nullable();
            $table->text('diaChi')->nullable();
            $table->string('thanhPho')->nullable();
            $table->boolean('isDefault')->default(false);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('sdt')->references('sdt')->on('nguoidung')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diachi');
    }
};
