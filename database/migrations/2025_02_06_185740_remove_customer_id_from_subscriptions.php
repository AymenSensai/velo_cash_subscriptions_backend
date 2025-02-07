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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']); // Drop foreign key constraint (if exists)
            $table->dropColumn('customer_id');    // Drop the column
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        });
    }
};
