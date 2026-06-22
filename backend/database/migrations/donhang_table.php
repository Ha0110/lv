<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('donhang')) {
            return;
        }

        Schema::create('donhang', function (Blueprint $table) {
            $table->char('maDonHang', 10)->primary();
            $table->char('sdt', 10)->nullable();
            $table->string('tenNguoiNhan', 200);
            $table->char('sdtNguoiNhan', 10);
            $table->char('email', 255)->nullable();
            $table->text('diaChiGiaoHang');
            $table->decimal('tongTien', 12, 2);
            $table->integer('diemDaSuDung')->default(0);
            $table->integer('diemDuocTich')->default(0);
            $table->enum('trangThai', ['cho_xu_ly','dang_xu_ly','da_giao','huy'])->default('cho_xu_ly');
            $table->dateTime('createdAt')->useCurrent();
            $table->dateTime('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donhang');
    }
};
