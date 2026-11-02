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
        Schema::create('rsa_keys', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->unique();
            $table->text('public_key');
            $table->text('encrypted_private_key');
            $table->string('key_algorithm', 50)->default('RSA-PSS');
            $table->unsignedSmallInteger('key_size')->default(2048);
            $table->enum('status', ['active', 'retired'])->default('active');
            $table->timestamp('generated_at');
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsa_keys');
    }
};
