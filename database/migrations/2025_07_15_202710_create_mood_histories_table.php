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
        Schema::create('mood_histories', function (Blueprint $table) {
            $table->id();
            $table->text('text'); // User's mood description
            $table->string('mood', 20); // Mood category (happy, sad, etc.)
            $table->float('polarity', 8, 4); // Sentiment polarity (-1.0 to 1.0)
            $table->float('subjectivity', 8, 4); // Sentiment subjectivity (0.0 to 1.0)
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_histories');
    }
};
