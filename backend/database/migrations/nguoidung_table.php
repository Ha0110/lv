<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nguoidung')) {
            return;
        }

        Schema::create('nguoidung', function (Blueprint $table) {
            $table->string('sdt')->primary();
            $table->string('email')->unique();
            $table->string('matKhau')->nullable();
            $table->string('hoTen')->nullable();
            $table->char('maXacNhan', 6)->nullable();
            $table->dateTime('thoiGianHetHanMaXacNhan')->nullable();
            $table->enum('role', ['customer','admin'])->default('customer');
            $table->boolean('emailVerified')->default(false);
            $table->integer('diemTichLuy')->default(0);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoidung');
    }
};
