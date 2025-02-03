<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the billing_day column as an integer (1-31)
            $table->tinyInteger('billing_day')->nullable()->comment('Day of the month for billing (1-31)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the billing_day column if the migration is rolled back
            $table->dropColumn('billing_day');
        });
    }
};
