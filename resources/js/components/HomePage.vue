<template>
    <div id="app">
        <div class="navbar">
            <router-link to="/" class="logo-link">
                <img src="@/assets/images/image.png" alt="logo" class="logo" />
            </router-link>
            <router-link to="/" class="nl brand-link">
                <h5>SBH BOOKING</h5>
            </router-link>
            <div class="login-container">
                <router-link to="/login"><img src="@/assets/images/login.png" alt="login" class="loginlogo" /></router-link>
                <router-link to="/login" class="login">Login</router-link>
            </div>
            <div class="sign-container">
                <router-link to="/register"><img src="@/assets/images/login.png" alt="sign" class="signlogo" /></router-link>
                <router-link to="/register" class="sign">Sign Up</router-link>
            </div>
            <div class="menu-container">
                <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage" />
                <select class="Menu" id="menuSelect" @change="navigateTo">
                    <option value="Menu" disabled selected hidden>Menu</option>
                    <option value="/recently_searched">Recently Searched</option>
                    <option value="/help_support">Help & Support</option>
                </select>
            </div>
        </div>
        <div class="overall">
        <div class="home">
            <h4 class="helper">WELCOME TO SURIGAO BOARDING HOUSE BOOKING SYSTEM</h4>
            <p class="tex">Where boarding houses are affordable for every users.</p>
        </div>

        <div class="search-bar">
            <img src="@/assets/images/search.png" alt="search" class="search-logo" />
            <div class="search-container">
                <input 
                    class="search" 
                    type="text" 
                    placeholder="Search for Location" 
                    v-model="searchLocation"
                    @input="handleSearchInput"
                />
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

            <p class="cale">Day In</p>
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
          <p class="cal">Day Out</p>
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

            <img src="@/assets/images/person.png" alt="logo" class="loogo" />
            <div class="guest-controls-wrapper" style="position: relative;">
                <label class="cals" @click="toggleControls">
                    Bed and Guest in every Room <br />
                    Beds {{ beds }}, Persons {{ persons }}, Rooms {{ rooms }}
                </label>
                
                <div class="cons" v-if="controlsVisible" @click.stop>
                    <div class="items">
                        <label for="beds">Beds</label>
                        <button @click="decrease('beds')" :disabled="beds <= 1" class="butses">-</button>
                        <input class="size" type="number" id="beds" v-model="beds" min="1" :max="maxBeds" />
                        <button @click="increase('beds')" :disabled="beds >= maxBeds" class="butse">+</button>
                    </div>
                    
                    <div class="items">
                        <label for="persons">Persons</label>
                        <button @click="decrease('persons')" :disabled="persons <= 1" class="butsre">-</button>
                        <input class="sizes" type="number" id="persons" v-model="persons" min="1" :max="maxPersons" />
                        <button @click="increase('persons')" :disabled="persons >= maxPersons" class="butsr">+</button>
                    </div>
                    
                    <div class="items">
                        <label for="rooms">Rooms</label>
                        <button @click="decrease('rooms')" :disabled="rooms <= 1" class="buts">-</button>
                        <input class="sizee" type="number" id="rooms" v-model="rooms" min="1" :max="maxRooms" />
                        <button @click="increase('rooms')" :disabled="rooms >= maxRooms" class="butsv">+</button>
                    </div>
                    
                    <div class="control-buttons">
                        <button @click="reset" class="but">Reset</button>
                        <button @click="confirm" class="butss">Confirm</button>
                    </div>
                </div>
            </div>
            <input type="button" value="Find" class="button" @click="handleBooking" />
        </div>

        <div class="searchI">
            <p class="searchitem">Recently Searched</p>
            <p class="search-display">None searched yet.</p>
        </div>

        <div class="reminder" v-show="reminderVisible">
            <div class="container">
                <img src="@/assets/images/smile.png" alt="smile" class="smile" />
                <img src="@/assets/images/X.png" alt="Close" class="x" style="cursor: pointer;" @click="hideReminder" />
                <div class="pase">
                    <p>
                        Please <router-link to="/user_register" class="signup">Sign Up</router-link> first for better use of the platform. 
                        If you already have an account, then click <router-link to="/user_login" class="signup">Login</router-link>.
                    </p>
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
                <router-link to="/personal-info" class="footer-link">Donot sell my Personal Information</router-link>
                <router-link to="/help" class="footer-link">Help</router-link>
                <router-link to="/cyber-security" class="footer-link">Cyber Security</router-link>
                <router-link to="/how-it-works" class="footer-link">Learn how SBH BOOKING works</router-link>
            </div>
            <div class="copywright">
                <p>SBH BOOKING, Surigao City, Surigao Del Norte, Philippines <br />
                    Copyright 2024 SBH BOOKING | All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
