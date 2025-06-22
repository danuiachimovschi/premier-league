import axios from 'axios';

const API_BASE_URL = '/api';

class ApiService {
    async getSeasons() {
        const response = await axios.get(`${API_BASE_URL}/seasons`);
        return response.data;
    }

    async createSeason() {
        const response = await axios.post(`${API_BASE_URL}/seasons`, {
            name: `Season ${Date.now()}`
        });
        return response.data;
    }

    async resetSeason(seasonId) {
        const response = await axios.delete(`${API_BASE_URL}/seasons/${seasonId}/reset`);
        return response.data;
    }

    async getLeagueTable(seasonId) {
        const response = await axios.get(`${API_BASE_URL}/seasons/${seasonId}/table`);
        return response.data;
    }

    async getMatches(seasonId) {
        const response = await axios.get(`${API_BASE_URL}/seasons/${seasonId}/matches`);
        return response.data;
    }

    async simulateNextWeek(seasonId) {
        const response = await axios.post(`${API_BASE_URL}/seasons/${seasonId}/generate-week`);
        return response.data;
    }

    async simulateAllWeeks(seasonId) {
        const response = await axios.post(`${API_BASE_URL}/seasons/${seasonId}/simulate-all`);
        return response.data;
    }

    async getPredictions(seasonId) {
        const response = await axios.get(`${API_BASE_URL}/seasons/${seasonId}/predictions`);
        return response.data;
    }
}

export default new ApiService();