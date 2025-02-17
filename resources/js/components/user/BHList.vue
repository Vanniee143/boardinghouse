<template>
  <div id="app">
    <Navbare />
    <div class="overall">
      <div class="home">
        <h4 class="helper">Boarding House List</h4>
      </div>

      <!-- Move search criteria display here -->
      <div class="search-criteria" v-if="searchParams.location">
        <p class="search-info">
          Showing results for: 
          <span class="search-term">{{ searchParams.location }}</span>
          with minimum 
          <span class="search-term">{{ searchParams.beds }} beds</span>
          in 
          <span class="search-term">{{ searchParams.rooms }} rooms</span>
        </p>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="loading-state">
        <p>Loading boarding houses...</p>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="boardingHouses.length === 0" class="empty-state">
        <p>No boarding houses found. Please try different search criteria.</p>
      </div>

      <!-- Boarding houses list -->
      <div v-else v-for="house in boardingHouses" 
           :key="house.boarding_house_id" 
           class="house-card">
        <div class="house-grid">
          <!-- Left side: Image and Title -->
          <div class="left-column">
            <h4 class="house-title">{{ house.name }}</h4>
            <div class="house-image-container">
              <img 
                :src="getBoardingHouseImage(house.bh_images)" 
                :alt="house.name"
                class="house-image"
                @error="handleImageError"
                @click="openModal(getBoardingHouseImage(house.bh_images))"
                ref="houseImage"
                style="cursor: pointer"
              >
            </div>
          </div>

          <!-- Right side: Info and booking -->
          <div class="house-info">
            <div class="details-section">
              <div class="booking-info">
                <p class="booking-title">SBH Booking</p>
                
                <!-- Add details section -->
                <div class="details-info">
                  <div class="info-group">
                    <h5 class="info-title">Description</h5>
                    <p class="info-text">{{ house.description }}</p>
                  </div>

                  <div class="info-group">
                    <h5 class="info-title">Location</h5>
                    <p class="info-text">{{ house.address }}</p>
                  </div>

                  <div class="info-group">
                    <h5 class="info-title">Landlord Information</h5>
                    <p class="info-text">
                      <span class="info-label">Name:</span>{{ house.landlord_name }}<br>
                      <span class="info-label">Contact:</span> {{ house.landlord_phone }}
                    </p>
                  </div>
                </div>

                <button @click="goToRooms(house.boarding_house_id)" class="book-button">View Rooms</button>
                <p class="review-title">Reviews & Ratings</p>
                <button class="view-button" @click="viewReviews(house.boarding_house_id)">View</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3931.712556440649!2d125.49050407450618!3d9.790371276607797!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zOcKwNDcnMjUuMyJOIDEyNcKwMjknMzUuMSJF!5e0!3m2!1sen!2sph!4v1730087382440!5m2!1sen!2sph" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>

    <!-- Add modal for enlarged image -->
    <div v-if="showImageModal" class="image-modal" @click="closeModal">
      <div class="modal-content">
        <img :src="selectedImage" alt="Enlarged view" class="modal-image">
        <button class="close-button" @click="closeModal">&times;</button>
      </div>
    </div>
  </div>
</template>

<script>
import Navbare from './layouts/Navbare.vue';
import axios from 'axios';

export default {
  name: 'BoardingList',
  components: {
    Navbare
  },
  data() {
    return {
      boardingHouses: [],
      loading: true,
      error: null,
      searchParams: {
        location: '',
        beds: 1,
        rooms: 1
      },
      showImageModal: false,
      selectedImage: null
    }
  },
  methods: {
    getBoardingHouseImage(image) {
      if (image) {
        console.log('Original boarding house image path:', image);
        return `/storage/${image}`;
      }
      // Use a default image from public folder
      return require('@/assets/images/room1.png');
    },

    handleImageError(e) {
      console.error('Image failed to load:', e.target.src);
      // Set fallback image
      e.target.src = require('@/assets/images/room1.png');
    },

    async fetchBoardingHouses() {
      this.loading = true;
      this.error = null;
      
      try {
        // Update searchParams from route query
        this.searchParams = {
          location: this.$route.query.location || '',
          beds: parseInt(this.$route.query.beds) || 1,
          rooms: parseInt(this.$route.query.rooms) || 1
        };

        const response = await axios.get('/boarding-houses/available', {
          params: {
            query: this.searchParams.location,
            beds: this.searchParams.beds,
            rooms: this.searchParams.rooms
          }
        });

        if (response.data && response.data.status === 'success') {
          // Filter boarding houses based on location
          this.boardingHouses = response.data.data.filter(house => {
            const searchTerm = this.searchParams.location.toLowerCase();
            return (
              house.address.toLowerCase().includes(searchTerm) ||
              house.name.toLowerCase().includes(searchTerm)
            );
          });
          
          if (this.boardingHouses.length === 0) {
            this.error = 'No boarding houses found matching your criteria';
          }
        } else {
          throw new Error(response.data?.message || 'Failed to fetch boarding houses');
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        this.error = error.response?.data?.message || 'Failed to load boarding houses. Please try again.';
        this.boardingHouses = [];
      } finally {
        this.loading = false;
      }
    },
    viewReviews(boardingHouseId) {
      this.$router.push({
        name: 'user.reviews',
        params: { id: boardingHouseId }
      });
    },
    goToRooms(boardingHouseId) {
      this.$router.push({
        name: 'user.rooms',
        params: { id: boardingHouseId }
      });
    },
    openModal(imageSrc) {
      this.selectedImage = imageSrc;
      this.showImageModal = true;
      document.body.style.overflow = 'hidden';
    },
    closeModal() {
      this.showImageModal = false;
      this.selectedImage = null;
      document.body.style.overflow = 'auto';
    }
  },
  created() {
    this.fetchBoardingHouses();
  },
  watch: {
    '$route.query': {
      handler() {
        this.fetchBoardingHouses();
      },
      immediate: true
    }
  }
}
</script>

<style scoped>
.overall {
  padding: 40px 20px;
  max-width: 1200px;
  margin: 0;
  margin-left: 20px;
  margin-top: 3%;
}

.house-card {
  background: var(--sds-size-stroke-border) solid #BEBEBE;
  border-radius: 8px;
  box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
  margin-bottom: 30px;
  padding: 20px 15px;
  width: 62%;
  margin-left: 0;
}

.house-grid {
  display: grid;
  grid-template-columns: 300px 300px;
  gap: 20px;
  align-items: start;
  justify-content: start;
}

.house-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
  padding: 0;
}