// Import the functions from the scripts file
import { showReminder as showReminderExternal, hideReminder as hideReminderExternal } 
from '@/assets/js/homepage_scripts/scripts.js';
import axios from 'axios';

export default {
    name: 'Homepage',
    setup() {
        const router = useRouter(); // Initialize the router

        const beds = ref(1);
        const persons = ref(1);
        const rooms = ref(1);
        const searchLocation = ref('');
        const timeIn = ref('');
        const timeOut = ref('');
        const controlsVisible = ref(false);
        const reminderVisible = ref(false);
        const maxPersons = ref(48);
        const maxBeds = ref(48);
        const maxRooms = ref(6);

        const dayInInput = ref(null);
        const dayOutInput = ref(null);
        const locationSuggestions = ref([]);
        const showLocationDropdown = ref(false);

        const toggleControls = () => {
            controlsVisible.value = !controlsVisible.value;
        };

        const increase = (type) => {
            switch(type) {
                case 'beds':
                    if (beds.value < maxBeds.value) {
                        beds.value++;
                        if (persons.value < maxPersons.value) {
                            persons.value++;
                        }
                        updateBedsAndRooms();
                    }
                    break;
                case 'persons':
                    if (persons.value < maxPersons.value) {
                        persons.value++;
                        updateBedsAndRooms();
                    }
                    break;
                case 'rooms':
                    if (rooms.value < maxRooms.value) {
                        rooms.value++;
                        if (persons.value < maxPersons.value) persons.value++;
                        if (beds.value < maxBeds.value) beds.value++;
                    }
                    break;
            }
        };

        const decrease = (type) => {
            switch(type) {
                case 'beds':
                    if (beds.value > 1) {
                        beds.value--;
                        if (persons.value > 1) {
                            persons.value--;
                        }
                        updateBedsAndRooms();
                    }
                    break;
                case 'persons':
                    if (persons.value > 1) {
                        persons.value--;
                        updateBedsAndRooms();
                    }
                    break;
                case 'rooms':
                    if (rooms.value > 1) {
                        rooms.value--;
                        persons.value = Math.max(1, persons.value - 1);
                        beds.value = Math.max(1, beds.value - 1);
                    }
                    break;
            }
        };

        const reset = () => {
            beds.value = 1;
            persons.value = 1;
            rooms.value = 1;
        };

        const confirm = () => {
            controlsVisible.value = false;
        };

        const showReminder = () => {
            reminderVisible.value = true;
            showReminderExternal(); // Call the external function if needed
        };

        const hideReminder = () => {
            reminderVisible.value = false;
            hideReminderExternal(); // Call the external function if needed
        };

        const navigateTo = (event) => {
            const selectedValue = event.target.value;
            if (selectedValue && selectedValue !== "Menu") {
                router.push({
                    path: selectedValue,
                    query: {
                        beds: beds.value,
                        persons: persons.value,
                        rooms: rooms.value,
                        timeIn: timeIn.value,
                        timeOut: timeOut.value,
                        location: searchLocation.value
                    }
                });
            }
        };

        const updateBedsAndRooms = () => {
            beds.value = Math.min(Math.ceil(persons.value / 1), maxBeds.value);
            rooms.value = Math.min(Math.ceil(persons.value / 8), maxRooms.value);
        };

        const handleBooking = () => {
            // Check for dates
            if (!timeIn.value || !timeOut.value) {
                alert('Please select check-in and check-out dates');
                return;
            }
            
            // Check for location
            if (!searchLocation.value) {
                alert('Please enter a location');
                return;
            }

            // Check for valid booking values
            if (beds.value < 1 || persons.value < 1 || rooms.value < 1) {
                alert('Please select valid number of beds, persons, and rooms');
                return;
            }

            // Check maximum limits
            if (persons.value > maxPersons.value) {
                alert(`Maximum number of persons allowed is ${maxPersons.value}`);
                return;
            }

            if (beds.value > maxBeds.value) {
                alert(`Maximum number of beds allowed is ${maxBeds.value}`);
                return;
            }

            if (rooms.value > maxRooms.value) {
                alert(`Maximum number of rooms allowed is ${maxRooms.value}`);
                return;
            }

            // Save the search data before showing reminder
            saveSearch();
            
            // Show reminder (for non-logged in users)
            showReminder();
        };

        // Load saved search data
        const loadSavedSearch = () => {
            try {
                const saved = localStorage.getItem('lastSearch');
                if (saved) {
                    const data = JSON.parse(saved);
                    searchLocation.value = data.location || '';
                    timeIn.value = data.timeIn || '';
                    timeOut.value = data.timeOut || '';
                    beds.value = data.beds || 0;
                    persons.value = data.persons || 0;
                    rooms.value = data.rooms || 0;
                }
            } catch (error) {
                console.error('Error loading saved search:', error);
                // Handle the error gracefully without breaking the app
            }
        };

        // Save search data
        const saveSearch = () => {
            try {
                const newSearch = {
                    location: searchLocation.value,
                    timeIn: timeIn.value,
                    timeOut: timeOut.value,
                    beds: beds.value,
                    persons: persons.value,
                    rooms: rooms.value,
                    date: new Date().toLocaleString() // Add timestamp
                };

                // Get existing searches
                let searches = [];
                const existingSearches = localStorage.getItem('searchHistory');
                if (existingSearches) {
                    searches = JSON.parse(existingSearches);
                }

                // Add new search to beginning of array
                searches.unshift(newSearch);

                // Keep only the last 5 searches
                searches = searches.slice(0, 5);

                // Save back to localStorage
                localStorage.setItem('searchHistory', JSON.stringify(searches));

                // Also save as last search for compatibility
                localStorage.setItem('lastSearch', JSON.stringify(newSearch));
            } catch (error) {
                console.error('Error saving search:', error);
            }
        };

        const isValidForm = computed(() => {
            return searchLocation.value &&
                   timeIn.value &&
                   timeOut.value &&
                   beds.value > 0 &&
                   persons.value > 0 &&
                   rooms.value > 0;
        });

        const validateDates = () => {
            const start = new Date(timeIn.value);
            const end = new Date(timeOut.value);
            return start < end;
        };

        const handleError = (error) => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        };

        const safeNavigate = async (route) => {
            try {
                await router.push(route);
            } catch (error) {
                handleError(error);
            }
        };

        const toggleDatePicker = (type) => {
            if (type === 'dayIn') {
                dayInInput.value.showPicker();
            } else if (type === 'dayOut') {
                dayOutInput.value.showPicker();
            }
        };

        const handleSearchInput = async () => {
            if (!searchLocation.value) {
                locationSuggestions.value = [];
                showLocationDropdown.value = false;
                return;
            }

            try {
                const response = await axios.get('/boarding-houses/available', {
                    params: { 
                        query: searchLocation.value,
                        limit: 50  // Increase the limit significantly
                    }
                });

                if (response.data.status === 'success') {
                    locationSuggestions.value = response.data.data;
                    showLocationDropdown.value = true;
                }
            } catch (error) {
                console.error('Error fetching locations:', error);
                locationSuggestions.value = [];
            }
        };

        const selectLocation = (location) => {
            searchLocation.value = location.address;
            showLocationDropdown.value = false;
        };

        // Close dropdown when clicking outside
        const handleClickOutside = (event) => {
            if (!event.target.closest('.search-container')) {
                showLocationDropdown.value = false;
            }
        };

        onMounted(() => {
            document.addEventListener('click', handleClickOutside);
        });

        onUnmounted(() => {
            document.removeEventListener('click', handleClickOutside);
        });

        return {
            beds, persons, rooms, searchLocation, timeIn, timeOut,
            controlsVisible, reminderVisible, toggleControls,
            increase, decrease, reset, confirm, showReminder, hideReminder, navigateTo, updateBedsAndRooms, handleBooking,
            saveSearch,
            isValidForm,
            validateDates,
            handleError,
            safeNavigate,
            maxPersons,
            maxBeds,
            maxRooms,
            toggleDatePicker,
            dayInInput,
            dayOutInput,
            locationSuggestions,
            showLocationDropdown,
            handleSearchInput,
            selectLocation
        };
    },
};
</script>

<style scoped>
@import '@/assets/css/homepage.css';

.search-container {
    position: relative;
    flex: 1;
    width: 100%;
}

.location-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 9999;
    margin-top: 4px;
    width: 100%;
}

.location-item {
    padding: 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.location-item:last-child {
    border-bottom: none;
}

.location-item:hover {
    background-color: #f5f5f5;
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
    font-size: 0.9em;
    color: #666;
}

.location-info {
    font-size: 0.8em;
    color: #794646;
}

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

/* Ensure the dropdown appears above other elements */
.search-bar {
    position: relative;
    z-index: 100;
}

/* Style the scrollbar */
.location-dropdown::-webkit-scrollbar {
    width: 8px;
}

.location-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.location-dropdown::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.location-dropdown::-webkit-scrollbar-thumb:hover {
    background: #555;
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
