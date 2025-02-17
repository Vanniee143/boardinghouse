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
      <div class="home">
        <h4 class="helper">ADD NEW ROOM</h4>
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
                :key="house.id"
                :value="house.id"
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

          <div class="form-actions">
            <button type="button" @click="$router.push('/landlord/rooms')" class="cancel-btn">Cancel</button>
            <button type="submit" class="submit-btn">Add Room</button>
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
  name: 'LandlordAddRoom',
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
      boardingHouses: [],
      roomData: {
        boarding_house_id: '',
        room_name: '',
        bed_quantity: '',
        status: '',
        room_image: null,
        map_link: '',
        price: '',
      },
      imagePreview: null,
      showMapPreview: false,
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

    async fetchBoardingHouses() {
      try {
        console.log('Fetching boarding houses...');
        const response = await axios.get('/landlord/get-boarding-houses');
        console.log('Response:', response.data);
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data;
          console.log('Processed boarding houses:', this.boardingHouses);
        } else {
          throw new Error('Failed to fetch boarding houses');
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        this.boardingHouses = [];
      }
    },

    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.roomData.room_image = file;
        const reader = new FileReader();
        reader.onload = e => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    async submitRoom() {
      try {
        const formData = new FormData();
        const landlordId = localStorage.getItem('userId');
        
        // Add required fields
        formData.append('boarding_house_id', this.roomData.boarding_house_id);
        formData.append('room_name', this.roomData.room_name);
        formData.append('bed_quantity', this.roomData.bed_quantity);
        formData.append('status', this.roomData.status);
        formData.append('price', this.roomData.price);
        
        // Add required user fields
        formData.append('user_id', landlordId);
        formData.append('created_by', landlordId);
        formData.append('updated_by', landlordId);

        // Handle map link
        if (this.roomData.map_link) {
          if (this.roomData.map_link.includes('<iframe')) {
            const srcMatch = this.roomData.map_link.match(/src="([^"]+)"/);
            if (srcMatch && srcMatch[1]) {
              formData.append('map_link', srcMatch[1]);
            }
          } else {
            formData.append('map_link', this.roomData.map_link);
          }
        }
        
        // Handle room image
        if (this.roomData.room_image instanceof File) {
          formData.append('room_image', this.roomData.room_image);
        }

        const response = await axios.post('/landlord/add-room', formData);

        if (response.data.status === 'success') {
          alert('Room added successfully');
          this.$router.push('/landlord/rooms');
        }
      } catch (error) {
        console.error('Error saving room:', error);
        alert('Failed to save room: ' + (error.response?.data?.message || error.message));
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
    }
  },
  async created() {
    await this.fetchLandlordProfile();
    await this.fetchBoardingHouses();
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

.form-container {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  margin: 0 auto;
}

.overall {
  transform: translate(0%, -3%);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 96%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #794646;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
}

.cancel-btn,
.submit-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cancel-btn {
  background: #ed0505;
  border: none;
  color: white;
}

.submit-btn {
  background: black;
  border: none;
  color: white;
}

.cancel-btn:hover {
  background: #b80909;
}

.submit-btn:hover {
  background: #4b4b4b;
}

@media (max-width: 768px) {
  .form-container {
    padding: 1rem;
  }
}

.file-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
}

.image-preview-container {
  margin-top: 1rem;
  border-radius: 8px;
  overflow: hidden;
  max-width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8f8f8;
  border: 1px solid #ddd;
}

.image-preview {
  max-width: 100%;
  height: auto;
  display: block;
  object-fit: contain;
  max-height: 300px;
}

@media (max-width: 768px) {
  .image-preview {
    max-height: 200px;
  }
}

@media (max-width: 480px) {
  .image-preview {
    max-height: 150px;
  }
}

/* Profile section styles */
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

/* Adjust menu container to not use margin-left: auto */
.menu-container {
  margin-left: 0;
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