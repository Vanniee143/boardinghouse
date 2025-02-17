<template>
  <div class="admin-rooms">
    <div class="navbars">
      <router-link to="/admin">
        <img src="@/assets/images/image.png" alt="logo" class="logos">
      </router-link>
      <router-link to="/admin" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>

      <div class="profile-section">
        <div class="admin-profile">
          <img 
            :src="getProfilePicture(adminProfile.profile_picture)"
            alt="profile" 
            class="profile-icon"
            @error="handleImageError"
            @load="handleImageLoad"
          />
          <span class="admin-profile-name">{{ adminName }}</span>
        </div>
      </div>

      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateMenu">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/admin/accounts">Accounts</option>
          <option value="/admin/profile">Profile Settings</option>
          <option value="/admin/admin_login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Rooms</h2>
          <router-link to="/admin/rooms/add" class="add-btn">
            + Add Room
          </router-link>
        </div>

        <div v-if="loading" class="loading">
          Loading rooms...
        </div>

        <div v-else-if="error" class="error">
          {{ error }}
        </div>

        <div v-else-if="!rooms.length" class="empty-message">
          No rooms available
        </div>

        <div v-else class="rooms-grid">
          <div class="room-card" v-for="room in rooms" :key="room.room_id">
            <div class="room-image" @click="openImageModal(room.room_images)">
              <img 
                :src="getRoomImage(room.room_images)" 
                :alt="room.room_name"
                @error="handleRoomImageError"
              />
            </div>

            <div class="room-details">
              <h3 class="room-name">{{ room.room_name }}</h3>

              <div class="room-info">
                <div class="info-row">
                  <span class="info-label">Beds:</span>
                  <span class="info-value">{{ room.bed_quantity }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Status:</span>
                  <span class="info-value" :class="room.status">{{ room.status }}</span>
                </div>

                <div class="info-row">
                  <span class="info-label">Price:</span>
                  <span class="info-value">₱{{ formatPrice(room.price) }}</span>
                </div>
              </div>

              <div class="room-actions">
                <button class="action-btn edit" @click="editRoom(room)">
                  <img src="@/assets/images/Edit.png" alt="edit" class="action-icon">
                  Edit
                </button>

                <button class="action-btn delete" @click="deleteRoom(room.room_id)">
                  <img src="@/assets/images/trash.png" alt="delete" class="action-icon">
                  Delete
                </button>

                <button class="action-btn view" @click="viewRoomDetails(room)">
                  <img src="@/assets/images/eye.png" alt="view" class="action-icon">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
      <div class="modal-content">
        <img :src="selectedImage" alt="Zoomed image" class="zoomed-image">
        <button class="close-button" @click="closeImageModal">&times;</button>
      </div>
    </div>

    <div v-if="showMapModal" class="map-modal" @click="closeMapModal">
      <div class="modal-content map-modal-content">
        <iframe 
          :src="getEmbedMapUrl(selectedMap)"
          width="100%" 
          height="450" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <button class="close-button" @click="closeMapModal">&times;</button>
      </div>
    </div>

    <div 
      class="zoom-overlay" 
      :class="{ 'active': isZoomed }"
      @click="closeZoom"
    >
      <img 
        v-if="selectedImage"
        :src="selectedImage" 
        alt="Zoomed image" 
        class="zoomed-image"
        @click.stop
      />
    </div>

    <!-- Room Details Modal -->
    <div v-if="selectedRoom && showDetailsModal" class="details-modal" @click="closeDetailsModal">
      <div class="modal-content details-modal-content" @click.stop>
        <div class="room-details-container">
          <h2>{{ selectedRoom.room_name }}</h2>

          <div class="details-info">
            <div class="info-group">
              <label>Boarding House:</label>
              <span>{{ selectedRoom.boarding_house_name }}</span>
            </div>

            <div class="info-group">
              <label>Bed Quantity:</label>
              <span>{{ selectedRoom.bed_quantity }}</span>
            </div>

            <div class="info-group">
              <label>Price:</label>
              <span>₱{{ formatPrice(selectedRoom.price) }}</span>
            </div>

            <div class="info-group">
              <label>Status:</label>
              <span :class="selectedRoom.status">{{ selectedRoom.status }}</span>
            </div>

            <div class="map-group" v-if="selectedRoom.map_link">
              <label>Location:</label>
              <div class="map-container">
                <div v-html="sanitizedMapLink(selectedRoom.map_link)"></div>
              </div>
            </div>

            <div class="detail-section">
              <h3>Creation Information</h3>
              <div class="detail-row">
                <span class="detail-label">Created By:</span>
                <span class="detail-value">
                  {{ selectedRoom.created_by_name || "System" }}
                  <span v-if="selectedRoom.created_by_type" class="user-type">
                    ({{ selectedRoom.created_by_type }})
                  </span>
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Created At:</span>
                <span class="detail-value">{{ formatDate(selectedRoom.created_at) }}</span>
              </div>
            </div>

            <div class="detail-section">
              <h3>Last Update Information</h3>
              <div class="detail-row">
                <span class="detail-label">Updated By:</span>
                <span class="detail-value">
                  {{ selectedRoom.updated_by_name || "System" }}
                  <span v-if="selectedRoom.updated_by_type" class="user-type">
                    ({{ selectedRoom.updated_by_type }})
                  </span>
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Updated At:</span>
                <span class="detail-value">{{ formatDate(selectedRoom.updated_at) }}</span>
              </div>
            </div>
          </div>

          <button class="close-details-btn" @click="closeDetailsModal">&times;</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import defaultProfilePic from '@/assets/images/Profile.png';
import axios from 'axios';
import defaultRoomImage from '@/assets/images/room1.png';
import { EventBus } from '@/eventBus';

export default {
  name: 'AdminRooms',
  data() {
    return {
      rooms: [],
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
        email: localStorage.getItem('userEmail'),
        phone_number: localStorage.getItem('userPhone'),
        gender: localStorage.getItem('userGender'),
        birthdate: localStorage.getItem('userBirthdate')
      },
      defaultProfilePic,
      showImageModal: false,
      selectedImage: null,
      loading: false,
      error: null,
      boardingHouses: [],
      showMapModal: false,
      selectedMap: null,
      defaultRoomImage,
      isZoomed: false,
      selectedRoom: null,
      showDetailsModal: false,
    }
  },
  methods: {
    ...mapActions({
      deleteRoomAction: 'admin/deleteRoom',
      fetchRoomsAction: 'admin/fetchRooms'
    }),

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.logout();
      } else if (route !== '#') {
        this.$router.push(route);
      }
    },

    async logout() {
      try {
        // Call the logout endpoint
        await axios.post('/admin/logout');
        
        // Clear all localStorage items
        localStorage.removeItem('token');
        localStorage.removeItem('userName');
        localStorage.removeItem('userId');
        localStorage.removeItem('userType');
        localStorage.removeItem('profilePicture');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userPhone');
        localStorage.removeItem('userGender');
        localStorage.removeItem('userBirthdate');
        
        // Remove Authorization header
        delete axios.defaults.headers.common['Authorization'];
        
        // Redirect to login page
        this.$router.push('/admin/admin_login');
      } catch (error) {
        console.error('Logout error:', error);
        // Still redirect even if the server request fails
        this.$router.push('/admin/admin_login');
      }
    },

    editRoom(room) {
      this.$router.push({
        path: `/admin/edit-room/${room.room_id}`
      });
    },

    async deleteRoom(id) {
      if (confirm('Are you sure you want to delete this room?')) {
        try {
          const response = await axios.delete(`/admin/delete-room/${id}`, {
            headers: {
              'X-User-Id': localStorage.getItem('userId')
            }
          });

          if (response.data.status === 'success') {
            alert('Room deleted successfully');
            // Remove the room from the local array
            this.rooms = this.rooms.filter(room => room.room_id !== id);
          } else {
            throw new Error(response.data.message || 'Failed to delete room');
          }
        } catch (error) {
          console.error('Error deleting room:', error);
          alert(error.response?.data?.message || 'Failed to delete room');
        }
      }
    },

    async fetchRooms() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/fetch-rooms');
        if (response.data.status === 'success') {
          this.rooms = response.data.data;
          await this.fetchBoardingHouses();
        } else {
          throw new Error(response.data.message || 'Failed to fetch rooms');
        }
      } catch (error) {
        console.error('Error fetching rooms:', error);
        this.error = error.response?.data?.message || 'Failed to load rooms';
        this.rooms = [];
      } finally {
        this.loading = false;
      }
    },

    async fetchBoardingHouses() {
      try {
        const response = await axios.get('/admin/fetch-boarding-houses');
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
      }
    },

    getProfilePicture(profilePicture) {
      console.log('Getting profile picture:', profilePicture);
      console.log('Profile from localStorage:', localStorage.getItem('profilePicture'));
      
      if (profilePicture && profilePicture.trim() !== '') {
        console.log('Using provided profile picture:', profilePicture);
        return profilePicture;
      }
      
      console.log('Using default picture:', this.defaultProfilePic);
      return this.defaultProfilePic;
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    handleImageLoad() {
      console.log('Image loaded successfully');
    },

    formatDate(date) {
      return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true
      });
    },

    getBoardingHouseName(boardingHouseId) {
      const house = this.boardingHouses.find(h => h.boarding_house_id === boardingHouseId);
      return house ? house.name : 'Unknown';
    },

    handleRoomImageError(e) {
      console.log('Image load error, using default image');
      e.target.src = this.defaultRoomImage;
    },

    toggleZoom(imageUrl) {
      if (!imageUrl || imageUrl === this.defaultRoomImage) {
        return;
      }
      this.selectedImage = imageUrl;
      this.isZoomed = !this.isZoomed;
      document.body.style.overflow = this.isZoomed ? 'hidden' : 'auto';
    },

    closeZoom() {
      this.isZoomed = false;
      this.selectedImage = null;
      document.body.style.overflow = 'auto';
    },

    openImageModal(imageUrl) {
      if (!imageUrl || imageUrl === this.defaultRoomImage) {
        return;
      }
      this.selectedImage = this.getRoomImage(imageUrl);
      this.showImageModal = true;
      document.body.style.overflow = 'hidden';
    },

    closeImageModal() {
      this.showImageModal = false;
      this.selectedImage = null;
      document.body.style.overflow = 'auto';
    },

    openMapModal(mapLink) {
      this.selectedMap = mapLink;
      this.showMapModal = true;
      document.body.style.overflow = 'hidden';
    },

    closeMapModal() {
      this.showMapModal = false;
      this.selectedMap = null;
      document.body.style.overflow = 'auto';
    },

    getEmbedMapUrl(url) {
      if (!url) return "";
      
      try {
        // If it's already an iframe code, extract the src
        if (url.includes('<iframe')) {
          const srcMatch = url.match(/src="([^"]+)"/);
          if (srcMatch && srcMatch[1]) {
            return srcMatch[1];
          }
        }
        
        // If it's just the URL, return it directly
        return url;
      } catch (error) {
        console.error('Error processing map URL:', error);
        return "";
      }
    },

    formatPrice(price) {
      return Number(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },

    getRoomImage(image) {
      if (image) {
        if (image.startsWith('http')) {
          return image;
        }
        if (image.startsWith('/storage/')) {
          return image;
        }
        return `/storage/${image}`;
      }
      return this.defaultRoomImage;
    },

    async viewRoomDetails(room) {
      try {
        // First set the selected room directly from the passed room data
        this.selectedRoom = room;
        
        // Then show the modal
        this.showDetailsModal = true;
        document.body.style.overflow = 'hidden';

        // Optionally fetch additional details if needed
        const response = await axios.get(`/admin/get-room/${room.room_id}`);
        if (response.data.status === 'success') {
          // Update with full details
          this.selectedRoom = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching room details:', error);
        alert('Failed to load room details');
        // Reset in case of error
        this.selectedRoom = null;
        this.showDetailsModal = false;
        document.body.style.overflow = 'auto';
      }
    },

    closeDetailsModal() {
      this.selectedRoom = null;
      this.showDetailsModal = false;
      document.body.style.overflow = 'auto';
    },

    sanitizedMapLink(url) {
      if (!url) return '';
      // If it's already an iframe code, return it as is
      if (url.includes('<iframe')) {
        return url;
      }
      // If it's a URL, wrap it in an iframe
      return `<iframe src="${url}" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>`;
    },

    handleRoomStatusUpdate(updatedRoomIds) {
      // Update status of affected rooms
      this.rooms = this.rooms.map(room => {
        if (updatedRoomIds.includes(room.room_id)) {
          return {
            ...room,
            status: 'available'
          };
        }
        return room;
      });
    }
  },
  created() {
    this.fetchRooms();
    
    // Listen for room status updates using emitter
    EventBus.on('rooms-status-updated', this.handleRoomStatusUpdate);
  },
  beforeUnmount() {
    // Clean up event listener
    EventBus.off('rooms-status-updated', this.handleRoomStatusUpdate);
  }
};
</script>

