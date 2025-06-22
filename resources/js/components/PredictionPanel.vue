<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="bg-purple-600 text-white px-6 py-4">
      <h2 class="text-xl font-bold">{{ getTitle() }}</h2>
    </div>
    
    <div class="p-6">
      <div class="space-y-3">
        <div 
          v-for="(prediction, index) in sortedPredictions" 
          :key="prediction.team"
          class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors duration-150"
        >
          <div class="flex items-center">
            <span class="inline-flex items-center justify-center w-8 h-8 mr-3 text-sm font-bold text-white rounded-full" :class="getPositionColor(index)">
              {{ index + 1 }}
            </span>
            <div>
              <span class="text-sm font-medium text-gray-900">{{ prediction.team }}</span>
              <div class="text-xs text-gray-500">{{ getPositionText(index) }}</div>
            </div>
          </div>
          <div class="text-right">
            <span class="text-lg font-bold" :class="getPercentageColor(prediction.percentage)">
              {{ prediction.percentage }}%
            </span>
            <div class="text-xs text-gray-500">chance</div>
          </div>
        </div>
      </div>
      <div class="mt-6 space-y-2">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Championship Probability</h4>
        <div 
          v-for="prediction in sortedPredictions" 
          :key="prediction.team + '-bar'"
          class="relative"
        >
          <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
            <span>{{ prediction.team }}</span>
            <span>{{ prediction.percentage }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="h-2 rounded-full transition-all duration-500 ease-out" 
              :class="getBarColor(prediction.percentage)"
              :style="{ width: prediction.percentage + '%' }"
            ></div>
          </div>
        </div>
      </div>
      <div class="mt-6 pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-4 text-center">
          <div class="bg-blue-50 rounded-lg p-3">
            <div class="text-xl font-bold text-blue-600">{{ getFavorite().team }}</div>
            <div class="text-xs text-blue-500 uppercase tracking-wide">Favorite</div>
          </div>
          <div class="bg-green-50 rounded-lg p-3">
            <div class="text-xl font-bold text-green-600">{{ getFavorite().percentage }}%</div>
            <div class="text-xs text-green-500 uppercase tracking-wide">Probability</div>
          </div>
        </div>
        <div class="mt-4 text-center text-xs text-gray-500">
          Predictions updated after Week {{ currentWeek }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PredictionPanel',
  props: {
    predictions: {
      type: Array,
      required: true
    },
    currentWeek: {
      type: Number,
      default: 1
    }
  },
  computed: {
    sortedPredictions() {
      return [...this.predictions].sort((a, b) => b.percentage - a.percentage);
    }
  },
  methods: {
    getTitle() {
      const weekSuffix = this.getOrdinalSuffix(this.currentWeek);
      return `${this.currentWeek}${weekSuffix} Week Predictions of Championship`;
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
    getPositionColor(index) {
      const colors = [
        'bg-yellow-500', 
        'bg-gray-400',    
        'bg-orange-600',  
        'bg-red-500'  
      ];
      return colors[index] || 'bg-gray-500';
    },
    getPositionText(index) {
      const positions = ['Champion Favorite', 'Strong Contender', 'Outside Chance', 'Longshot'];
      return positions[index] || 'Contender';
    },
    getPercentageColor(percentage) {
      if (percentage >= 40) {
        return 'text-green-600';
      } else if (percentage >= 20) {
        return 'text-yellow-600';
      } else if (percentage >= 10) {
        return 'text-orange-600';
      } else {
        return 'text-red-600';
      }
    },
    getBarColor(percentage) {
      if (percentage >= 40) {
        return 'bg-green-500';
      } else if (percentage >= 20) {
        return 'bg-yellow-500';
      } else if (percentage >= 10) {
        return 'bg-orange-500';
      } else {
        return 'bg-red-500';
      }
    },
    getFavorite() {
      return this.sortedPredictions[0] || { team: 'TBD', percentage: 0 };
    }
  }
}
</script>