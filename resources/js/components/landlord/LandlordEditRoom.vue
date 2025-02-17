<template>
  <div class="landlord-panel">
    <div class="navbars">
      <div class="nav-left">
        <router-link to="/landlord/dashboard" class="logo-link">
          <img src="@/assets/images/image.png" alt="logo" class="logos">
        </router-link>
        <router-link to="/landlord/dashboard" class="nl">
          <h5>SBH BOOKING</h5>
        </router-link>
      </div>

      <div class="nav-right">
        <div class="profile-section">
          <div class="landlord-profile">
            <img 
              :src="getProfilePicture(landlordProfile.profile_picture)"
              alt="profile" 
              class="profile-icon"
              @error="handleImageError"
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
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">EDIT ROOM</h4>
      </div>

      <div class="form-container">
        <form @submit.prevent="updateRoom" class="room-form">
          <div class="form-group">
            <label>Room Name</label>
            <input 
              type="text" 
              v-model="roomData.room_name" 
              required
              placeholder="Enter room name"
            >
          </div>

          <div class="form-group">
            <label>Bed Quantity</label>
            <input 
              type="number" 
              v-model="roomData.bed_quantity" 
              required
              min="1"
              placeholder="Enter number of beds"
            >
          </div>

          <div class="form-group">
            <label>Status</label>
            <select v-model="roomData.status" required>
              <option value="" disabled>Select Status</option>
              <option value="available">Available</option>
              <option value="occupied">Occupied</option>
              <option value="maintenance">Under Maintenance</option>
            </select>
          </div>

          <div class="form-group">
            <label>Room Image</label>
            <input 
              type="file" 
              @change="handleImageUpload" 
              accept="image/*"
              class="file-input"
            >
            <div v-if="imagePreview" class="image-preview-container">
              <img :src="imagePreview" alt="Room preview" class="image-preview">
            </div>
          </div>

          <div class="form-group">
            <label>Map Link</label>
            <div class="map-input-container">
              <input 
                type="text" 
                v-model="roomData.map_link" 
                placeholder="Paste Google Maps embed code here"
                class="map-input"
              >
              <button 
                type="button" 
                class="preview-map-btn" 
                @click="previewMap" 
                v-if="roomData.map_link"
              >
                Preview Map
              </button>
            </div>
            <small class="map-help">
              Go to Google Maps > Share > Embed a map > Copy the entire iframe code and paste it here
            </small>
          </div>

          <div class="form-group">
            <label>Price</label>
            <input
                type="number"
                v-model="roomData.price"
                required
                placeholder="Enter room price"
                class="form-input"
                min="0"
                step="0.01"
            />
          </div>

          <div class="timestamps">
            <div class="timestamp-info">
              <span class="timestamp-label">Created:</span>
              <span class="timestamp-value">{{ formatTimestamp(roomData.created_at) }}</span>
            </div>
            <div class="timestamp-info">
              <span class="timestamp-label">Last Updated:</span>
              <span class="timestamp-value">{{ formatTimestamp(roomData.updated_at) }}</span>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="$router.push('/landlord/rooms')" class="cancel-btn">Cancel</button>
            <button type="submit" class="submit-btn">Update Room</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Map Preview Modal -->
    <div v-if="showMapPreview" class="map-modal" @click="closeMapPreview">
      <div class="map-modal-content" @click.stop>
        <div v-html="sanitizedMapLink"></div>
        <button class="close-map-btn" @click="closeMapPreview">&times;</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'LandlordEditRoom',
  props: {
    id: {
      type: String,
      required: true
    }
  },
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
      roomData: {
        room_name: '',
        bed_quantity: 1,
        status: 'available',
        room_images: null,
        map_link: '',
        price: 0,
        boarding_house_id: '',
        updated_by: localStorage.getItem('userId')
      },
      imagePreview: null,
      showMapPreview: false,
      loading: true,
      error: null
    };
  },
  computed: {
    sanitizedMapLink() {
      if (!this.roomData.map_link) return '';
      
      try {
        if (this.roomData.map_link.includes('<iframe')) {
          const srcMatch = this.roomData.map_link.match(/src="([^"]+)"/);
          if (srcMatch && srcMatch[1]) {
            return `<iframe 
                    src="${srcMatch[1]}" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                    </iframe>`;
          }
        }
        return `<iframe src="${this.roomData.map_link}" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>`;
      } catch (error) {
        console.error('Error processing map URL:', error);
        return '';
      }
    }
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
        localStorage.clear();
        delete axios.defaults.headers.common['Authorization'];
        this.$router.push('/landlord/login');
      } catch (error) {
        console.error('Logout error:', error);
        this.$router.push('/landlord/login');
      }
    },

    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          alert('Image size should not exceed 2MB');
          event.target.value = '';
          return;
        }
        this.roomData.room_images = file;
        this.imagePreview = URL.createObjectURL(file);
      }
    },

    async fetchRoomData() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          console.error('No user ID found in localStorage');
          this.$router.push('/landlord/login');
          return;
        }

        console.log('Fetching room data for:', {
          roomId: this.$route.params.id,
          userId: userId
        });

        const response = await axios.get(`/landlord/get-room/${this.$route.params.id}`, {
          headers: {
            'X-User-Id': userId,
            'Accept': 'application/json'
          }
        });

        console.log('Room data response:', response.data);

        if (response.data.status === 'success') {
          const room = response.data.data;
          this.roomData = {
            ...this.roomData,
            room_name: room.room_name,
            bed_quantity: room.bed_quantity,
            status: room.status,
            map_link: room.map_link,
            price: room.price,
            boarding_house_id: room.boarding_house_id,
            created_at: room.created_at,
            updated_at: room.updated_at
          };

          if (room.room_images) {
            this.imagePreview = room.room_images;
          }

          console.log('Room data loaded:', this.roomData);
        } else {
          throw new Error(response.data.message || 'Failed to fetch room data');
        }
      } catch (error) {
        console.error('Error fetching room data:', error);
        console.error('Error details:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        });
        
        if (error.response?.status === 403) {
          alert('You are not authorized to edit this room');
          this.$router.push('/landlord/rooms');
        } else if (error.response?.status === 401) {
          alert('Please log in again');
          this.$router.push('/landlord/login');
        } else {
          this.error = 'Failed to load room data: ' + (error.response?.data?.message || error.message);
        }
      } finally {
        this.loading = false;
      }
    },

    async updateRoom() {
      try {
        const formData = new FormData();
        
        // Append all form fields
        Object.keys(this.roomData).forEach(key => {
          if (key === 'room_images' && this.roomData[key] instanceof File) {
            formData.append('room_images', this.roomData[key]);
          } else {
            formData.append(key, this.roomData[key]);
          }
        });

        const userId = localStorage.getItem('userId');
        const response = await axios.post(
          `/landlord/update-room/${this.$route.params.id}`, 
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data',
              'X-User-Id': userId
            }
          }
        );

        if (response.data.status === 'success') {
          alert('Room updated successfully');
          this.$router.push('/landlord/rooms');
        }
      } catch (error) {
        console.error('Error updating room:', error);
        if (error.response?.status === 403) {
          alert('You are not authorized to update this room');
          this.$router.push('/landlord/rooms');
        } else {
          alert(error.response?.data?.message || 'Failed to update room');
        }
      }
    },

    previewMap() {
      if (this.roomData.map_link) {
        this.showMapPreview = true;
        document.body.style.overflow = 'hidden';
      }
    },

    closeMapPreview() {
      this.showMapPreview = false;
      document.body.style.overflow = 'auto';
    },

    formatTimestamp(timestamp) {
      if (!timestamp) return 'N/A';
      try {
        return new Date(timestamp).toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });
      } catch (error) {
        console.error('Error formatting timestamp:', error);
        return 'N/A';
      }
    }
  },
  async created() {
    await this.fetchLandlordProfile();
    await this.fetchRoomData();
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

.overall {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px);
}

