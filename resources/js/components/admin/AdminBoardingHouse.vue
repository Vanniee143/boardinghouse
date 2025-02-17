<template>
  <div class="admin-panel">
    <div class="navbars">
      <router-link to="/admin" class="logo-link">
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
      <div class="boarding-houses-container">
        <h4 class="helper">BOARDING HOUSES</h4>

        <button class="add-house-btn" @click="showAddHouseForm">
          <img src="@/assets/images/Plus.png" alt="add" class="add-icon white-icon">
          Add New Boarding House
        </button>

        <div class="houses-list">
          <div v-if="loading" class="loading-state">
            Loading boarding houses...
          </div>

          <div v-else-if="boardingHouses.length === 0" class="empty-state">
            No boarding houses found
          </div>

          <div v-else class="houses-grid">
            <div v-for="house in boardingHouses" :key="house.boarding_house_id" class="house-card">
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
                  <span class="value">{{ house.rooms_count || 0 }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Available:</span>
                  <span class="value available">{{ house.available_rooms_count || 0 }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Occupied:</span>
                  <span class="value occupied">{{ house.occupied_rooms_count || 0 }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Maintenance:</span>
                  <span class="value maintenance">{{ house.maintenance_rooms_count || 0 }}</span>
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
              <div class="info-row">
                <span class="label">Created By:</span>
                <span class="value">
                  {{ house.created_by_name || 'System' }}
                  <span v-if="house.created_by_type" class="user-type" :class="house.created_by_type.toLowerCase()">
                    ({{ house.created_by_type }})
                  </span>
                </span>
              </div>
              <div class="info-row">
                <span class="label">Updated By:</span>
                <span class="value">
                  {{ house.updated_by_name || 'System' }}
                  <span v-if="house.updated_by_type" class="user-type" :class="house.updated_by_type.toLowerCase()">
                    ({{ house.updated_by_type }})
                  </span>
                </span>
              </div>

              <div class="house-actions">
                <button @click="editHouse(house)" class="action-btn edit">
                  <img src="@/assets/images/Edit.png" alt="edit" class="action-icon">
                  Edit
                </button>
                <button @click="viewDetails(house)" class="action-btn view">
                  <img src="@/assets/images/eye.png" alt="view" class="action-icon">
                  View Details
                </button>
                <button @click="confirmDelete(house)" class="action-btn delete">
                  <img src="@/assets/images/trash.png" alt="delete" class="action-icon">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDetailsModal" class="details-modal" @click="closeDetailsModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedHouse.name }}</h2>
          <button class="close-button" @click="closeDetailsModal">&times;</button>
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
              <h3>Landlord Information</h3>
              <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value">{{ selectedHouse.landlord_name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Contact:</span>
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
                  {{ selectedHouse.created_by_name }}
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
                  {{ selectedHouse.updated_by_name }}
                  <span v-if="selectedHouse.updated_by_type" class="user-type">({{ selectedHouse.updated_by_type }})</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="edit-btn" @click="editFromModal(selectedHouse)">Edit</button>
          <button class="close-btn" @click="closeDetailsModal">Close</button>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
      <div class="modal-content" @click.stop>
        <button class="close-button" @click="closeImageModal">&times;</button>
        <div class="zoom-controls">
          <button @click="zoomIn" class="zoom-btn">+</button>
          <button @click="zoomOut" class="zoom-btn">-</button>
          <button @click="resetZoom" class="zoom-btn">Reset</button>
        </div>
        <div class="image-container">
          <img 
            :src="getBoardingHouseImage(selectedImage)" 
            :alt="selectedHouse?.name"
            class="modal-image"
            :style="{ transform: `scale(${zoomLevel})` }"
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
  name: 'AdminBoardingHouse',
  data() {
    return {
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId')
      },
      defaultProfilePic,
      boardingHouses: [],
      loading: true,
      error: null,
      showDetailsModal: false,
      selectedHouse: null,
      defaultImage: defaultHouseImage,
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
      try {
        if (!profilePicture) {
          return this.defaultProfilePic;
        }
        if (profilePicture instanceof File) {
          return URL.createObjectURL(profilePicture);
        }
        if (typeof profilePicture === 'string' && profilePicture.trim() !== '') {
          return profilePicture;
        }
        return this.defaultProfilePic;
      } catch (error) {
        console.error('Error getting profile picture:', error);
        return this.defaultProfilePic;
      }
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.logout();
      } else {
        this.$router.push(route);
      }
    },

    async logout() {
      try {
        await axios.post('/admin/logout');
        localStorage.clear();
        this.$router.push('/admin/admin_login');
      } catch (error) {
        console.error('Logout error:', error);
        this.$router.push('/admin/admin_login');
      }
    },

    async fetchBoardingHouses() {
      try {
        this.loading = true;
        const response = await axios.get('/admin/get-boarding-houses');
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data;
        } else {
          console.error('Failed to fetch boarding houses:', response.data.message);
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
      } finally {
        this.loading = false;
      }
    },

    showAddHouseForm() {
      this.$router.push('/admin/add-boarding-house');
    },

    editHouse(house) {
      this.$router.push(`/admin/boarding-houses/${house.boarding_house_id}/edit`);
    },

    viewDetails(house) {
      this.selectedHouse = house;
      this.showDetailsModal = true;
      document.body.style.overflow = 'hidden';
    },

    closeDetailsModal() {
      this.showDetailsModal = false;
      this.selectedHouse = null;
      document.body.style.overflow = 'auto';
    },

    editFromModal(house) {
      this.closeDetailsModal();
      this.editHouse(house);
    },

    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    },

    getBoardingHouseImage(image) {
      if (image) {
        return image;
      }
      return this.defaultImage;
    },

    async updateBoardingHouse(id, data) {
      try {
        const response = await axios.put(`/admin/update-boarding-house/${id}`, data, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (response.data.status === 'success') {
          alert('Boarding house updated successfully');
          await this.fetchBoardingHouses();
        }
      } catch (error) {
        console.error('Error updating boarding house:', error);
        alert(error.response?.data?.message || 'Failed to update boarding house');
      }
    },

    openImageModal(house) {
      this.selectedHouse = house;
      this.selectedImage = house.bh_images;
      this.showImageModal = true;
      this.zoomLevel = 1;
      document.body.style.overflow = 'hidden';
    },
    closeImageModal() {
      this.showImageModal = false;
      this.selectedImage = null;
      this.zoomLevel = 1;
      document.body.style.overflow = 'auto';
    },
    zoomIn() {
      if (this.zoomLevel < this.maxZoom) {
        this.zoomLevel = Math.min(this.maxZoom, this.zoomLevel + this.zoomStep);
      }
    },
    zoomOut() {
      if (this.zoomLevel > this.minZoom) {
        this.zoomLevel = Math.max(this.minZoom, this.zoomLevel - this.zoomStep);
      }
    },
    resetZoom() {
      this.zoomLevel = 1;
    },
    confirmDelete(house) {
      if (confirm(`Are you sure you want to delete "${house.name}"?`)) {
        this.deleteBoardingHouse(house.boarding_house_id);
      }
    },

    async deleteBoardingHouse(id) {
      try {
        const response = await axios.delete(`/admin/boarding-houses/${id}`);
        if (response.data.status === 'success') {
          alert('Boarding house deleted successfully');
          await this.fetchBoardingHouses(); // Refresh the list
        }
      } catch (error) {
        console.error('Error deleting boarding house:', error);
        alert(error.response?.data?.message || 'Failed to delete boarding house');
      }
    }
  },
  async created() {
    // Check if user is authenticated and is admin
    const isAdmin = localStorage.getItem('userType') === 'admin';
    const hasToken = localStorage.getItem('token') === 'true';
    const userId = localStorage.getItem('userId');
    
    if (!isAdmin || !hasToken || !userId) {
      console.log('Not authenticated, redirecting to login');
      this.$router.push('/admin/admin_login');
      return;
    }

    await this.fetchBoardingHouses();
  }
};
</script>

