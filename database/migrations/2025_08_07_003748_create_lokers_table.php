<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lokers', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('lokasi');
            $table->integer('gaji')->nullable();
            $table->date('deadline')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // pembuat
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lokers');
    }

};
