<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-blue-600 text-white px-6 py-4">
      <h2 class="text-xl font-bold">League Table</h2>
    </div>
    
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teams</th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">PTS</th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">W</th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">D</th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">L</th>
            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GD</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="(team, index) in sortedTeams" 
            :key="team.name"
            :class="getRowClass(index)"
            class="hover:bg-gray-50 transition-colors duration-150"
          >
            <td class="px-4 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <span class="inline-flex items-center justify-center w-6 h-6 mr-3 text-xs font-bold text-gray-600 bg-gray-100 rounded-full">
                  {{ index + 1 }}
                </span>
                <span class="text-sm font-medium text-gray-900">{{ team.name }}</span>
              </div>
            </td>
            <td class="px-3 py-4 text-center text-sm font-semibold text-gray-900">{{ team.points }}</td>
            <td class="px-3 py-4 text-center text-sm text-gray-500">{{ team.played }}</td>
            <td class="px-3 py-4 text-center text-sm text-gray-500">{{ team.wins }}</td>
            <td class="px-3 py-4 text-center text-sm text-gray-500">{{ team.draws }}</td>
            <td class="px-3 py-4 text-center text-sm text-gray-500">{{ team.losses }}</td>
            <td class="px-3 py-4 text-center text-sm font-medium" :class="getGoalDifferenceClass(team.goalDifference)">
              {{ team.goalDifference > 0 ? '+' : '' }}{{ team.goalDifference }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <span>Week {{ currentWeek }} of 6</span>
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-1"></div>
            <span class="text-xs">Championship</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LeagueTable',
  props: {
    teams: {
      type: Array,
      required: true
    },
    currentWeek: {
      type: Number,
      default: 1
    }
  },
  computed: {
    sortedTeams() {
      return [...this.teams].sort((a, b) => {
        if (b.points !== a.points) {
          return b.points - a.points;
        }
        if (b.goalDifference !== a.goalDifference) {
          return b.goalDifference - a.goalDifference;
        }
        return b.goalsFor - a.goalsFor;
      });
    }
  },
  methods: {
    getRowClass(index) {
      if (index === 0) {
        return 'bg-yellow-50 border-l-4 border-yellow-400';
      }
      return '';
    },
    getGoalDifferenceClass(goalDifference) {
      if (goalDifference > 0) {
        return 'text-green-600';
      } else if (goalDifference < 0) {
        return 'text-red-600';
      }
      return 'text-gray-900';
    }
  }
}
</script>