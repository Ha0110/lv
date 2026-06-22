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
            $table->char('sdt', 10)->primary();
            $table->char('email', 255);
            $table->string('matKhau')->nullable();
            $table->string('hoTen', 200)->nullable();
            $table->char('maXacNhan', 6)->nullable();
            $table->dateTime('thoiGianHetHanMaXacNhan')->nullable();
            $table->enum('role', ['customer', 'admin', 'owner'])->default('customer');
            $table->boolean('emailVerified')->default(false);
            $table->integer('diemTichLuy')->default(0);
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();

            $table->unique('email', 'uk_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoidung');
    }
};
