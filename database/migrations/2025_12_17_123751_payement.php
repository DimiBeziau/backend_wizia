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
        if (! Schema::hasTable('payment_users')) {
            Schema::create('payment_users', function (Blueprint $table) {
                $table->id();
                $table->integer('idUser');
                $table->integer('idAbonnements');
                $table->datetime('datePayement');
                $table->datetime('dateStart');
                $table->datetime('dateEnd');
                $table->datetime('dateCancel')->nullable();
                $table->boolean('cancelAbonnement')->default(false);
                $table->string('paymentMethod')->default('stripe');
                $table->string('idTransaction');
                $table->string('currency', 3)->default('EUR');
                $table->boolean('isRecurring')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_users');

    }
};
