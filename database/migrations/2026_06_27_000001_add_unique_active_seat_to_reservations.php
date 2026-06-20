<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Prevent two ACTIVE (pending/accepted) reservations for the same seat in
     * the same session. Canceled reservations are excluded so a seat can be
     * re-booked after a cancellation. Postgres partial unique index.
     */
    public function up(): void
    {
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS reservations_active_seat_unique
            ON reservations (session_id, seat_id)
            WHERE status IN ('pending', 'accepted')
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS reservations_active_seat_unique');
    }
};
