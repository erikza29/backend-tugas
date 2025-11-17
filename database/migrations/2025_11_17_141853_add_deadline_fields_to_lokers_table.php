<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lokers', function (Blueprint $table) {
            $table->integer('deadline_value')->nullable()->after('gaji');
            $table->enum('deadline_unit', ['jam', 'hari'])->nullable()->after('deadline_value');
            $table->dateTime('deadline_end')->nullable()->after('deadline_unit');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokers', function (Blueprint $table) {
            //
        });
    }
};
