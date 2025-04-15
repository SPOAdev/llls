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
    Schema::create('licenses', function (Blueprint $table) {
        $table->id();
        $table->string('license_key')->unique();
		$table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->json('validation_rules')->nullable();
        $table->enum('status', ['active', 'inactive', 'cancelled'])->default('active');
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
