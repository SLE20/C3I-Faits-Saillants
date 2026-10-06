<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('reference', 40)->unique();
            $table->string('title', 255);

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            // La date peut être connue sans que l'heure soit connue.
            $table->date('event_date')->index();
            $table->time('event_time')->nullable();

            $table->text('location_details')->nullable();
            $table->longText('description');
            $table->text('involved_parties')->nullable();

            // NULL = inconnu ; 0 = aucun cas signalé.
            $table->unsignedInteger('deaths_count')->nullable();
            $table->unsignedInteger('injuries_count')->nullable();
            $table->unsignedInteger('kidnapped_count')->nullable();
            $table->unsignedInteger('arrests_count')->nullable();

            $table->text('material_damage')->nullable();
            $table->text('seizures')->nullable();

            $table->string('source_type', 50)->nullable();
            $table->text('source_details')->nullable();
            $table->string('source_reference', 255)->nullable();

            $table->string('reliability', 30)
                ->default('not_assessed');

            $table->string('importance', 30)
                ->default('medium')
                ->index();

            $table->string('confidentiality', 30)
                ->default('internal');

            $table->string('verification_status', 30)
                ->default('to_verify')
                ->index();

            $table->longText('follow_up')->nullable();
            $table->text('analyst_notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};