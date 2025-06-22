<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'team_seasons';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team_id');
            $table->uuid('season_id');
            $table->integer('played')->default(0);
            $table->integer('won')->default(0);
            $table->integer('drawn')->default(0);
            $table->integer('lost')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->integer('points')->default(0);
            $table->decimal('championship_probability', 5, 2)->default(0);
            $table->json('form')->nullable();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams');
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
            
            $table->unique(['team_id', 'season_id']);
            $table->index(['season_id', 'points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};