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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->enum('message_type', ['general', 'property_inquiry', 'roommate_inquiry'])->default('general');
            $table->timestamps();
            
            // Indexes
            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
