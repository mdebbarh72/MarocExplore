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

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itenerary_id')->constrained('iteneraries', 'id')->onDelete('cascade');
            $table->string('title',256);
            $table->string('address', 512);
            $table->timestamps();
        });

        Schema::create('visiting_places', function(Blueprint $table)
        {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations', 'id')->onDelete('cascade');
            $table->string('name', 256);
            $table->timestamps();
        });

        schema::create('activities', function(Blueprint $table)
        {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations', 'id')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        schema::create('dishes', function(Blueprint $table)
        {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations', 'id')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinations');

        Schema::dropIfExists('visiting_places');

        schema::dropIfExists('activities');

        Schema::dropIfExists('dishes');
    }
};
