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
            $table->string('avatar')->nullable()->after('email');
            $table->text('bio')->nullable()->after('password');
            $table->boolean('is_admin')->default(false)->after('bio');
            $table->unsignedInteger('points')->default(0)->after('is_admin');
            $table->unsignedInteger('streak_days')->default(0)->after('points');
            $table->unsignedInteger('longest_streak')->default(0)->after('streak_days');
            $table->timestamp('last_read_at')->nullable()->after('longest_streak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'bio',
                'is_admin',
                'points',
                'streak_days',
                'longest_streak',
                'last_read_at',
            ]);
        });
    }
};
