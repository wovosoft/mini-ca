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
        Schema::create('root_cas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain');
            $table->text('description')->nullable();
            $table->longText('private_key');
            $table->longText('public_key')->nullable();
            $table->longText('certificate');
            $table->string('passphrase')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->text('revocation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('root_cas');
    }
};
