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
        <h4 class="helper">ADD BOARDING HOUSE</h4>
      </div>

      <div class="form-container">
        <form @submit.prevent="submitBoardingHouse" class="boarding-house-form">
          <div class="form-group">
            <label>Boarding House Name</label>
            <input 
              type="text" 
              v-model="formData.name" 
              required
              placeholder="Enter boarding house name"
            >
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea 
              v-model="formData.description" 
              required
              placeholder="Enter description"
              rows="4"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Address</label>
            <input 
              type="text" 
              v-model="formData.address" 
              required
              placeholder="Enter complete address"
            >
          </div>

          <div class="form-group">
            <label>Landlord Name</label>
            <input 
              type="text" 
              v-model="formData.landlord_name" 
              required
              placeholder="Enter landlord name"
            >
          </div>

          <div class="form-group">
            <label>Landlord Phone</label>
            <input 
              type="tel" 
              v-model="formData.landlord_phone" 
              required
              placeholder="Enter landlord phone number"
              pattern="[0-9]*"
            >
          </div>

          <div class="form-group">
            <label>Boarding House Image</label>
            <div class="image-upload-container">
              <div class="image-preview" v-if="imagePreview">
                <img :src="imagePreview" alt="Preview" class="preview-image">
                <button type="button" class="remove-image" @click="removeImage">&times;</button>
              </div>
              <div class="upload-btn-wrapper">
                <button type="button" class="upload-btn" @click="triggerFileInput">
                  {{ imagePreview ? 'Change Image' : 'Upload Image' }}
                </button>
                <input 
                  type="file" 
                  ref="fileInput"
                  @change="handleImageUpload" 
                  accept="image/*"
                  required
                  class="file-input"
                >
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="$router.push('/landlord/boarding-houses')" class="cancel-btn">Cancel</button>
            <button type="submit" class="submit-btn">Add Boarding House</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'LandlordAddBoardingHouse',
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
      formData: {
        name: '',
        description: '',
        address: '',
        landlord_name: '',
        landlord_phone: '',
        bh_images: null,
        user_id: localStorage.getItem('userId'),
        created_by: localStorage.getItem('userId'),
        updated_by: localStorage.getItem('userId')
      },
      imagePreview: null
    };
  },
  computed: {
    sanitizedMapLink() {
      if (!this.formData.map_link) return '';
      
      try {
        if (this.formData.map_link.includes('<iframe')) {
          const srcMatch = this.formData.map_link.match(/src="([^"]+)"/);
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
        return `<iframe src="${this.formData.map_link}" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>`;
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

    triggerFileInput() {
      this.$refs.fileInput.click();
    },

    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB limit
          alert('Image size should not exceed 2MB');
          return;
        }
        this.formData.bh_images = file;
        this.imagePreview = URL.createObjectURL(file);
      }
    },

    removeImage() {
      this.formData.bh_images = null;
      this.imagePreview = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },

    async submitBoardingHouse() {
      try {
        const formData = new FormData();
        const userId = localStorage.getItem('userId');
        
        // Add all required fields
        formData.append('name', this.formData.name);
        formData.append('description', this.formData.description);
        formData.append('address', this.formData.address);
        formData.append('landlord_name', this.formData.landlord_name);
        formData.append('landlord_phone', this.formData.landlord_phone);
        if (this.formData.bh_images instanceof File) {
            formData.append('bh_images', this.formData.bh_images);
        }
        formData.append('user_id', userId);
        formData.append('created_by', userId);
        formData.append('updated_by', userId);

        // Send request
        const response = await axios.post('/landlord/add-boarding-house', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.status === 'success') {
            alert('Boarding house added successfully');
            this.$router.push('/landlord/boarding-houses');
        }
      } catch (error) {
        console.error('Error saving boarding house:', error);
        alert(error.response?.data?.message || 'Failed to add boarding house');
      }
    },

    previewMap() {
      if (this.formData.map_link) {
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

.menu-container {
  margin-left: 0 !important;
  margin-right: 2rem;
}

@media (max-width: 768px) {
  .landlord-profile-name {
    display: none;
  }
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
.form-group textarea {
  width: 97%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.form-group textarea {
  resize: vertical;
  min-height: 120px;
}

.map-input-container {
  position: relative;
}

.map-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.preview-map-btn {
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
  padding: 0.25rem 0.5rem;
  border: none;
  border-radius: 4px;
  background-color: #f0f0f0;
  cursor: pointer;
}

.map-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.map-modal-content {
  background-color: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  width: 90%;
  max-height: 80%;
  overflow: auto;
}

.close-map-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  border: none;
  background: none;
  font-size: 24px;
  cursor: pointer;
}

.map-help {
  display: block;
  margin-top: 0.5rem;
  font-size: 14px;
  color: #794646;
}

/* Add these button styles */
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
  transition: all 0.3s ease;
}

.cancel-btn {
  background: #ed0505;
  color: white;
}

.submit-btn {
  background: black;
  color: white;
}

.cancel-btn:hover {
  background: #b80909;
  transform: translateY(-2px);
}

.submit-btn:hover {
  background: #4b4b4b;
  transform: translateY(-2px);
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

@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
    gap: 0.5rem;
  }

  .cancel-btn,
  .submit-btn,
  .preview-map-btn {
    width: 100%;
  }
}

.price-input {
  width: 97% !important;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.price-input:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.form-group input[type="tel"] {
  width: 97%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.form-group input[type="tel"]:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.image-upload-container {
  margin-top: 1rem;
}

.image-preview {
  position: relative;
  width: 200px;
  height: 200px;
  margin-bottom: 1rem;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.remove-image {
  position: absolute;
  top: -10px;
  right: -10px;
  background: #ff4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-btn-wrapper {
  position: relative;
  overflow: hidden;
  display: inline-block;
}

.upload-btn {
  background: #794646;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.file-input {
  position: absolute;
  left: 0;
  top: 0;
  opacity: 0;
  cursor: pointer;
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