<template>
  <div id="app">
    <Navbar />
    
    <div class="overall">
      <div class="home">
        <h4 class="helper">WELCOME TO SURIGAO BOARDING HOUSE BOOKING SYSTEM</h4>
        <p class="tex">Where boarding houses are affordable for every users.</p>
      </div>

      <div class="search-bar">
        <img src="@/assets/images/search.png" alt="search" class="search-logo">
        <div class="search-container">
          <input 
            class="search" 
            type="text" 
            placeholder="Search for Location" 
            v-model="searchLocation"
            @input="handleSearchInput"
          >
          <div class="location-dropdown" v-if="showLocationDropdown && locationSuggestions.length > 0">
            <div 
              v-for="location in locationSuggestions" 
              :key="location.boarding_house_id"
              class="location-item"
              @click="selectLocation(location)"
            >
              <div class="location-details">
                <div class="location-name">{{ location.name }}</div>
                <div class="location-address">
                  <i class="fas fa-map-marker-alt"></i>
                  {{ location.address }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="vertical-line"></div>
        <label class="cale">Day In</label>
        <div class="date-picker-container">
          <img src="@/assets/images/Calendar.png" class="menuimg" alt="Calendar">
          <input 
            type="date" 
            class="time_in" 
            id="time_in"
            ref="dayInInput"
            @click="toggleDatePicker('dayIn')"
            v-model="timeIn"
          >
        </div>

        <div class="vertical-line"></div>
        <label class="cal">Day Out</label>
        <div class="date-picker-container">
          <img src="@/assets/images/Calendar.png" class="menuimg" alt="Calendar">
          <input 
            type="date" 
            class="time_out" 
            id="time_out"
            ref="dayOutInput"
            @click="toggleDatePicker('dayOut')"
            v-model="timeOut"
          >
        </div>

        <div class="vertical-line"></div>
        <div class="guest-controls-wrapper">
          <img src="@/assets/images/person.png" alt="logo" class="loogo">
          <label class="cals" @click="toggleControls">
            Bed and Room <br>
            Beds {{ beds }}, Rooms {{ rooms }}
          </label>
        </div>
        <button type="button" class="button" @click="handleBooking">Find</button>
      </div>

      <div class="booking-controls" v-show="controlsVisible">
        <div class="cons">
          <div class="items">
            <label for="beds">Beds</label>
            <button @click="decrease('beds')" class="butse" :disabled="beds <= 1">-</button>    
            <input class="size" type="number" id="beds" v-model="beds" min="1" readonly>
            <button @click="increase('beds')" class="butse" :disabled="beds >= maxBeds">+</button>
          </div>

          <div class="items">
            <label for="rooms">Rooms</label>
            <button @click="decrease('rooms')" class="buts" :disabled="rooms <= 1">-</button>
            <input class="sizee" type="number" id="rooms" v-model="rooms" min="1" readonly>
            <button @click="increase('rooms')" class="buts" :disabled="rooms >= maxRooms">+</button>
          </div>

          <div class="control-buttons">
            <button @click="reset" class="but">Reset</button>
            <button @click="confirm" class="butss">Confirm</button>
          </div>
        </div>
      </div>

      <div class="searchI">
        <p class="searchitem">Recently Searched</p>
        <div class="recent-searches-container">
          <div v-if="searchHistory.length === 0" class="search-display">
            None searched yet.
          </div>
          <div v-else class="recent-searches-list">
            <div v-for="(search, index) in searchHistory" 
                 :key="index" 
                 class="recent-search-item">
              <div class="search-content" @click="handleRecentSearch(search)">
                <p class="search-location">
                  <i class="fas fa-map-marker-alt"></i> 
                  {{ search.location }}
                </p>
                <p class="search-specs">
                  {{ search.beds }} beds, {{ search.rooms }} rooms
                </p>
                <p class="search-date">{{ formatDate(search.date) }}</p>
              </div>
              <button class="delete-search" @click.stop="deleteSearch(index)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>        
    </div>

    <div class="footer">
      <div class="social">
        <p class="footer-title">SBH BOOKING</p>
        <div class="social-icons">
          <a href="https://www.facebook.com/ivann.mendoza.501" target="_blank" rel="noopener noreferrer">
            <img src="@/assets/images/fb icon.png" alt="Facebook" class="icons">
          </a>
          <a href="https://x.com/lisondra_angelo" target="_blank" rel="noopener noreferrer">
            <img src="@/assets/images/Twitter Or X icon.png" alt="Twitter" class="icons">
          </a>
          <a href="https://www.instagram.com/vannieealakdan/" target="_blank" rel="noopener noreferrer">
            <img src="@/assets/images/Instagram icon.png" alt="Instagram" class="icons">
          </a>
          <a href="https://www.youtube.com/@clarenceangelolisondra1356" target="_blank" rel="noopener noreferrer">
            <img src="@/assets/images/Youtube icon.png" alt="Youtube" class="icons">
          </a>
        </div>
      </div>

      <div class="infos">
        <router-link to="/about_us" class="footer-link">About us</router-link>
        <router-link to="/terms" class="footer-link">Terms & Conditions</router-link>
        <router-link to="/legal" class="footer-link">Legal Information</router-link>
        <router-link to="/privacy" class="footer-link">Privacy Notice</router-link>
      </div>

      <div class="infos2">
        <router-link to="/personal-info" class="footer-link">Do not sell my Personal Information</router-link>
        <router-link to="/help" class="footer-link">Help</router-link>
        <router-link to="/cyber-security" class="footer-link">Cyber Security</router-link>
        <router-link to="/how-it-works" class="footer-link">Learn how SBH BOOKING works</router-link>
      </div>

      <div class="copywright">
        <p>SBH BOOKING, Surigao City, Surigao Del Norte, Philippines <br>
          Copyright 2024 SBH BOOKING | All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '@/components/user/layouts/Navbar.vue'
import axios from 'axios'

export default {
  name: 'UserHomePage',
  components: {
    Navbar
  },
  data() {
    return {
      beds: 1,
      rooms: 1,
      controlsVisible: false,
      maxBeds: 48,
      maxRooms: 6,
      searchLocation: '',
      timeIn: '',
      timeOut: '',
      isLoggedIn: false,
      userName: '',
      recentSearches: [],
      locationSuggestions: [],
      showLocationDropdown: false,
      searchHistory: []
    }
  },
  methods: {
    toggleControls() {
      this.controlsVisible = !this.controlsVisible
    },
    increase(type) {
      if (type === 'beds' && this.beds < this.maxBeds) {
        this.beds++
      } else if (type === 'rooms' && this.rooms < this.maxRooms) {
        this.rooms++
        if (this.beds < this.maxBeds) this.beds++
      }
    },
    decrease(type) {
      if (type === 'beds' && this.beds > 1) {
        this.beds--
      } else if (type === 'rooms' && this.rooms > 1) {
        this.rooms--
        if (this.beds > 1) this.beds--
      }
    },
    reset() {
      this.beds = 1
      this.rooms = 1
    },
    confirm() {
      this.controlsVisible = false
    },
    toggleDatePicker(input) {
      const inputElement = this.$refs[input + 'Input']
      inputElement.showPicker()
    },
    handleBooking() {
      if (!this.searchLocation) {
        alert('Please enter a location');
        return;
      }

      // Create search history entry
      const searchEntry = {
        location: this.searchLocation,
        timeIn: this.timeIn,
        timeOut: this.timeOut,
        beds: this.beds,
        rooms: this.rooms,
        persons: this.beds, // Assuming persons equals beds
        date: new Date().toLocaleString(),
      };

      // Get existing search history
      let searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
      
      // Add new search to beginning of array
      searchHistory.unshift(searchEntry);
      
      // Keep only last 10 searches
      searchHistory = searchHistory.slice(0, 10);
      
      // Save to localStorage
      localStorage.setItem('searchHistory', JSON.stringify(searchHistory));

      // Navigate to BHList with parameters
      this.$router.push({
        name: 'user.boarding-houses',
        query: {
          location: this.searchLocation,
          beds: this.beds,
          rooms: this.rooms
        }
      });
    },
    async handleSearchInput() {
      if (!this.searchLocation) {
        this.locationSuggestions = [];
        this.showLocationDropdown = false;
        return;
      }

      try {
        // Add console.log to see what we're sending
        console.log('Searching for:', this.searchLocation);
        
        const response = await axios.get('/boarding-houses/available', {
          params: { 
            query: this.searchLocation,
            limit: 50  // Increase the limit significantly
          }
        });

        // Add console.log to see the raw response
        console.log('API Response:', response.data);

        if (response.data.status === 'success') {
          this.locationSuggestions = response.data.data;
          // Log the number of suggestions
          console.log('Number of suggestions:', this.locationSuggestions.length);
          this.showLocationDropdown = true;
        }
      } catch (error) {
        console.error('Error fetching locations:', error);
        this.locationSuggestions = [];
      }
    },
    selectLocation(location) {
      this.searchLocation = location.address;
      this.showLocationDropdown = false;
      // Remove automatic navigation - only update the input field
    },
    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      });
    },
    handleRecentSearch(search) {
      this.searchLocation = search.location;
      this.beds = search.beds;
      this.rooms = search.rooms;
      this.timeIn = search.timeIn;
      this.timeOut = search.timeOut;
      
      this.handleBooking();
    },
    loadSearchHistory() {
      try {
        const searches = JSON.parse(localStorage.getItem('searchHistory') || '[]');
        this.searchHistory = searches; // Show all searches
      } catch (error) {
        console.error('Error loading search history:', error);
        this.searchHistory = [];
      }
    },
    deleteSearch(index) {
      try {
        let searches = JSON.parse(localStorage.getItem('searchHistory') || '[]');
        searches.splice(index, 1);
        localStorage.setItem('searchHistory', JSON.stringify(searches));
        this.searchHistory = searches; // Show all searches
      } catch (error) {
        console.error('Error deleting search:', error);
      }
    }
  },
  created() {
    // Check authentication on component creation
    const token = localStorage.getItem('token')
    const userType = localStorage.getItem('userType')
    
    if (!token || userType !== 'user') {
      this.$router.push('/login')
    }
  },
  mounted() {
    this.loadSearchHistory();
  }
}
</script>

