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
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')
                ->nullable()
                ->comment('Social login provider name');
            $table->string('provider_id')
                ->nullable()
                ->comment('Social login provider unique identification');
            $table->string('avatar')
                ->nullable()
                ->comment('User profile picture');
            $table->string('password')
                ->nullable()
                ->change()
                ->comment('User password hash, null for social access users');
            
            $table->unique(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id', 'avatar']);
        });
    }
};