.house-image-container {
  width: 400px;
  height: 500px;
  overflow: hidden;
  background: #f5f5f5;
  border-radius: 8px;
}

.house-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.house-title {
  font-size: 20px;
  color: #333;
  margin: 10px 0;
  padding: 0;
  font-weight: 600;
}

.details-section {
  margin-top: 16px;
  background: #CFFFD7;
  box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
  border-radius: 8px;
  margin-left: 90px;
  padding: 15px;
  width: 300px; 
}

.booking-info {
  width: 100%;
}

.booking-title {
  font-size: 18px; /* Reduced from 22px */
  font-weight: bold;
  color: #000000;
  margin-bottom: 15px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e0e0e0;
  text-align: center;
}

.details-info {
  margin-bottom: 20px;
  text-align: center;
}

.info-group {
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.info-group:last-child {
  border-bottom: none;
}

.info-title {
  font-size: 18px; /* Reduced from 16px */
  font-weight: 600;
  color: #000000;
  margin-bottom: 5px;
}

.info-text {
  color: #000000;
  line-height: 1.4;
  font-size: 18px;
  margin: 0;
}

.info-label {
  font-size: 18px;
  font-weight: 500;
  color: #000000;
  display: inline-block;
  width: 60px; /* Reduced from 70px */
}

.book-button {
  width: 40%;
  background: #000000;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  margin: 15px auto;
  transition: background 0.3s;
  display: block;
}

.book-button:hover {
  background: #3a3a3a;
}

.review-title {
  font-size: 18px;
  text-align: center;
  color: #000000;
  margin: 10px 0;
}

.view-button {
  width: 40%;
  background: #000000;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 13px;
  transition: background 0.3s;
  display: block;
  margin: 0 auto;
}

.view-button:hover {
  background: #3a3a3a;
}

/* Keep the existing map and filter styles */
.map {
  position: fixed;
  top: 90px;
  right: 20px;
  width: 640px;
  height: 570px;
  z-index: 10;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  padding: 10px;
}

.map iframe {
  width: 100%;
  height: 100%;
  border-radius: 4px;
}

/* Loading and error states */
.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 2rem;
  margin: 2rem 0;
  background: #f8f9fa;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.error-state {
  color: #dc3545;
  background: #f8d7da;
}

.empty-state {
  color: #666;
}

/* Responsive layout */
@media (max-width: 1200px) {
  .house-card {
    margin-right: 0;
    max-width: 100%;
  }
  
  .map, .room-size {
    width: 250px;
  }
  
  .map {
    height: 200px;
  }
}

@media (max-width: 768px) {
  .map {
    position: static;
    width: 100%;
    margin-bottom: 20px;
  }
  
  .house-grid {
    grid-template-columns: 1fr;
  }

  .house-image-container {
    min-height: 300px;
  }

  .house-image {
    min-height: 300px;
  }

  .details-section {
    padding: 15px;
  }

  .info-group {
    margin-bottom: 12px;
    padding-bottom: 12px;
  }

  .info-title {
    font-size: 15px;
  }

  .info-text {
    font-size: 13px;
  }

  .left-column {
    width: 100%;
  }
  
  .house-title {
    text-align: center;
  }

  .house-card {
    margin: 0 0 20px 0;
    width: 100%;
  }
  
  .helper {
    margin-left: 0;
  }
  
  .loading-state, .error-state, .empty-state {
    margin-left: 0;
  }
}

.left-column {
  width: 300px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.helper {
  text-align: left;
  margin-left: 0;
  margin-bottom: 30px;
  margin-top: 20px;
}

.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.modal-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.close-button {
  position: absolute;
  top: -40px;
  right: -40px;
  background: none;
  border: none;
  color: white;
  font-size: 30px;
  cursor: pointer;
  padding: 10px;
}

.close-button:hover {
  color: #ddd;
}

/* Add hover effect to the house image */
.house-image:hover {
  opacity: 0.9;
  transform: scale(1.02);
  transition: all 0.3s ease;
}

/* Add these new styles */
.search-criteria {
  background: #f8f9fa;
  padding: 1rem 1.5rem;
  margin: 1rem 0 2rem 0;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  width: 61%; /* Match the width of house cards */
}

.search-info {
  color: #333;
  font-size: 1rem;
  margin: 0;
  line-height: 1.5;
}

.search-term {
  color: #794646;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  background: rgba(121, 70, 70, 0.1);
  border-radius: 4px;
}
</style>

