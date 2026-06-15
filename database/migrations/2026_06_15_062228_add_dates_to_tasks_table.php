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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'start_date')) {
            $table->date('start_date')->nullable()->after('position');
            }
            if (!Schema::hasColumn('tasks', 'due_date')) {
            $table->date('due_date')->nullable()->after('start_date');
        }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'due_date']);
        });
    }
};
