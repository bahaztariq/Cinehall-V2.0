<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Sensible defaults
        $now = now();
        DB::table('settings')->insert([
            ['key' => 'cinema_name',   'value' => 'Cinehall',              'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_email', 'value' => 'support@cinehall.test', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'currency',      'value' => 'USD',                   'created_at' => $now, 'updated_at' => $now],
            ['key' => 'default_price', 'value' => '12.50',                 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'vip_price',     'value' => '18.00',                 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'booking_hold_minutes', 'value' => '15',            'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
