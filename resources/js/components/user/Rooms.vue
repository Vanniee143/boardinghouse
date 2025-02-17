<template>
  <div id="app">
    <Navbare />
    <div class="overall">
      <div class="home">
        <h4 class="helper">Available Rooms</h4>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="loading">
        Loading rooms...
      </div>

      <!-- Error state -->
      <div v-if="error" class="error">
        {{ error }}
      </div>

      <!-- No rooms message -->
      <div v-if="!loading && !error && rooms.length === 0" class="no-rooms">
        No available rooms found for this boarding house.
      </div>

      <!-- Rooms list -->
      <div v-else-if="!loading && !error" v-for="room in rooms" 
           :key="room.room_id" 
           class="room-card">
        <div class="room-grid">
          <!-- Left side: Image and Title -->
          <div class="left-column">
            <h4 class="room-title">{{ room.room_name }}</h4>
            <div class="room-image-container">
              <img 
                :src="getRoomImage(room.room_images)" 
                :alt="room.room_name"
                class="room-image"
                @error="handleImageError"
                @click="openModal(getRoomImage(room.room_images))"
                ref="roomImage"
                style="cursor: pointer"
              >
            </div>
          </div>

          <!-- Right side: Info and booking -->
          <div class="room-info">
            <div class="details-section">
              <div class="booking-info">
                <p class="booking-title">Room Details</p>
                
                <div class="details-info">
                  <div class="info-group">
                    <h5 class="info-title">Room Name</h5>
                    <p class="info-text">{{ room.room_name }}</p>
                  </div>

                  <div class="info-group">
                    <h5 class="info-title">Price</h5>
                    <p class="info-text">₱{{ room.price }}/month</p>
                  </div>

                  <div class="info-group">
                    <h5 class="info-title">Bed Quantity</h5>
                    <p class="info-text">{{ room.bed_quantity }} beds</p>
                  </div>

                  <div class="info-group">
                    <h5 class="info-title">Status</h5>
                    <p class="info-text">{{ room.status }}</p>
                  </div>

                  <div class="info-group" v-if="room.map_link">
                    <h5 class="info-title">Map Location</h5>
                    <button @click="openMapModal(room.map_link)" class="map-button">
                      View Map
                    </button>
                  </div>
                </div>

                <button 
  v-if="room.status !== 'occupied' && room.status !== 'under maintenance'" 
  @click="goToBooking(room.room_id)" 
  class="book-button"
>
  Book Now
</button>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModal" class="image-modal">
      <div class="modal-content" @click.stop>
        <img :src="selectedImage" alt="Enlarged view" class="modal-image">
        <button class="close-button" @click="closeModal">&times;</button>
      </div>
    </div>

    <!-- Add Map Modal -->
    <div v-if="showMapModal" class="map-modal">
      <div class="map-modal-content" @click.stop>
        <iframe 
          :src="selectedMapLink"
          width="100%" 
          height="450" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        <button class="close-button" @click="closeMapModal">&times;</button>
      </div>
    </div>
  </div>
</template>

<script>
import Navbare from './layouts/Navbare.vue';
import axios from 'axios';
// Import a default room image from your public folder
import defaultRoomImage from '@/assets/images/room1.png'; // Make sure this image exists in your public folder

