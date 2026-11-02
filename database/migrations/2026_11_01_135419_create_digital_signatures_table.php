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
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('rsa_key_id')->constrained()->restrictOnDelete();
            $table->text('signature_base64');
            $table->string('hash_algorithm', 30)->default('SHA-256');
            $table->string('signature_algorithm', 50)->default('RSA-PSS');
            $table->string('payload_hash', 64);
            $table->timestamp('signed_at');
            $table->timestamps();
            $table->index(['rsa_key_id', 'signed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
