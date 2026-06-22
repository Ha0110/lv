<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donhang', function (Blueprint $table) {
            $table->string('maDonHang')->primary();
            $table->char('sdt', 10)->nullable();
            $table->string('tenNguoiNhan');
            $table->char('sdtNguoiNhan', 10);
            $table->string('email')->nullable();
            $table->text('diaChiGiaoHang');
            $table->decimal('tongTien', 12, 2);
            $table->integer('diemDaSuDung')->default(0);
            $table->integer('diemDuocTich')->default(0);
            $table->enum('trangThai', ['cho_xu_ly','dang_xu_ly','da_giao','huy'])->default('cho_xu_ly');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('sdt')->references('sdt')->on('nguoidung')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donhang');
    }
};
