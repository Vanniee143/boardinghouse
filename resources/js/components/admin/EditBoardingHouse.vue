<template>
  <div class="admin-panel">
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
      <div class="form-container">
        <h4 class="helper">Edit Boarding House</h4>
        <form @submit.prevent="updateBoardingHouse" class="boarding-house-form">
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
              <div class="preview-container" v-if="imagePreview || formData.bh_images">
                <img :src="imagePreview || formData.bh_images" alt="Preview" class="image-preview">
                <button type="button" @click="removeImage" class="remove-image">×</button>
              </div>
              <div class="upload-area" v-else @click="triggerFileInput">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload image</p>
              </div>
              <input 
                type="file" 
                ref="fileInput" 
                @change="handleImageUpload" 
                accept="image/*"
                class="file-input"
                style="display: none"
              >
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="$router.push('/admin/boarding-houses')" class="cancel-btn">Cancel</button>
            <button type="submit" class="submit-btn">Update Boarding House</button>
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
  name: 'EditBoardingHouse',
  data() {
    return {
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId')
      },
      defaultProfilePic,
      formData: {
        name: '',
        description: '',
        address: '',
        landlord_name: '',
        landlord_phone: '',
        bh_images: null,
        user_id: localStorage.getItem('userId')
      },
      boardingHouseId: this.$route.params.id,
      imagePreview: null,
      originalImage: null
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

    async fetchBoardingHouse() {
      try {
        const response = await axios.get(`/admin/get-boarding-house/${this.$route.params.id}`);
        this.formData = response.data.data;
        this.originalImage = this.formData.bh_images;
        this.imagePreview = this.formData.bh_images ? `/storage/${this.formData.bh_images}` : null;
      } catch (error) {
        console.error('Error fetching boarding house:', error);
      }
    },

    triggerFileInput() {
      this.$refs.fileInput.click();
    },

    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.formData.bh_images = file;
        this.imagePreview = URL.createObjectURL(file);
      }
    },

    removeImage() {
      this.formData.bh_images = null;
      this.imagePreview = null;
      this.$refs.fileInput.value = '';
    },

    async updateBoardingHouse() {
      try {
        const formDataToSend = new FormData();
        
        // Append all form fields
        Object.keys(this.formData).forEach(key => {
          if (key === 'bh_images' && this.formData[key] instanceof File) {
            formDataToSend.append('bh_images', this.formData[key]);
          } else {
            formDataToSend.append(key, this.formData[key]);
          }
        });

        // Add method spoofing for PUT request
        formDataToSend.append('_method', 'PUT');

        // Use POST method with _method field for PUT
        const response = await axios.post(
          `/admin/boarding-houses/${this.$route.params.id}`, 
          formDataToSend,
          {
            headers: {
              'Content-Type': 'multipart/form-data',
              'X-HTTP-Method-Override': 'PUT'
            }
          }
        );

        if (response.data.status === 'success') {
          alert('Boarding house updated successfully');
          this.$router.push('/admin/boarding-houses');
        }
      } catch (error) {
        console.error('Error updating boarding house:', error);
        alert(error.response?.data?.message || 'Failed to update boarding house');
      }
    }
  },
  created() {
    if (!this.boardingHouseId) {
      console.error('No boarding house ID provided');
      this.$router.push('/admin/boarding-houses');
      return;
    }
    this.fetchBoardingHouse();
  }
};
</script>

<style scoped>
@import '@/assets/css/admin_panel.css';

.overall {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px);
}

.form-container {
  background: #fff;
  border-radius: 8px;
  padding: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  margin: 0 auto;
}

.home {
  margin-bottom: 2rem;
}

.helper {
  color: #794646;
  font-size: 24px;
  font-weight: 700;
  margin: 0;
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

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
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

/* Profile section styles */
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

/* Responsive styles */
@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .form-container {
    padding: 1.5rem;
  }

  .form-group input,
  .form-group textarea {
    width: 95%;
  }

  .form-actions {
    flex-direction: column-reverse;
    gap: 0.5rem;
  }

  .cancel-btn,
  .submit-btn {
    width: 100%;
  }
}

/* Navbar styles */
.navbars {
  display: flex;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: white;
  padding: 0 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.logo-link {
  display: flex;
  align-items: center;
}

.nl {
  text-decoration: none;
  margin-left: 1rem;
}

.nl h5 {
  color: #794646;
  margin: 0;
}

.profile-section {
  margin-left: auto;
  display: flex;
  align-items: center;
}

.menu-container {
  margin-left: 2rem;
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
  border: 1px solid #ddd;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

/* Add responsive styles */
@media screen and (max-width: 768px) {
  .navbars {
    padding: 0 1rem;
  }

  .profile-section {
    margin-right: 1rem;
  }

  .admin-profile-name {
    display: none;
  }
}

.menuimgg {
  width: 24px;
  height: 24px;
  margin-right: 0.5rem;
}

.Menu {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background: none;
    border: none;
    color: #794646;
    font-family: Arial;
    font-size: 16px;
    font-weight: 600;
    padding: 6px;
    cursor: pointer;
}

.image-upload-container {
  width: 100%;
  margin-bottom: 1rem;
}

.preview-container {
  position: relative;
  width: 200px;
  height: 200px;
  margin-bottom: 1rem;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.remove-image {
  position: absolute;
  top: -10px;
  right: -10px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-area {
  border: 2px dashed #794646;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: background-color 0.2s;
}

.upload-area:hover {
  background-color: #f3f4f6;
}

.upload-area i {
  font-size: 2rem;
  color: #794646;
  margin-bottom: 1rem;
}

.upload-area p {
  color: #794646;
  margin: 0;
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