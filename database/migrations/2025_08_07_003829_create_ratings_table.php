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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yangreting_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('target_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('loker_id')->constrained('lokers')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->timestamps();

            $table->unique(['yangreting_id', 'target_id', 'loker_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
