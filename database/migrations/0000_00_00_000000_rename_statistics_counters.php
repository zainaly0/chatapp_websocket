<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('websockets_statistics_entries')) {
            DB::statement("ALTER TABLE websockets_statistics_entries CHANGE peak_connection_count peak_connections_count INT");
            DB::statement("ALTER TABLE websockets_statistics_entries CHANGE websocket_message_count websocket_messages_count INT");
            DB::statement("ALTER TABLE websockets_statistics_entries CHANGE api_message_count api_messages_count INT");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('websockets_statistics_entries')) {
            DB::statement("ALTER TABLE websockets_statistics_entries CHANGE peak_connections_count peak_connection_count INT");
            DB::statement("ALTER TABLE websockets_statistics_entries CHANGE websocket_messages_count websocket_message_count INT");
            DB::statement("ALTER TABLE websockets_statistics_entries CHANGE api_messages_count api_message_count INT");
        }
    }
};