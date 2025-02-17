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
      <div class="header-section">
        <h4 class="helper">ADD ROOM</h4>
      </div>

      <div class="form-container">
        <form @submit.prevent="submitRoom" class="room-form">
          <div class="form-group">
            <label>Boarding House</label>
            <select 
              v-model="roomData.boarding_house_id"
              required
              class="form-input"
            >
              <option value="" disabled selected>Select Boarding House</option>
              <option 
                v-for="house in boardingHouses" 
                :key="house.boarding_house_id"
                :value="house.boarding_house_id"
              >
                {{ house.name }}
              </option>
            </select>
          </div>

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
            <select 
              v-model="roomData.status"
              required
              class="form-input"
            >
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
              required
              class="file-input"
            >
            <div 
              v-if="imagePreview" 
              class="image-preview-container"
              @click="openImagePreview"
              style="cursor: pointer;"
            >
              <img :src="imagePreview" alt="Room preview" class="image-preview">
              <div class="preview-overlay">
                <span>Click to preview</span>
              </div>
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
            <label>Room Price</label>
            <input 
              type="number" 
              v-model="roomData.price" 
              required
              min="0"
              step="0.01"
              placeholder="Enter room price"
            >
          </div>

          <div class="form-actions">
            <button type="button" class="cancel-btn" @click="$router.push('/admin/rooms')">Cancel</button>
            <button type="submit" class="submit-btn">Add Room</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showMapPreview" class="map-modal" @click="closeMapPreview">
      <div class="modal-content" @click.stop>
        <button class="close-button" @click="closeMapPreview">&times;</button>
        <div class="map-container">
          <div v-html="sanitizedMapLink"></div>
        </div>
      </div>
    </div>

    <div v-if="showImagePreview" class="image-modal" @click="closeImagePreview">
      <div class="modal-content" @click.stop>
        <button class="close-button" @click="closeImagePreview">&times;</button>
        <div class="image-container">
          <img :src="imagePreview" alt="Room preview" class="full-image">
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'AddRoom',
  data() {
    return {
      isEditing: false,
      roomData: {
        boarding_house_id: '',
        room_name: '',
        bed_quantity: '',
        status: 'available',
        room_images: null,
        map_link: '',
        price: '',
        created_by: localStorage.getItem('userId')
      },
      boardingHouses: [],
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId')
      },
      defaultProfilePic,
      imagePreview: null,
      showMapPreview: false,
      showImagePreview: false
    }
  },
  computed: {
    sanitizedMapLink() {
      if (!this.roomData.map_link) return '';
      
      try {
        // If it's already an iframe code, extract the src
        if (this.roomData.map_link.includes('<iframe')) {
          const srcMatch = this.roomData.map_link.match(/src="([^"]+)"/);
          if (srcMatch && srcMatch[1]) {
            return `<iframe 
                    src="${srcMatch[1]}" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                    </iframe>`;
          }
        }
        
        // If it's just the URL, create an iframe
        return `<iframe 
                src="${this.roomData.map_link}" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
                </iframe>`;
      } catch (error) {
        console.error('Error processing map URL:', error);
        return '';
      }
    }
  },
  methods: {
    ...mapActions({
      addRoomAction: 'admin/addRoom',
      updateRoomAction: 'admin/updateRoom',
      fetchBoardingHousesAction: 'admin/fetchBoardingHouses'
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

    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB limit
          alert('File size should not exceed 2MB');
          event.target.value = '';
          return;
        }
        this.roomData.room_images = file;
        this.imagePreview = URL.createObjectURL(file);
      }
    },

    async submitRoom() {
      try {
        const formData = new FormData();
        
        // Convert map_link if it's an iframe before submitting
        if (this.roomData.map_link && this.roomData.map_link.includes('<iframe')) {
          const srcMatch = this.roomData.map_link.match(/src="([^"]+)"/);
          if (srcMatch && srcMatch[1]) {
            this.roomData.map_link = srcMatch[1];
          }
        }
        
        // Append all form fields
        formData.append('boarding_house_id', this.roomData.boarding_house_id);
        formData.append('room_name', this.roomData.room_name);
        formData.append('bed_quantity', this.roomData.bed_quantity);
        formData.append('status', this.roomData.status);
        formData.append('price', this.roomData.price);
        formData.append('created_by', this.roomData.created_by);
        
        if (this.roomData.map_link) {
          formData.append('map_link', this.roomData.map_link);
        }
        
        if (this.roomData.room_images instanceof File) {
          formData.append('room_images', this.roomData.room_images);
        }

        // Log the FormData contents for debugging
        for (let pair of formData.entries()) {
          console.log(pair[0] + ': ' + pair[1]);
        }

        const response = await axios.post('/admin/add-room', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (response.data.status === 'success') {
          alert('Room added successfully');
          this.$router.push('/admin/rooms');
        }
      } catch (error) {
        console.error('Error saving room:', error);
        console.error('Error details:', error.response?.data);
        alert(error.response?.data?.message || 'Failed to add room');
      }
    },

    async fetchBoardingHouses() {
      try {
        console.log('Fetching boarding houses...');
        const response = await axios.get('/admin/fetch-boarding-houses');
        console.log('Boarding houses response:', response.data);
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data;
        } else {
          throw new Error('Failed to fetch boarding houses');
        }
        
        console.log('Updated boardingHouses array:', this.boardingHouses);
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        this.boardingHouses = [];
      }
    },

    async fetchRoomData(id) {
      try {
        const response = await axios.get(`/admin/fetch-room/${id}`);
        this.roomData = response.data;
      } catch (error) {
        console.error('Error fetching room:', error);
        alert('Failed to fetch room data');
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

    async fetchAdminProfile() {
      try {
        const userId = this.adminProfile.user_id;
        const response = await axios.get(`/admin/fetch-account/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.adminName = userData.name;
          this.adminProfile.profile_picture = userData.profile_picture;
          
          // Update localStorage
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching admin profile:', error);
      }
    },

    openImagePreview() {
      this.showImagePreview = true;
      document.body.style.overflow = 'hidden';
    },

    closeImagePreview() {
      this.showImagePreview = false;
      document.body.style.overflow = 'auto';
    }
  },
  async created() {
    await this.fetchAdminProfile();
    await this.fetchBoardingHouses();
    
    const { edit, id } = this.$route.query;
    
    if (edit && id) {
      this.isEditing = true;
      await this.fetchRoomData(id);
    }
  },
  watch: {
    boardingHouses: {
      handler(newVal) {
        console.log('boardingHouses changed:', newVal);
      },
      deep: true
    }
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

.header-section {
  margin-bottom: 2rem;
}

.helper {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.form-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1.7rem;
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

.form-group select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1em;
  padding-right: 2.5rem;
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
  position: relative;
  transition: transform 0.2s ease;
}

.image-preview-container:hover {
  transform: scale(1.02);
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

.loading-text {
  color: #666;
  font-size: 0.9rem;
  margin-top: 0.5rem;
  font-style: italic;
}

.error-text {
  color: #dc3545;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

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
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 900px;
  max-height: 90vh;
  overflow: hidden;
}

.map-container {
  width: 100%;
  height: 100%;
  min-height: 450px;
  border-radius: 8px;
  overflow: hidden;
}

.map-container iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.close-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  color: #666;
  font-size: 24px;
  cursor: pointer;
  padding: 0.5rem;
  z-index: 1;
}

.close-button:hover {
  color: #333;
}

/* Responsive styles */
@media (max-width: 768px) {
  .form-container {
    padding: 1.5rem;
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
}

/* Add or update these styles */
.overall {
  padding: 2rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px); /* Adjust this to match navbar height */
}

.header-section {
  margin-bottom: 2rem;
}

.helper {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.form-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Fix viewport scrollbars */
.admin-rooms {
  width: 100%;
  height: 100vh;
  overflow: hidden;
  position: fixed;
  top: 0;
  left: 0;
}

.overall {
  height: calc(100vh - 70px); /* Subtract navbar height */
  overflow-y: auto; /* Allow vertical scroll */
  overflow-x: hidden;
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
  z-index: 1000;
}

.image-container {
  max-width: 90vw;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.full-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.preview-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.preview-overlay span {
  color: white;
  font-size: 14px;
  padding: 8px 16px;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 4px;
}

.image-preview-container {
  position: relative;
}

.image-preview-container:hover .preview-overlay {
  opacity: 1;
}

.modal-content {
  position: relative;
  background: transparent;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 1200px;
  max-height: 90vh;
  overflow: hidden;
}

.close-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.3);
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
  z-index: 1;
}

.close-button:hover {
  background: rgba(255, 255, 255, 0.5);
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