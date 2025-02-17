<template>

  <div id="app">

    <UserLayout />


    <div class="account-container">

      <div class="personal-info">

        <h4 class="helper">Recently Searched</h4>

        <div v-if="searchHistory.length" class="search-history">

          <div v-for="(search, index) in searchHistory" :key="index" class="search-item">

            <button class="delete-btn" @click.stop="deleteSearch(index)">

              <i class="fas fa-trash"></i>

            </button>

            <div class="search-details">

              <p class="search-location">

                <i class="fas fa-map-marker-alt"></i> 

                {{ search.location }}

              </p>

              <div class="search-info">

                <p class="dates">

                  <span class="label">Check-in:</span> 

                  {{ formatDate(search.timeIn) }}

                </p>

                <p class="dates">

                  <span class="label">Check-out:</span> 

                  {{ formatDate(search.timeOut) }}

                </p>

                <p class="specs">

                  <span class="label">Beds:</span> {{ search.beds }} |

                  <span class="label">Rooms:</span> {{ search.rooms }}

                </p>

              </div>

              <p class="search-date">Searched on: {{ search.date }}</p>

            </div>

            <div class="search-action">

              <button class="search-again-btn" @click="navigateToSearch(search)">Search Again</button>

            </div>

          </div>

        </div>

        <div v-else class="empty-state">

          <p class="tex">None searched yet.</p>

          <p class="sub-text">Your search history will appear here</p>

        </div>

      </div>

    </div>

  </div>

</template>



<script>
import UserLayout from './layouts/UserLayout.vue';

export default {

  name: 'UserRecentlySearched',
  components: {
    UserLayout
  },

  data() {

    return {

      searchHistory: []

    }

  },

  methods: {

    loadSearchHistory() {

      try {

        const searches = localStorage.getItem('searchHistory');

        this.searchHistory = searches ? JSON.parse(searches) : [];

      } catch (error) {

        console.error('Error loading search history:', error);

        this.searchHistory = [];

      }

    },

    formatDate(date) {

      if (!date) return 'Not specified';

      return new Date(date).toLocaleDateString();

    },

    navigateToSearch(search) {

      this.$router.push({

        name: 'user.boarding-houses',

        query: {

          location: search.location,

          beds: search.beds,

          rooms: search.rooms

        }

      });

    },

    deleteSearch(index) {

      let searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');

      searchHistory.splice(index, 1);

      localStorage.setItem('searchHistory', JSON.stringify(searchHistory));

      this.searchHistory = searchHistory;

    }

  },

  mounted() {

    this.loadSearchHistory();

    // Refresh search history every 30 seconds

    this.refreshInterval = setInterval(this.loadSearchHistory, 30000);

  },

  beforeUnmount() {

    if (this.refreshInterval) {

      clearInterval(this.refreshInterval);

    }

  }

}

</script>



<style scoped>

@import '@/assets/css/user_your_account.css';

/* Remove padding-top from account-container */
.account-container {
  position: relative;
  height: 100vh;
  overflow: hidden;
}

.personal-info {
  position: fixed;
  height: calc(105vh - 65px);
  width: 91%;
  overflow-y: auto;
  padding: 20px;
  background: #EEE;
}

.helper {
  top: 0;
  background: #EEE;
  padding: 15px 0;
  color: #794646;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 20px;
  z-index: 10;
}

.search-history {
  margin-top: 20px;
  max-width: 800px;
  padding-bottom: 40px; /* Add space at bottom for scrolling */
}

.search-item {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  position: relative;
  width: 118%;
  transition: all 0.3s ease;
}

.search-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.search-location {
  color: #794646;
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 10px;
}

.search-info {
  margin: 10px 0;
}

.label {
  font-weight: 500;
  color: #666;
  margin-right: 5px;
}

.dates {
  margin: 5px 0;
  color: #333;
}

.specs {
  color: #555;
  margin: 5px 0;
}

.search-date {
  color: #888;
  font-size: 0.9rem;
  margin-top: 10px;
}

.search-action {
  position: absolute;
  bottom: 15px;
  right: 15px;
}

.search-again-btn {
  background: #794646;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.search-again-btn:hover {
  background: #623939;
}

.delete-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #dc3545;
  color: white;
  border: none;
  padding: 8px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.delete-btn:hover {
  background: #c82333;
}

.search-details {
  padding-right: 40px;
  margin-bottom: 50px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #666;
}

.sub-text {
  color: #999;
  margin-top: 10px;
  font-size: 0.9rem;
}

/* Add scrollbar styling for better appearance */
.personal-info::-webkit-scrollbar {
  width: 8px;
}

.personal-info::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.personal-info::-webkit-scrollbar-thumb {
  background: #794646;
  border-radius: 4px;
}

.personal-info::-webkit-scrollbar-thumb:hover {
  background: #623939;
}

@media (max-width: 768px) {
  .personal-info {
    left: 0;
    padding: 15px;
  }
}

</style>
