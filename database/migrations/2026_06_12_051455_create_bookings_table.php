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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // ID ng kliyente (ang nag-book)
            $table->unsignedBigInteger('worker_id'); // ID ng worker (ang binu-book)
            $table->string('category');              // Kategorya (e.g., Plumbing)
            $table->string('status')->default('Pending'); // Katayuan: Pending, Accepted, Completed, Cancelled
            $table->timestamps();

            // 🚀 Foreign keys para konektado sa users table pre
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('worker_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};