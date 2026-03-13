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
        schema::create('categories', function(Blueprint $table)
        {
            $table->id();
            $table->string('name');
        });

        Schema::create('iteneraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories', 'id')->onDelete('set null');
            $table->string('title', 256);
            $table->enum('status', ['pending', 'visiting', 'visited', 'canceled']);
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iteneraries');
        Schema::dropIfExists('categories');
    }
};
