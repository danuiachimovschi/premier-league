<template>
  <div class="min-h-screen bg-gray-50 py-8" style="min-height: 100vh; background-color: #f9fafb; padding: 2rem 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
      <h1 class="text-4xl font-bold text-center text-gray-900 mb-8" style="font-size: 2.25rem; font-weight: bold; text-align: center; color: #111827; margin-bottom: 2rem;">
        Premier League Championship Prediction
      </h1>
      <div v-if="error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ error }}
      </div>
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading...</p>
      </div>
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
        <div class="lg:col-span-1">
          <league-table 
            :teams="teams" 
            :current-week="currentWeek"
          />
          <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <button 
              @click="playNextWeek"
              :disabled="currentWeek >= totalWeeks || loading"
              class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
            >
              Next Week
            </button>
            <button 
              @click="playAllWeeks"
              :disabled="currentWeek >= totalWeeks || loading"
              class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
            >
              Play All
            </button>
            <button 
              @click="resetSeason"
              :disabled="loading"
              class="flex-1 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200"
            >
              Reset
            </button>
          </div>
        </div>
        <div class="lg:col-span-1">
          <match-results 
            :matches="currentWeekMatches" 
            :current-week="currentWeek"
          />
        </div>
        <div class="lg:col-span-1">
          <prediction-panel 
            :predictions="predictions"
            :current-week="currentWeek"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ApiService from '../services/api';

export default {
  name: 'ChampionshipDashboard',
  data() {
    return {
      seasonId: null,
      teams: [],
      matches: {},
      predictions: [],
      currentWeek: 0,
      totalWeeks: 6,
      loading: false,
      error: null
    }
  },
  computed: {
    currentWeekMatches() {
      return this.matches[this.currentWeek] || [];
    }
  },
  async mounted() {
    await this.initializeSeason();
  },
  methods: {
    async initializeSeason() {
      this.loading = true;
      this.error = null;
      
      try {
        const seasonsResponse = await ApiService.getSeasons();
        
        if (seasonsResponse.data && seasonsResponse.data.length > 0) {
          const activeSeason = seasonsResponse.data.find(season => season.status === 'active');
          if (activeSeason) {
            this.seasonId = activeSeason.id;
          } else {
            const newSeasonResponse = await ApiService.createSeason();
            this.seasonId = newSeasonResponse.data.id;
          }
        } else {
          const newSeasonResponse = await ApiService.createSeason();
          this.seasonId = newSeasonResponse.data.id;
        }
        
        await this.loadSeasonData();
      } catch (error) {
        this.error = 'Failed to initialize season: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },
    
    async loadSeasonData() {
      try {
        const tableResponse = await ApiService.getLeagueTable(this.seasonId);
        this.teams = tableResponse.data.standings.map(standing => ({
          name: standing.team.name,
          points: standing.points,
          played: standing.played,
          wins: standing.won,
          draws: standing.drawn,
          losses: standing.lost,
          goalsFor: standing.goals_for,
          goalsAgainst: standing.goals_against,
          goalDifference: standing.goal_difference
        }));
        
        const matchesResponse = await ApiService.getMatches(this.seasonId);
        this.matches = {};
        
        // API returns array of weeks with matches object
        if (Array.isArray(matchesResponse.data)) {
          matchesResponse.data.forEach(weekData => {
            const week = weekData.week;
            const weekMatches = weekData.matches.matches || [];
            this.matches[week] = weekMatches.map(match => ({
              home: match.home_team.name,
              away: match.away_team.name,
              homeGoals: match.home_goals,
              awayGoals: match.away_goals
            }));
          });
        }
        
        this.currentWeek = this.calculateCurrentWeek();
        
        if (this.currentWeek >= 1) {
          await this.loadPredictions();
        }
      } catch (error) {
        this.error = 'Failed to load season data: ' + (error.response?.data?.message || error.message);
      }
    },
    
    calculateCurrentWeek() {
      let lastPlayedWeek = 0;
      
      for (let week = 1; week <= this.totalWeeks; week++) {
        const weekMatches = this.matches[week];
        if (weekMatches && weekMatches.length > 0) {
          const allPlayed = weekMatches.every(match => 
            match.homeGoals !== null && match.awayGoals !== null
          );
          
          if (allPlayed) {
            lastPlayedWeek = week;
          }
        }
      }
      
      return lastPlayedWeek;
    },
    
    async loadPredictions() {
      try {
        const predictionsResponse = await ApiService.getPredictions(this.seasonId);
        if (predictionsResponse.data && predictionsResponse.data.predictions && Array.isArray(predictionsResponse.data.predictions)) {
          this.predictions = predictionsResponse.data.predictions.map(prediction => ({
            team: prediction.team,
            percentage: Math.round(prediction.championship_probability)
          }));
        } else {
          this.predictions = [];
        }
      } catch (error) {
        console.error('Failed to load predictions:', error);
        this.predictions = [];
      }
    },
    
    async playNextWeek() {
      if (this.currentWeek >= this.totalWeeks) return;
      
      this.loading = true;
      this.error = null;
      
      try {
        await ApiService.simulateNextWeek(this.seasonId);
        await this.loadSeasonData();
      } catch (error) {
        this.error = 'Failed to simulate next week: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },
    
    async playAllWeeks() {
      if (this.currentWeek >= this.totalWeeks) return;
      
      this.loading = true;
      this.error = null;
      
      try {
        await ApiService.simulateAllWeeks(this.seasonId);
        await this.loadSeasonData();
      } catch (error) {
        this.error = 'Failed to simulate all weeks: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },
    
    async resetSeason() {
      if (!confirm('Are you sure you want to reset the season? All match results will be cleared.')) {
        return;
      }
      
      this.loading = true;
      this.error = null;
      
      try {
        await ApiService.resetSeason(this.seasonId);
        await this.loadSeasonData();
        this.predictions = [];
      } catch (error) {
        this.error = 'Failed to reset season: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>