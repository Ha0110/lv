<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thanhtoan', function (Blueprint $table) {
            $table->string('maThanhToan')->primary();
            $table->string('maDonHang');
            $table->enum('phuongThuc', ['cod','vnpay']);
            $table->decimal('soTien', 12, 2);
            $table->string('maGiaoDich')->nullable();
            $table->enum('trangThai', ['cho_thanhtoan','thanh_cong','that_bai','da_hoan_tien'])->default('cho_thanhtoan');
            $table->dateTime('thoiGianThanhToan')->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('maDonHang')->references('maDonHang')->on('donhang')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thanhtoan');
    }
};
