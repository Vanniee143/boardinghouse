<template>
    <div>
      <div class="navbar">
        <router-link to="/">
          <img src="@/assets/images/image.png" alt="logo" class="logo" />
        </router-link>
        <router-link to="/" class="nl">
          <h5>SBH BOOKING</h5>
        </router-link>

        <div class="login-container">
          <router-link to="/login">
            <img src="@/assets/images/login.png" alt="login" class="loginlogo" />
          </router-link>
          <router-link to="/login" class="login">Login</router-link>
        </div>

        <div class="sign-container">
          <router-link to="/register">
            <img src="@/assets/images/login.png" alt="sign" class="signlogo" />
          </router-link>
          <router-link to="/register" class="sign">Sign Up</router-link>
        </div>

        <div class="menu-container">
          <img src="@/assets/images/Menu.png" alt="menu" class="menuimg" id="menuImage" />
          <select class="Menu" id="menuSelect" v-model="selectedMenu" @change="navigateMenu">
            <option value="Menu" disabled selected hidden>Menu</option>
            <option value="recently_searched">Recently Searched</option>
            <option value="help_support">Help & Support</option>
          </select>
        </div>
      </div>

      <!-- Add sidebar back -->
      <div class="sidebar">
        <ul>
          <li>
            <router-link to="/" class="back-link">← &nbsp;Back</router-link>
          </li>
          <li>
            <router-link to="/recently_searched" class="recent">Recently Searched</router-link>
          </li>
          <li>
            <router-link to="/help_support" class="help">Help & Support</router-link>
          </li>
        </ul>
      </div>

      <div class="account-container">
        <div class="personal-info">
          <h4 class="helper">Recently Searched</h4>
          
          <div class="search-content">
            <div v-if="searchHistory.length" class="search-history">
              <div v-for="(search, index) in searchHistory" :key="index" class="search-item">
                <p class="search-details">
                  <span class="search-location">Location: {{ search.location }}</span><br>
                  Check-in: {{ search.timeIn }}<br>
                  Check-out: {{ search.timeOut }}<br>
                  Beds: {{ search.beds }}<br>
                  Persons: {{ search.persons }}<br>
                  Rooms: {{ search.rooms }}<br>
                  <span class="search-date">Searched on: {{ search.date }}</span>
                </p>
                <button @click="deleteSearch(index)" class="delete-btn">
                  <img src="@/assets/images/trash.png" alt="Delete" class="delete-icon">
                </button>
              </div>
            </div>
            <p v-else class="no-results">None searched yet.</p>
          </div>
        </div>
      </div>
    </div>

  </template>

  <script>
  export default {
    name: "RecentlySearched",
    data() {
      return {
        selectedMenu: "Menu",
        searchHistory: []
      };
    },
    mounted() {
      this.loadSearchHistory();
      // Set up auto-refresh every 30 seconds
      this.refreshInterval = setInterval(this.loadSearchHistory, 30000);
    },
    beforeUnmount() {
      if (this.refreshInterval) {
        clearInterval(this.refreshInterval);
      }
    },
    methods: {
      navigateMenu() {
        if (this.selectedMenu === "recently_searched") {
          this.$router.push("/recently_searched");
        } else if (this.selectedMenu === "help_support") {
          this.$router.push("/help_support");
        }
      },

      loadSearchHistory() {
        try {
          const searches = localStorage.getItem('searchHistory');
          this.searchHistory = searches ? JSON.parse(searches) : [];
        } catch (error) {
          console.error('Error loading search history:', error);
          this.searchHistory = [];
        }
      },

      deleteSearch(index) {
        try {
          // Get current searches
          let searches = JSON.parse(localStorage.getItem('searchHistory') || '[]');
          
          // Remove the search at the specified index
          searches.splice(index, 1);
          
          // Save back to localStorage
          localStorage.setItem('searchHistory', JSON.stringify(searches));
          
          // Update the component's data
          this.searchHistory = searches;
        } catch (error) {
          console.error('Error deleting search:', error);
        }
      }
    },
  };

</script>

  <style>
  @import "@/assets/css/recently.css";
  .logo{
    margin-left: 15px;
}

.nl {
  text-decoration: none;
  position: relative;
}

.nl::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 2px;
  bottom: -4px;
  left: 0;
  background-color: #794646;
  transform: scaleX(0);
  transform-origin: bottom right;
  transition: transform 0.3s ease-out;
}

.nl:hover::after {
  transform: scaleX(1);
  transform-origin: bottom left;
}

.nl h5 {
  color: #794646;
  margin: 0;
  transform: translate(0%, 10%);
  font-size: 1.5rem;
  font-weight: 700;
}
  </style>







  






