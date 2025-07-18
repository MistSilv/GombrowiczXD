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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('role')->default('produkcja');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('produkty', function (Blueprint $table) {
            $table->id();
            $table->string('tw_nazwa');
            $table->string('tw_idabaco')->nullable();
            $table->boolean('is_wlasny')->default(false); // true=własny, false=obcy
        });

        Schema::create('ean_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produkt_id')->constrained('produkty')->onDelete('cascade');
            $table->string('kod_ean', 13);
            $table->timestamps();
        });

        Schema::create('automats', function (Blueprint $table) {
            $table->id();
            $table->string('nazwa');
            $table->string('lokalizacja');
            $table->timestamps();
        });

        Schema::create('zamowienia', function (Blueprint $table) {
            $table->id();
            $table->timestamp('data_zamowienia')->useCurrent();
            $table->date('data_realizacji')->nullable();
            $table->unsignedBigInteger('automat_id')->nullable();
            $table->foreign('automat_id')->references('id')->on('automats')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('produkt_zamowienie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zamowienie_id')->constrained('zamowienia')->onDelete('cascade');
            $table->foreignId('produkt_id')->constrained('produkty')->onDelete('cascade');
            $table->integer('ilosc');
        });

        Schema::create('straty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automat_id')->constrained('automats')->onDelete('cascade');
            $table->date('data_straty')->default(now());
            $table->text('opis')->nullable();
            $table->timestamps();
        });

        Schema::create('produkt_strata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strata_id')->constrained('straty')->onDelete('cascade');
            $table->foreignId('produkt_id')->constrained('produkty')->onDelete('cascade');
            $table->integer('ilosc');
            $table->timestamps();
        });

        Schema::create('wsady', function (Blueprint $table) {
            $table->id();
            $table->timestamp('data_wsadu')->useCurrent();
            $table->foreignId('automat_id')->constrained('automats')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('produkt_wsad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wsad_id')->constrained('wsady')->onDelete('cascade');
            $table->foreignId('produkt_id')->constrained('produkty')->onDelete('cascade');
            $table->integer('ilosc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produkt_wsad');
        Schema::dropIfExists('wsady');
        Schema::dropIfExists('produkt_strata');
        Schema::dropIfExists('straty');
        Schema::dropIfExists('produkt_zamowienie');
        Schema::dropIfExists('zamowienia');
        Schema::dropIfExists('ean_codes');
        Schema::dropIfExists('produkty');
        Schema::dropIfExists('automats');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};