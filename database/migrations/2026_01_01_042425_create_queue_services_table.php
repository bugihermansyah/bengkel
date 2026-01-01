<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queue_services', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('number');
            $table->foreignUlid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignUlid('mechanic_id')->constrained('users')->cascadeOnDelete();
            $table->mediumText('complaint')->nullable();
            $table->string('status')->default('pending'); // Assuming default status
            $table->mediumText('notes')->nullable();
            $table->timestamp('process_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_services');
    }
};
