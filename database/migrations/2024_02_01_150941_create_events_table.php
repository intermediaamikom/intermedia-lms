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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('division_id')
                  ->references('id')
                  ->on('divisions')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->datetime('occasion_date');
            $table->dateTime('start_register');
            $table->dateTime('end_register');
            $table->unsignedInteger('quota');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