export default {
  name: 'Rooms',
  components: {
    Navbare
  },
  data() {
    return {
      rooms: [],
      loading: true,
      error: null,
      showImageModal: false,
      selectedImage: null,
      defaultImage: defaultRoomImage,
      showMapModal: false,
      selectedMapLink: null
    }
  },
  methods: {
    getRoomImage(image) {
      if (image) {
        // If it's already a full URL (including our asset URL), return as is
        if (image.startsWith('http')) {
          return image;
        }
        // If it's a storage path, prepend storage
        if (image.startsWith('/storage/')) {
          return image;
        }
        // If it's just a path, prepend storage
        return `/storage/${image}`;
      }
      // Return default image if no image provided
      return this.defaultImage;
    },

    handleImageError(e) {
      console.log('Image load error:', e);
      e.target.src = this.defaultImage;
    },

    async fetchRooms() {
      try {
        this.loading = true;
        this.error = null;
        
        const boardingHouseId = this.$route.params.id;
        console.log('Fetching rooms for boarding house:', boardingHouseId);
        
        const response = await axios.get(`/user/rooms/${boardingHouseId}/list`);
        
        if (response.data && response.data.status === 'success') {
          // Log the image paths for debugging
          console.log('Room data:', response.data.data);
          this.rooms = response.data.data.map(room => ({
            ...room,
            room_images: room.room_images || null
          }));
        } else {
          throw new Error(response.data?.message || 'Failed to fetch rooms');
        }
      } catch (error) {
        console.error('Error details:', error);
        this.error = 'Failed to load rooms: ' + (error.response?.data?.message || error.message);
      } finally {
        this.loading = false;
      }
    },

    goToBooking(roomId) {
      this.$router.push({
        name: 'user.booking',
        params: { 
          id: roomId,
          boardingHouseId: this.$route.params.id
        }
      });
    },

    openModal(imageSrc) {
      if (!imageSrc || imageSrc === this.defaultImage) return;
      this.selectedImage = imageSrc;
      this.showImageModal = true;
      document.body.style.overflow = 'hidden';
    },

    closeModal() {
      this.showImageModal = false;
      this.selectedImage = null;
      document.body.style.overflow = 'auto';
    },

    openMapModal(mapLink) {
      this.selectedMapLink = mapLink;
      this.showMapModal = true;
      document.body.style.overflow = 'hidden';
    },
    closeMapModal() {
      this.showMapModal = false;
      this.selectedMapLink = null;
      document.body.style.overflow = 'auto';
    }
  },
  created() {
    this.fetchRooms();
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

.room-card {
  background: var(--sds-size-stroke-border) solid #BEBEBE;
  border-radius: 8px;
  box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
  margin-bottom: 30px;
  padding: 20px 15px;
  width: 62%;
  margin-left: 0;
}

.room-grid {
  display: grid;
  grid-template-columns: 300px 300px;
  gap: 20px;
  align-items: start;
  justify-content: start;
}

.room-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
  padding: 0;
}

.room-image-container {
  width: 400px;
  height: 500px;
  overflow: hidden;
  background: #f5f5f5;
  border-radius: 8px;
}

.room-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.room-title {
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
  margin-left: 95px;
  padding: 15px;
  width: 300px;
}

.booking-info {
  width: 100%;
}

.booking-title {
  font-size: 18px;
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
  font-size: 16px;
  font-weight: 600;
  color: #000000;
  margin-bottom: 5px;
}

.info-text {
  color: #000000;
  line-height: 1.4;
  font-size: 14px;
  margin: 0;
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

.map-link {
  color: #0066cc;
  text-decoration: none;
}

.map-link:hover {
  text-decoration: underline;
}

.no-rooms {
  text-align: center;
  padding: 2rem;
  color: #666;
  font-size: 1.1rem;
}

.loading, .error {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error {
  color: #dc2626;
}

.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  cursor: pointer;
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
  cursor: default;
}

.modal-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.close-button {
  position: absolute;
  top: -40px;
  right: -40px;
  background: none;
  border: none;
  color: white;
  font-size: 36px;
  cursor: pointer;
  padding: 10px;
  transition: transform 0.2s;
}

.close-button:hover {
  transform: scale(1.1);
  color: #ddd;
}

/* Add hover effect for room images */
.room-image:hover {
  opacity: 0.9;
  transform: scale(1.02);
  transition: all 0.3s ease;
}

/* Adjust room image container */
.room-image-container {
  cursor: pointer;
  transition: transform 0.3s ease;
}

.room-image-container:hover {
  transform: scale(1.02);
}

.map-button {
  background: #000000;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  margin: 10px auto;
  transition: background 0.3s;
  display: block;
}

.map-button:hover {
  background: #3a3a3a;
}

.map-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.map-modal-content {
  position: relative;
  width: 90%;
  max-width: 800px;
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.map-modal-content iframe {
  border-radius: 4px;
  width: 100%;
  height: 450px;
}

.map-modal .close-button {
  position: absolute;
  top: -40px;
  right: -40px;
  background: none;
  border: none;
  color: white;
  font-size: 36px;
  cursor: pointer;
  padding: 10px;
  transition: transform 0.2s;
}

.map-modal .close-button:hover {
  transform: scale(1.1);
  color: #ddd;
}
</style> 