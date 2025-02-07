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
        Schema::create('customer_subscription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->boolean('is_paused')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_subscription');
    }
};
