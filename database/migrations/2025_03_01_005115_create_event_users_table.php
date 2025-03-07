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
        Schema::create('event_users', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')
                  ->references('id')
                  ->on('events')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignUuid('user_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->string('number_certificate')->nullable();
            $table->timestamps();

            $table->unique(['number_certificate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_users');
    }
};
