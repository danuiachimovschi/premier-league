import './bootstrap';
import { createApp } from 'vue';

import ChampionshipDashboard from './components/ChampionshipDashboard.vue';
import LeagueTable from './components/LeagueTable.vue';
import MatchResults from './components/MatchResults.vue';
import PredictionPanel from './components/PredictionPanel.vue';

const app = createApp({});

app.component('championship-dashboard', ChampionshipDashboard);
app.component('league-table', LeagueTable);
app.component('match-results', MatchResults);
app.component('prediction-panel', PredictionPanel);
app.mount('#app');