.form-container {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  margin: 0 auto;
}

.helper {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 18px;
}

.form-group input,
.form-group select {
  width: 97%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.file-input {
  width: 97%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

.image-preview-container {
  margin-top: 1rem;
  border-radius: 8px;
  overflow: hidden;
  max-width: 300px;
  height: 200px;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8f8f8;
  border: 1px solid #ddd;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
}

.cancel-btn,
.submit-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  transition: background-color 0.2s;
}

.cancel-btn {
  background: #f0f0f0;
  color: #333;
}

.submit-btn {
  background: #794646;
  color: white;
}

.cancel-btn:hover {
  background: #e0e0e0;
}

.submit-btn:hover {
  background: #693c3c;
}

/* Profile section styles */
.profile-section {
  margin-left: auto !important;
  margin-right: 1rem !important;
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

/* Map related styles */
.map-input-container {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 0.5rem;
  width: 97%;
}

.map-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s ease;
}

.map-input:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.preview-map-btn {
  padding: 0.75rem 1.5rem;
  background: #794646;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.preview-map-btn:hover {
  background: #693c3c;
  transform: translateY(-2px);
}

.map-help {
  color: #666;
  font-size: 0.9rem;
  margin-top: 0.5rem;
  font-style: italic;
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

.map-modal-content {
  position: relative;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  width: 90%;
  max-width: 900px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.close-map-btn {
  position: absolute;
  top: -40px;
  right: -40px;
  background: none;
  border: none;
  color: white;
  font-size: 30px;
  cursor: pointer;
  padding: 10px;
  transition: opacity 0.3s ease;
}

.close-map-btn:hover {
  opacity: 0.8;
}

/* Responsive styles */
@media (max-width: 768px) {
  .form-container {
    padding: 1rem;
  }

  .form-group input,
  .form-group select,
  .file-input {
    width: 95%;
  }

  .image-preview-container {
    max-width: 100%;
  }

  .map-input-container {
    flex-direction: column;
    gap: 0.5rem;
  }

  .preview-map-btn {
    width: 100%;
  }

  .map-modal-content {
    width: 95%;
    padding: 1rem;
  }

  .close-map-btn {
    top: -30px;
    right: -10px;
  }

  .landlord-profile-name {
    display: none;
  }
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-left: auto;
}

.profile-section {
  display: flex;
  align-items: center;
}

.landlord-profile {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.menu-container {
  display: flex;
  align-items: center;
}

.timestamps {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.timestamp-info {
  display: flex;
  margin-bottom: 0.5rem;
  color: #666;
  font-size: 0.9rem;
}

.timestamp-label {
  width: 120px;
  font-weight: 600;
  color: #794646;
}

.timestamp-value {
  flex: 1;
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