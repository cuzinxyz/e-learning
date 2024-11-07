<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('authentication_codes', function (Blueprint $table) {
            $table->id();
            $table
                ->integer('user_id')
                ->nullable();
            $table
                ->string('authentication_code_hash')
                ->nullable();
            $table
                ->timestamp('expires_at')
                ->nullable();
            $table
                ->smallInteger('code_type')
                ->nullable()
                ->comment('1: Register, 2: Reset password, 3: Re-authentication');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authentication_codes');
    }
};
