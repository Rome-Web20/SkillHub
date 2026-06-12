<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Nakakonekta sa mismong transaction ticket ng booking
            $table->foreignId('job_booking_id')->constrained('job_bookings')->onDelete('cascade');
            // Ang nagbigay ng review (Client)
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            // Ang nakatanggap ng review (Worker)
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            
            $table->unsignedTinyInteger('rating'); // 1 hanggang 5 stars
            $table->text('comment')->nullable(); // Mensahe o feedback
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};