<style scoped>
@import "@/assets/css/user_homepage.css";
.router-link-active.logo-link,
.router-link-exact-active.logo-link,
.router-link-active.nl,
.router-link-exact-active.nl {
  background-color: transparent !important;
  text-decoration: none !important;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
}

.search-container {
  position: relative;
  flex: 1;
}

.location-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #f5f5f2;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  max-height: 300px;
  overflow-y: auto;
  z-index: 1000;
}

.location-item {
  padding: 10px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
}

.location-item:last-child {
  border-bottom: none;
}

.location-item:hover {
  background-color: #d7d7d7;
}

.location-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.location-name {
  font-weight: 600;
  color: #333;
}

.location-address {
  font-size: 1em;
  color: #666;
  display: flex;
  align-items: center;
  gap: 6px;
}

.location-address i {
  color: #000000;
  font-size: 1em;
}

.recent-searches-container {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 10px;
  margin-top: 10px;
  width: 100%;
  max-width: 1235px;
  margin-bottom: 50px;
}

.recent-searches-list {
  max-height: 400px; /* Height for 2 items plus a bit extra to show there's more */
  overflow-y: auto;
  scrollbar-width: thin;
}

.recent-searches-list::-webkit-scrollbar {
  width: 6px;
}

.recent-searches-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.recent-searches-list::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.recent-search-item {
  background: white;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.recent-search-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-content {
  flex-grow: 1;
  cursor: pointer;
}

.search-location {
  color: #794646;
  font-weight: 600;
  margin: 0;
}

.search-specs {
  color: #666;
  font-size: 0.9em;
  margin: 0;
}

.search-date {
  color: #888;
  font-size: 0.8em;
  margin: 0;
}

.searchI {
  margin: 150px auto 50px;
  width: 1235px;
  padding: 0 20px;
}

.searchitem {
  font-weight: 600;
  color: #794646;
  margin-bottom: 20px;
  font-size: 1.2em;
  width: auto;
}

.delete-search {
  background: none;
  border: none;
  color: #dc3545;
  padding: 4px 8px;
  cursor: pointer;
  transition: color 0.2s ease;
  opacity: 0;
  margin-left: 8px;
}

.search-bar{
    width: 1200px;
    height: 88px;
    background: #e3e2e2;
    box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
    transform: translate(127px, 100px);   
    display: flex;
    align-items: center;
    padding: 0 25px;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}

.recent-search-item:hover .delete-search {
  opacity: 1;
}

.delete-search:hover {
  color: #c82333;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.overall {
  flex: 1;
  position: relative;
  padding-bottom: 200px; /* Space for footer */
}

.footer {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: 20px 0;
}

/* Adjust search section spacing */
.searchI {
  margin: 150px auto 50px;  /* Added bottom margin */
  width: 1235px;
  padding: 0 20px;
}

.recent-searches-container {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 10px;
  margin-top: 10px;
  width: 100%;
  max-width: 1235px;
  margin-bottom: 50px;  /* Increased bottom margin */
}

.social-icons a {
    text-decoration: none;
    display: inline-block;
    transition: transform 0.2s ease;
}

.social-icons a:hover {
    transform: scale(1.1);
}

.footer-link {
  color: inherit;
  text-decoration: none;
  transition: color 0.2s ease;
}

.footer-link:hover {
  color: #794646;
}

.infos, .infos2 {
    display: flex;
    flex-direction: column;
    gap: 15px;  /* Adds vertical spacing between links */
    margin: 20px 0;  /* Adds margin above and below sections */
}

.footer-link {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
    padding: 5px 0;  /* Adds padding above and below each link */
}

.footer {
    display: flex;
    flex-direction: column;
    gap: 20px;  /* Adds spacing between footer sections */
    padding: 30px 0;  /* Adds padding to the footer */
}

</style>    