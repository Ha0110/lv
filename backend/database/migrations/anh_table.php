<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('anh')) {
            return;
        }

        Schema::create('anh', function (Blueprint $table) {
            $table->string('maAnh')->primary();
            $table->string('maBienThe');
            $table->string('duongDan');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent();

            $table->foreign('maBienThe')->references('maBienThe')->on('bienthesanpham')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anh');
    }
};
