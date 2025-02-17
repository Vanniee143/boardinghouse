<template>
  <div class="landlord-panel">
    <div class="navbars">
      <router-link to="/landlord/dashboard" class="logo-link">
        <img src="@/assets/images/image.png" alt="logo" class="logos">
      </router-link>
      <router-link to="/landlord/dashboard" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>

      <div class="profile-section">
        <div class="landlord-profile">
          <img 
            :src="getProfilePicture(landlordProfile.profile_picture)"
            alt="profile" 
            class="profile-icon"
            @error="handleImageError"
            @load="handleImageLoad"
          />
          <span class="landlord-profile-name">{{ landlordName }}</span>
        </div>
      </div>

      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateMenu">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/landlord/profile">Profile Settings</option>
          <option value="/landlord/login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="header-section">
        <h4 class="helper">BOARDING HOUSES</h4>
        <button class="add-house-btn" @click="showAddHouseForm">
          <img src="@/assets/images/Plus.png" alt="add" class="add-icon white-icon">
          Add New Boarding House
        </button>
      </div>

      <div class="houses-container">
        <div v-if="loading" class="loading">
          Loading boarding houses...
        </div>

        <div v-else-if="error" class="error">
          {{ error }}
        </div>

        <div v-else-if="!boardingHouses.length" class="no-houses">
          <p>No boarding houses available</p>
          <p>Click "Add New Boarding House" to add your first property</p>
        </div>

        <div v-else class="houses-grid">
          <div v-for="house in boardingHouses" :key="house.id" class="house-card">
            <div class="house-image" @click="openImageModal(house)">
              <img 
                :src="getBoardingHouseImage(house.bh_images)" 
                :alt="house.name"
                @error="handleImageError"
                class="bh-image"
              />
            </div>

            <h3 class="house-name">{{ house.name }}</h3>
            
            <div class="room-stats">
              <div class="info-row">
                <span class="label">Total Rooms:</span>
                <span class="value">{{ house.rooms }}</span>
              </div>
              <div class="info-row">
                <span class="label">Available:</span>
                <span class="value available">{{ house.available }}</span>
              </div>
              <div class="info-row">
                <span class="label">Occupied:</span>
                <span class="value occupied">{{ house.occupied }}</span>
              </div>
            </div>

            <div class="info-row">
              <span class="label">Address:</span>
              <span class="value">{{ house.address }}</span>
            </div>
            <div class="info-row">
              <span class="label">Landlord:</span>
              <span class="value">{{ house.landlord_name }}</span>
            </div>
            <div class="info-row">
              <span class="label">Phone:</span>
              <span class="value">{{ house.landlord_phone }}</span>
            </div>

            <div class="house-actions">
              <button class="action-btn edit" @click="editHouse(house)">
                <img src="@/assets/images/Edit.png" alt="edit" class="action-icon">
                Edit
              </button>
              <button class="action-btn delete" @click="confirmDelete(house)">
                <img src="@/assets/images/trash.png" alt="delete" class="action-icon">
                Delete
              </button>
              <button class="action-btn view" @click="viewDetails(house)">
                <img src="@/assets/images/eye.png" alt="view" class="action-icon">
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Update the details modal section -->
    <div v-if="selectedHouse" class="details-modal" @click="closeDetails">
      <div class="modal-content details-modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedHouse.name }}</h2>
          <button class="close-button" @click="closeDetails">&times;</button>
        </div>

        <div class="modal-body">
          <div class="details-info">
            <div class="detail-section">
              <h3>Basic Information</h3>
              <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span class="detail-value">{{ selectedHouse.description || 'No description available' }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">{{ selectedHouse.address }}</span>
              </div>
            </div>

            <div class="detail-section">
              <h3>Room Statistics</h3>
              <div class="detail-row">
                <span class="detail-label">Total Rooms:</span>
                <span class="detail-value">{{ selectedHouse.rooms }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Available:</span>
                <span class="detail-value available">{{ selectedHouse.available }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Occupied:</span>
                <span class="detail-value occupied">{{ selectedHouse.occupied }}</span>
              </div>
            </div>

            <div class="detail-section">
              <h3>Contact Information</h3>
              <div class="detail-row">
                <span class="detail-label">Landlord:</span>
                <span class="detail-value">{{ selectedHouse.landlord_name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value">{{ selectedHouse.landlord_phone }}</span>
              </div>
            </div>

            <div class="detail-section">
              <h3>Record Information</h3>
              <div class="detail-row">
                <span class="detail-label">Created:</span>
                <span class="detail-value">{{ formatDate(selectedHouse.created_at) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Created By:</span>
                <span class="detail-value">
                  {{ selectedHouse.created_by_name || 'System' }}
                  <span v-if="selectedHouse.created_by_type" class="user-type">({{ selectedHouse.created_by_type }})</span>
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Last Updated:</span>
                <span class="detail-value">{{ formatDate(selectedHouse.updated_at) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Updated By:</span>
                <span class="detail-value">
                  {{ selectedHouse.updated_by_name || 'System' }}
                  <span v-if="selectedHouse.updated_by_type" class="user-type">({{ selectedHouse.updated_by_type }})</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="edit-btn" @click="editHouse(selectedHouse)">Edit</button>
          <button class="close-btn" @click="closeDetails">Close</button>
        </div>
      </div>
    </div>

    <!-- Update the image modal section -->
    <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
        <div class="modal-content" @click.stop>
            <button class="close-button" @click="closeImageModal">&times;</button>
            <div class="image-container">
                <img 
                    :src="getBoardingHouseImage(selectedImage)" 
                    :alt="selectedHouse?.name"
                    class="modal-image"
                />
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';
import defaultHouseImage from '@/assets/images/default-house.png';

export default {
  name: 'LandlordBoardingHouses',
  data() {
    return {
      landlordName: localStorage.getItem('userName') || 'Landlord',
      landlordProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
        email: localStorage.getItem('userEmail'),
        phone_number: localStorage.getItem('userPhone'),
        gender: localStorage.getItem('userGender'),
        birthdate: localStorage.getItem('userBirthdate')
      },
      defaultProfilePic,
      defaultImage: defaultHouseImage,
      boardingHouses: [],
      rooms: [],
      loading: true,
      error: null,
      selectedHouse: null,
      showImageModal: false,
      selectedImage: null,
      zoomLevel: 1,
      minZoom: 0.5,
      maxZoom: 3,
      zoomStep: 0.2
    };
  },
  methods: {
    getProfilePicture(profilePicture) {
      if (profilePicture && profilePicture.trim() !== '') {
        return profilePicture;
      }
      return this.defaultProfilePic;
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    handleImageLoad() {
      console.log('Image loaded successfully');
    },

    async fetchLandlordProfile() {
      try {
        const userId = this.landlordProfile.user_id;
        const response = await axios.get(`/landlord/fetch-profile/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.landlordName = userData.name;
          this.landlordProfile.profile_picture = userData.profile_picture;
          
          // Update localStorage
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching landlord profile:', error);
      }
    },

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/landlord/login') {
        this.logout();
      } else {
        this.$router.push(route);
      }
    },
    async logout() {
      try {
        await axios.post('/landlord/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        localStorage.removeItem('token');
        localStorage.removeItem('userName');
        localStorage.removeItem('userId');
        localStorage.removeItem('userType');
        localStorage.removeItem('profilePicture');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userPhone');
        localStorage.removeItem('userGender');
        localStorage.removeItem('userBirthdate');
        delete axios.defaults.headers.common['Authorization'];
        this.$router.push('/landlord/login');
      }
    },
    async fetchBoardingHouses() {
      try {
        const userId = localStorage.getItem('userId');
        console.log('Fetching boarding houses for landlord:', userId);
        
        const response = await axios.get('/landlord/get-boarding-houses', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data;
          console.log('Processed boarding houses:', this.boardingHouses);
        } else {
          throw new Error(response.data.message || 'Failed to fetch boarding houses');
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        if (error.response?.status === 403) {
          alert('You are not authorized to view these boarding houses');
          this.$router.push('/landlord/dashboard');
        } else {
          this.error = error.response?.data?.message || 'Failed to load boarding houses';
        }
      } finally {
        this.loading = false;
      }
    },
    showAddHouseForm() {
      this.$router.push('/landlord/add-boarding-house');
    },
    editHouse(house) {
      this.$router.push({
        name: 'landlord.boarding-houses.edit',
        params: { id: house.id }
      });
    },
    viewDetails(house) {
      this.selectedHouse = house;
      document.body.style.overflow = 'hidden';
    },
    closeDetails() {
      this.selectedHouse = null;
      document.body.style.overflow = 'auto';
    },
    formatDate(date) {
      return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    },
    editRoom(room) {
      this.$router.push(`/landlord/rooms/${room.id}/edit`);
    },
    viewRoomDetails(room) {
      this.$router.push(`/landlord/rooms/${room.id}`);
    },
    getBoardingHouseImage(image) {
      if (image) {
        return `/storage/${image}`;
      }
      return this.defaultImage;
    },
    openImageModal(house) {
      this.selectedHouse = house;
      this.selectedImage = house.bh_images;
      this.showImageModal = true;
      document.body.style.overflow = 'hidden';
    },
    closeImageModal() {
      this.showImageModal = false;
      this.selectedImage = null;
      this.selectedHouse = null;
      document.body.style.overflow = 'auto';
    },
    async confirmDelete(house) {
      if (confirm(`Are you sure you want to delete "${house.name}"? This action cannot be undone.`)) {
        try {
          const response = await axios.delete(`/landlord/delete-boarding-house/${house.id}`);
          if (response.data.status === 'success') {
            alert('Boarding house deleted successfully');
            // Remove from local array
            this.boardingHouses = this.boardingHouses.filter(h => h.id !== house.id);
          }
        } catch (error) {
          console.error('Error deleting boarding house:', error);
          alert(error.response?.data?.message || 'Failed to delete boarding house');
        }
      }
    }
  },
  async created() {
    try {
      await this.fetchLandlordProfile();
      await this.fetchBoardingHouses();
    } catch (error) {
      console.error('Error in created hook:', error);
    }
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

.profile-section {
  margin-left: auto;
  margin-right: 2rem;
  display: flex;
  align-items: center;
}

.landlord-profile {
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

.landlord-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0;
}

.button-group {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1rem;
}

.add-house-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: black;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 1rem;
}

.add-house-btn:hover {
  background: #3d3d3d;
}

.add-icon {
  width: 20px;
  height: 20px;
}

.houses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  padding: 1rem;
}

.house-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.house-card:hover {
  transform: translateY(-5px);
}

.house-details {
  padding: 1.5rem;
}

.house-name {
  color: #794646;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.house-address {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.house-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-label {
  color: #666;
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.stat-value {
  color: #794646;
  font-weight: 600;
  font-size: 1.25rem;
}

.house-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid #eee;
}

.action-btn {
  font-size: 0.9rem;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.action-btn.edit {
  background: #f3f4f6;
  color: #374151;
}

.action-btn.view {
  background: #794646;
  color: white;
}

.action-btn.delete {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-btn.edit:hover {
  background: #e5e7eb;
}

.action-btn.view:hover {
  background: #693c3c;
}

.action-btn.delete:hover {
  background: #fecaca;
}

.action-icon {
  width: 16px;
  height: 16px;
  object-fit: contain;
}

.no-houses {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  margin-top: 2rem;
}

.no-houses p {
  color: #666;
  margin: 0.5rem 0;
}

.no-houses p:first-child {
  font-size: 1.25rem;
  color: #794646;
  font-weight: 600;
}

@media (max-width: 768px) {
  .houses-grid {
    grid-template-columns: 1fr;
  }

  .house-actions {
    grid-template-columns: 1fr;
    gap: 0.75rem;
    padding: 1rem;
  }
  
  .action-btn {
    width: 100%;
    padding: 0.875rem;
  }
}

.section-header {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

.section-header .helper {
  margin: 0;
  grid-column: 2;
  text-align: center;
}

.section-header .add-house-btn {
  grid-column: 1;
  justify-self: start;
}

.spacer {
  grid-column: 3;
}

.rooms-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #ddd;
  text-align: center;
}

.rooms-section .helper {
  margin-bottom: 1rem;
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.room-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.room-card:hover {
  transform: translateY(-5px);
}

.room-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.room-details {
  padding: 1.5rem;
}

.room-name {
  color: #794646;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.room-info {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.room-price {
  color: #794646;
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.no-rooms {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  margin-top: 1rem;
}

.no-rooms p {
  color: #666;
  margin: 0.5rem 0;
}

.no-rooms p:first-child {
  font-size: 1.25rem;
  color: #794646;
  font-weight: 600;
}

.button-container {
  text-align: left;
  margin-top: 1rem;
}

.add-house-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: black;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.white-icon {
  filter: brightness(0) invert(1); /* Makes the icon white */
}

.overall {
  padding: 2rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px);
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 0 1rem;
}

.helper {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.add-house-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: black;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-house-btn:hover {
  background: #333;
  transform: translateY(-2px);
}

.white-icon {
  filter: brightness(0) invert(1);
  width: 20px;
  height: 20px;
}

.houses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  padding: 1rem;
}

.house-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.house-card:hover {
  transform: translateY(-5px);
}

.house-image {
  width: 100%;
  height: 200px;
  overflow: hidden;
  cursor: pointer;
}

.bh-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.house-image:hover .bh-image {
  transform: scale(1.05);
}

.house-details {
  padding: 1.5rem;
}

.house-name {
  color: #794646;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.room-stats {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.8rem;
  background: white;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.info-row:last-child {
  margin-bottom: 0;
}

.label {
  color: #794646;
  font-weight: 600;
}

.value {
  color: #374151;
  font-weight: 500;
}

.value.available {
  color: #10b981;
  font-weight: 600;
}

.value.occupied {
  color: #ef4444;
  font-weight: 600;
}

.house-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn.edit {
  background: #f3f4f6;
  color: #374151;
}

.action-btn.view {
  background: #794646;
  color: white;
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-icon {
  width: 16px;
  height: 16px;
}

.loading, .no-houses {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  margin: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .houses-grid {
    grid-template-columns: 1fr;
  }

  .house-card {
    margin: 0 1rem;
  }

  .action-btn {
    padding: 0.5rem;
    font-size: 0.9rem;
  }
}

.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.image-container {
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-image {
  max-width: 100%;
  max-height: 80vh;
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

.user-type {
  font-size: 0.85em;
  color: #794646;
  margin-left: 0.5rem;
  font-style: italic;
}

.details-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.details-modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  color: #794646;
  margin: 0;
}

.modal-body {
  padding: 1.5rem;
}

.detail-section {
  margin-bottom: 2rem;
}

.detail-section h3 {
  color: #794646;
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  margin-bottom: 0.75rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.detail-label {
  width: 120px;
  color: #666;
  font-weight: 600;
}

.detail-value {
  flex: 1;
  color: #333;
}

.detail-value.available {
  color: #10b981;
  font-weight: 600;
}

.detail-value.occupied {
  color: #ef4444;
  font-weight: 600;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.edit-btn, .close-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.edit-btn {
  background: #794646;
  color: white;
}

.close-btn {
  background: #f3f4f6;
  color: #374151;
}

.edit-btn:hover {
  background: #693c3c;
}

.close-btn:hover {
  background: #e5e7eb;
}

.user-type {
  font-size: 0.85em;
  color: #794646;
  margin-left: 0.5rem;
  font-style: italic;
}

.action-btn.delete {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn.delete:hover {
  background: #fecaca;
  transform: translateY(-2px);
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