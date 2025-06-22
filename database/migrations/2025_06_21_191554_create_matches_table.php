<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'games';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('season_id');
            $table->uuid('home_team_id');
            $table->uuid('away_team_id');
            $table->integer('home_goals')->nullable();
            $table->integer('away_goals')->nullable();
            $table->integer('week');
            $table->json('game_statistics')->nullable();
            $table->boolean('is_played')->default(false);
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
            $table->foreign('home_team_id')->references('id')->on('teams');
            $table->foreign('away_team_id')->references('id')->on('teams');
            
            $table->index(['season_id', 'week']);
            $table->unique(['season_id', 'home_team_id', 'away_team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};