<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('thanhtoan')) {
            return;
        }

        Schema::create('thanhtoan', function (Blueprint $table) {
            $table->char('maThanhToan', 10)->primary();
            $table->char('maDonHang', 10);
            $table->enum('phuongThuc', ['cod','vnpay']);
            $table->decimal('soTien', 12, 2);
            $table->char('maGiaoDich', 255)->nullable();
            $table->enum('trangThai', ['cho_thanhtoan','thanh_cong','that_bai','da_hoan_tien'])->default('cho_thanhtoan');
            $table->dateTime('thoiGianThanhToan')->nullable();
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thanhtoan');
    }
};