<style scoped>
@import '@/assets/css/admin_rooms.css';

.section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  padding: 1rem;
}

.room-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
  display: flex;
  flex-direction: column;
  min-height: 450px;
  border: 1px solid #eee;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}

.section-title {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

/* Responsive adjustments */
@media (max-width: 1400px) {
  .section {
    margin: 1rem;
  }
  
  .rooms-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  }
}

@media (max-width: 768px) {
  .section {
    padding: 1rem;
  }

  .rooms-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}

.add-btn {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: #000000;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-weight: 600;
  font-family: Arial;
}

.add-btn:hover {
  background: #323232;
}

.contains,
.containss {
  margin-bottom: 2rem;
}

.empty-message {
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
  text-align: center;
  color: #666;
  font-style: italic;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.profile-section {
  margin-left: auto;
  margin-right: 2rem;
  display: flex;
  align-items: center;
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.profile-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0;
}

.house-info {
  padding: 1rem;
  width: 101.5%;
  background: #f8f9fa;
  border-radius: 8px;
  margin: 1rem 0;
}

.info-row {
  display: flex;
  padding: 0.8rem;
  border-bottom: 1px solid #eee;
  background: white;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.info-row:last-child {
  margin-bottom: 0;
  border-bottom: none;
}

.info-label {
  font-weight: 600;
  color: #794646;
  width: 100px;
  flex-shrink: 0;
}

.info-value {
  color: #374151;
  flex-grow: 1;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.containss {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 1.5rem;
  padding-right: 5rem;
  margin-bottom: 1rem;
  position: relative;
  transition: box-shadow 0.3s ease;
  width: 90%;
}

.containss:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.action-icons {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  display: flex;
  gap: 1rem;
  z-index: 10;
}

.Edit,
.Trash {
  transform: translate(15px, 40px);
  width: 24px;
  height: 24px;
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.Edit:hover,
.Trash:hover {
  transform: translate(15px, 40px);
  opacity: 1;
}

.house-header {
  transform: translate(70px, 0px);
  padding-right: 4rem;
}

.sets {
  font-size: 1.4rem;
  color: #794646;
  font-weight: bold;
  margin-bottom: 1rem;
}

.room-info {
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  margin: 1rem 0;
}

.room-image-container {
  position: relative;
  width: 100%;
  max-width: 300px;
  height: 200px;
  border-radius: 8px;
  overflow: hidden;
  background-color: #f5f5f5;
  cursor: pointer;
}

.room-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.room-image:hover {
  transform: scale(1.05);
}

.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.zoomed-image {
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
  opacity: 0.8;
}

.loading,
.error {
  text-align: center;
  padding: 2rem;
  margin: 1rem 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.loading {
  color: #666;
}

.error {
  color: #dc3545;
  background-color: #fff5f5;
}

.rooms-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem 0;
}

.view-map-btn {
  background: #794646;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: background-color 0.2s;
}

.view-map-btn:hover {
  background: #693c3c;
}

.map-modal-content {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  width: 80%;
  max-width: 1000px;
}

.map-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
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
  opacity: 0.8;
}

.zoom-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.zoom-overlay.active {
  opacity: 1;
  visibility: visible;
}

.zoomed-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  transform: scale(1);
  transition: transform 0.3s ease;
}

.zoom-overlay.active .zoomed-image {
  transform: scale(1);
}

.user-type {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85em;
  font-weight: 600;
  margin-left: 0.5rem;
}

.user-type.admin {
  background-color: #fecaca;
  color: #dc2626;
}

.user-type.landlord {
  background-color: #bfdbfe;
  color: #2563eb;
}

.user-type.user {
  background-color: #d1fae5;
  color: #059669;
}

.boarding-house-link {
  color: #794646;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.boarding-house-link:hover {
  color: #693c3c;
  text-decoration: underline;
}

.info-row {
  display: flex;
  padding: 0.8rem;
  border-bottom: 1px solid #eee;
  background: white;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.info-label {
  font-weight: 600;
  color: #794646;
  width: 120px;
  flex-shrink: 0;
}

.info-value {
  color: #374151;
  flex-grow: 1;
}

.info-value.price {
  color: #10b981;
  font-weight: 600;
}

@media (max-width: 768px) {
  .room-image-container {
    height: 180px;
  }
  
  .close-button {
    top: 10px;
    right: 10px;
  }
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 2rem;
  padding: 1rem;
  max-width: 1400px;
  margin: 0 auto;
}

.room-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
  display: flex;
  flex-direction: column;
  min-height: 450px;
}

.room-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.room-image {
  width: 100%;
  height: 250px;
  overflow: hidden;
  cursor: pointer;
}

.room-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.room-image:hover img {
  transform: scale(1.05);
}

.room-details {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.room-name {
  color: #794646;
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

.room-info {
  flex: 1;
  margin-bottom: 1.5rem;
}

.room-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  padding: 1rem;
  border-top: 1px solid #eee;
  background: white;
  width: 100%;
  margin-top: auto;
  transform: translate(-13px);
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 0.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 40px;
  width: 100%;
  white-space: nowrap;
}

.action-btn .action-icon {
  width: 16px;
  height: 16px;
  min-width: 16px;
  object-fit: contain;
}

.action-btn.edit {
  background: #f3f4f6;
  color: #374151;
}

.action-btn.edit:hover {
  background: #e5e7eb;
}

.action-btn.view {
  background: #794646;
  color: white;
}

.action-btn.view:hover {
  background: #693c3c;
}

.action-btn.delete {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn.delete:hover {
  background: #fecaca;
}

/* Responsive adjustments */
@media (max-width: 1400px) {
  .rooms-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  }
}

@media (max-width: 768px) {
  .rooms-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .room-card {
    min-height: 400px;
  }

  .room-image {
    height: 200px;
  }

  .room-actions {
    padding: 0.75rem;
    gap: 0.5rem;
  }

  .action-btn {
    height: 36px;
    font-size: 0.8rem;
    padding: 0.5rem;
  }

  .action-btn .action-icon {
    width: 14px;
    height: 14px;
    min-width: 14px;
  }
}

.details-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.details-modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  position: relative;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  max-height: 90vh;
  overflow-y: auto;
}

.room-details-container {
  position: relative;
  padding: 1rem;
}

.details-info {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.info-group {
  background: #f8f9fa;
  padding: 1.25rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.detail-section {
  background: white;
  border: 1px solid #eee;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

/* Add scrollbar styling */
.details-modal-content::-webkit-scrollbar {
  width: 8px;
}

.details-modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.details-modal-content::-webkit-scrollbar-thumb {
  background: #794646;
  border-radius: 4px;
}

.details-modal-content::-webkit-scrollbar-thumb:hover {
  background: #693c3c;
}

.room-details-container h2 {
  color: #794646;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 2rem;
}

.details-info {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.info-group {
  background: #f8f9fa;
  padding: 1.25rem;
  border-radius: 8px;
}

.info-group label {
  display: block;
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.info-group span {
  color: #333;
  font-size: 1.1rem;
}

.detail-section {
  background: white;
  border: 1px solid #eee;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.detail-section h3 {
  color: #794646;
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f0f0f0;
}

.detail-row {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 0.75rem;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.detail-label {
  width: 140px;
  color: #666;
  font-weight: 600;
}

.detail-value {
  flex: 1;
  color: #333;
}

.map-container {
  width: 100%;
  height: 300px;
  border-radius: 8px;
  overflow: hidden;
  margin-top: 0.5rem;
  border: 1px solid #eee;
}

.map-container iframe {
  width: 100%;
  height: 100%;
  border: 0;
}

.close-details-btn {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: none;
  border: none;
  font-size: 28px;
  color: #666;
  cursor: pointer;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.close-details-btn:hover {
  background: #f3f4f6;
  color: #333;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .details-modal {
    padding: 1rem;
  }

  .details-modal-content {
    padding: 1.5rem;
    margin: 1rem;
  }

  .room-details-container h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .detail-label {
    width: 100%;
  }

  .close-details-btn {
    top: 1rem;
    right: 1rem;
  }
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