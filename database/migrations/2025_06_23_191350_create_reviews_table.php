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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reviewable'); // Creates reviewable_type and reviewable_id
            $table->integer('rating');
            $table->text('comment');
            $table->enum('review_type', ['user', 'property', 'service_provider']);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('reviewable_type');
            $table->index('reviewable_id');
            $table->index('reviewer_id');
            $table->index('review_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
