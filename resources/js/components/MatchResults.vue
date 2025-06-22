<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-green-600 text-white px-6 py-4">
      <h2 class="text-xl font-bold">Match Results</h2>
    </div>
    <div class="p-6">
      <div class="text-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ getWeekTitle() }}
        </h3>
      </div>
      <div v-if="matches.length > 0" class="space-y-4">
        <div 
          v-for="(match, index) in matches" 
          :key="index"
          class="bg-gray-50 rounded-lg p-4 border border-gray-200"
        >
          <div class="flex items-center justify-between">
            <div class="text-right flex-1">
              <span class="text-sm font-medium text-gray-900">{{ match.home }}</span>
            </div>
            <div class="mx-4 bg-white rounded-lg px-4 py-2 border-2 border-gray-300 shadow-sm">
              <span class="text-lg font-bold text-gray-900">
                {{ match.homeGoals }} - {{ match.awayGoals }}
              </span>
            </div>
            <div class="text-left flex-1">
              <span class="text-sm font-medium text-gray-900">{{ match.away }}</span>
            </div>
          </div>
          <div class="mt-2 text-center">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="getResultClass(match)">
              {{ getResultText(match) }}
            </span>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-8">
        <div class="text-gray-400 mb-4">
          <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-gray-500 font-medium">No matches this week</p>
        <p class="text-gray-400 text-sm mt-1">Click "Next Week" to see upcoming fixtures</p>
      </div>
      <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm text-gray-600">
          <span>Week {{ currentWeek }}</span>
          <span>{{ matches.length }} {{ matches.length === 1 ? 'match' : 'matches' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MatchResults',
  props: {
    matches: {
      type: Array,
      default: () => []
    },
    currentWeek: {
      type: Number,
      default: 1
    }
  },
  methods: {
    getWeekTitle() {
      const weekSuffix = this.getOrdinalSuffix(this.currentWeek);
      return `${this.currentWeek}${weekSuffix} Week Match Results`;
    },
    getOrdinalSuffix(number) {
      const j = number % 10;
      const k = number % 100;
      if (j === 1 && k !== 11) {
        return 'st';
      }
      if (j === 2 && k !== 12) {
        return 'nd';
      }
      if (j === 3 && k !== 13) {
        return 'rd';
      }
      return 'th';
    },
    getResultClass(match) {
      if (match.homeGoals > match.awayGoals) {
        return 'bg-blue-100 text-blue-800';
      } else if (match.awayGoals > match.homeGoals) {
        return 'bg-red-100 text-red-800';
      } else {
        return 'bg-yellow-100 text-yellow-800';
      }
    },
    getResultText(match) {
      if (match.homeGoals > match.awayGoals) {
        return `${match.home} Win`;
      } else if (match.awayGoals > match.homeGoals) {
        return `${match.away} Win`;
      } else {
        return 'Draw';
      }
    }
  }
}
</script>