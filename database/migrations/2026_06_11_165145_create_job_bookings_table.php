<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_bookings', function (Blueprint $table) {
            $table->id();
            // Kumokonekta sa User ID ng nag-book (Client)
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            // Kumokonekta sa User ID ng gagawa (Worker)
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            
            $table->string('service_title'); // Halimbawa: "Sira na Lababo" o "Walang Kuryente"
            $table->text('description')->nullable();
            $table->string('location'); // Lokasyon ng trabaho
            $table->decimal('price', 8, 2)->default(0.00); // Presyo ng serbisyo
            
            // Status ng booking base sa framework ng system niyo
            $table->enum('status', ['pending', 'accepted', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_bookings');
    }
};