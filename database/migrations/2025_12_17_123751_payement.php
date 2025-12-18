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
        Schema::create('payement_user', function (Blueprint $table) {
            $table->id();
            $table->integer('idUser');
            $table->integer('idAbonnements');
            $table->datetime('datePayement');
            $table->datetime('dateStart');
            $table->datetime('dateEnd');
            $table->datetime('dateCancel')->nullable();
            $table->boolean('cancelAbonnement')->default(false); // abonnement annulé
            $table->string('paymentMethod')->default('stripe'); // méthode de paiement utilisée
            $table->string('idTransaction');// identifiant de la transaction
            $table->string('currency', 3)->default('EUR'); // ISO 4217 currency code
            $table->boolean('isRecurring')->default(true);// il pay mensuellement
            $table->text('notes')->nullable();// commentaires     
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
