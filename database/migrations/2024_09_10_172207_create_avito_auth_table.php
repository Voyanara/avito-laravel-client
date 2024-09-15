<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('avito_module_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->text('client_id');
            $table->text('client_secret');
            $table->text('grant_type');
            $table->integer('expires_in')->nullable();
            $table->text('access_token')->nullable();

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avito_module_clients');
    }
};
