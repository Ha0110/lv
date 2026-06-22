<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chitietthuoctinh')) {
            return;
        }

        Schema::create('chitietthuoctinh', function (Blueprint $table) {
            $table->string('maBienThe');
            $table->string('maTT');
            $table->string('giaTri')->nullable();

            $table->primary(['maBienThe', 'maTT']);

            $table->foreign('maBienThe')->references('maBienThe')->on('bienthesanpham')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('maTT')->references('maTT')->on('thuoctinh')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietthuoctinh');
    }
};
