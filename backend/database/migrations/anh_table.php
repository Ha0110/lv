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
            $table->char('maAnh', 10)->primary();
            $table->char('maBienThe', 10);
            $table->char('duongDan', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anh');
    }
};
