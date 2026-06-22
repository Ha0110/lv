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
            $table->char('maBienThe', 10);
            $table->char('maTT', 10);
            $table->string('giaTri')->nullable();

            $table->primary(['maBienThe', 'maTT']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chitietthuoctinh');
    }
};