<style scoped>
@import '@/assets/css/admin_panel.css';

.overall {
  padding: 2rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px);
}

.boarding-houses-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.helper {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 2rem;
}

/* Add New Button */
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
  margin-bottom: 2rem;
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

/* Houses Grid */
.houses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.house-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.house-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.house-name {
  color: #794646;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  padding: 0.5rem;
  background: white;
  border-radius: 6px;
}

.label {
  color: #666;
  font-weight: 500;
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

.value.maintenance {
  color: #f59e0b;
  font-weight: 600;
}

.user-type {
  font-size: 0.85em;
  color: #794646;
  margin-left: 0.5rem;
  font-style: italic;
}

/* Actions */
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

.action-btn.delete {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn.delete:hover {
  background: #fecaca;
  transform: translateY(-2px);
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-icon {
  width: 16px;
  height: 16px;
}

/* Loading and Empty States */
.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #666;
}

/* Profile section */
.profile-section {
  margin-left: auto !important;
  margin-right: 1rem !important;
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
  margin-left: 0 !important;
  margin-right: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .boarding-houses-container {
    padding: 1rem;
  }

  .houses-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .house-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }

  .admin-profile-name {
    display: none;
  }
}

.details-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  color: #794646;
  margin: 0;
  font-size: 1.5rem;
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
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-weight: 600;
  color: #374151;
  width: 120px;
  flex-shrink: 0;
}

.detail-value {
  color: #4b5563;
  flex-grow: 1;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
  padding: 0.5rem;
}

.edit-btn,
.close-btn {
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

.edit-btn:hover,
.close-btn:hover {
  transform: translateY(-2px);
}

@media (max-width: 640px) {
  .modal-content {
    width: 95%;
    margin: 1rem;
  }

  .detail-row {
    flex-direction: column;
  }

  .detail-label {
    width: 100%;
    margin-bottom: 0.25rem;
  }
}

/* Add a room stats section */
.room-stats {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
}

.room-stats-title {
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  background: white;
  border-radius: 4px;
  margin-bottom: 0.5rem;
}

.info-row:last-child {
  margin-bottom: 0;
}

.label {
  color: #666;
  font-weight: 500;
}

.value {
  font-weight: 600;
}

.boarding-house-image {
  width: 200px;
  height: 150px;
  overflow: hidden;
  border-radius: 8px;
}

.boarding-house-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.details-image {
  width: 100%;
  max-height: 300px;
  overflow: hidden;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.details-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.details-info {
  padding: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.stat-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
}

.stat-label {
  display: block;
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 600;
  color: #794646;
}

.house-image {
  width: 100%;
  height: 200px;
  margin-bottom: 1rem;
  border-radius: 8px;
  overflow: hidden;
}

.bh-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
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
  transition: transform 0.3s ease;
}

.zoom-controls {
  position: absolute;
  bottom: -50px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 0.5rem;
  border-radius: 8px;
}

.zoom-btn {
  background: #794646;
  color: white;
  border: none;
  border-radius: 4px;
  width: 30px;
  height: 30px;
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.zoom-btn:hover {
  background: #693c3c;
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
