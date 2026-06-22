<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('khuyenmai')) {
            return;
        }

        Schema::create('khuyenmai', function (Blueprint $table) {
            $table->char('maKhuyenMai', 10)->primary();
            $table->string('tenKhuyenMai')->nullable();
            $table->decimal('giaTriToiThieu', 12, 2)->nullable();
            $table->char('maBienTheQuaTang', 10)->nullable();
            $table->dateTime('ngayBatDau')->nullable();
            $table->dateTime('ngayKetThuc')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khuyenmai');
    }
};
