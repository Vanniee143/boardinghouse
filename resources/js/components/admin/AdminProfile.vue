<template>
  <div class="admin-profile-page">
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
          <option value="/admin/admin_login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">PROFILE SETTINGS</h4>
      </div>

      <div class="form-container">
        <form @submit.prevent="handleSubmit">
          <div class="profile-image-section">
            <img 
              :src="imagePreview || getProfilePicture(adminProfile.profile_picture)" 
              alt="Profile" 
              class="profile-preview"
              @click="openImageModal(imagePreview || getProfilePicture(adminProfile.profile_picture))"
            >
            <div class="image-upload">
              <label for="profile-image" class="upload-btn">Change Profile Picture</label>
              <input 
                type="file" 
                id="profile-image"
                @change="handleImageUpload" 
                accept="image/*"
                class="file-input"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Full Name</label>
            <input 
              type="text" 
              v-model="formData.name" 
              required
              placeholder="Enter your name"
            >
          </div>

          <div class="form-group">
            <label>Email</label>
            <input 
              type="email" 
              v-model="formData.email" 
              required
              placeholder="Enter your email"
            >
          </div>

          <div class="form-group">
            <label>Phone Number</label>
            <input 
              type="tel" 
              v-model="formData.phone_number" 
              placeholder="Enter phone number"
            >
          </div>

          <div class="form-group">
            <label>Gender</label>
            <select v-model="formData.gender" required>
              <option value="" disabled>Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label>Birthdate</label>
            <input 
              type="date" 
              v-model="formData.birthdate"
              required
            >
          </div>

          <div class="form-actions">
            <button type="button" class="cancel-btn" @click="$router.push('/admin')">Cancel</button>
            <button type="submit" class="submit-btn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
      <div class="modal-content">
        <img :src="zoomedImage" alt="Zoomed profile" class="zoomed-image">
        <button class="close-button" @click="closeImageModal">&times;</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'AdminProfile',
  data() {
    return {
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId')
      },
      formData: {
        name: '',
        email: '',
        phone_number: '',
        gender: '',
        birthdate: '',
        profile_picture: null
      },
      defaultProfilePic,
      imagePreview: null,
      showImageModal: false,
      zoomedImage: null
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
        this.formData.profile_picture = file;
        const reader = new FileReader();
        reader.onload = e => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    async fetchProfile() {
      try {
        const userId = this.adminProfile.user_id;
        const response = await axios.get(`/admin/fetch-account/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.formData = {
            name: userData.name,
            email: userData.email,
            phone_number: userData.phone_number || '',
            gender: userData.gender || '',
            birthdate: userData.birthdate || '',
            profile_picture: null
          };
          
          // Update localStorage and component data
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
          this.adminName = userData.name;
          this.adminProfile.profile_picture = userData.profile_picture;
        }
      } catch (error) {
        console.error('Error fetching profile:', error);
        alert('Failed to load profile data');
      }
    },

    async handleSubmit() {
      try {
        const formData = new FormData();
        
        // Add all form fields to FormData
        formData.append('name', this.formData.name);
        formData.append('email', this.formData.email);
        formData.append('phone_number', this.formData.phone_number || '');
        formData.append('gender', this.formData.gender || '');
        formData.append('birthdate', this.formData.birthdate || '');

        // Only append profile picture if it's a new file
        if (this.formData.profile_picture instanceof File) {
          formData.append('profile_picture', this.formData.profile_picture);
        }

        // Add performed_by field for activity tracking
        const adminId = localStorage.getItem('userId');
        formData.append('performed_by', adminId);

        const userId = this.adminProfile.user_id;
        const response = await axios.post(`/admin/update-profile/${userId}`, 
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          }
        );

        if (response.data.status === 'success') {
          // Update localStorage with new values
          localStorage.setItem('userName', this.formData.name);
          if (response.data.data.profile_picture) {
            localStorage.setItem('profilePicture', response.data.data.profile_picture);
          }

          // Update component data
          this.adminName = this.formData.name;
          this.adminProfile.profile_picture = response.data.data.profile_picture;

          alert('Profile updated successfully');
          await this.fetchProfile(); // Refresh the profile data
        } else {
          throw new Error(response.data.message || 'Failed to update profile');
        }
      } catch (error) {
        console.error('Error updating profile:', error);
        if (error.response) {
          console.error('Server response:', error.response.data);
        }
        alert(error.response?.data?.message || 'Failed to update profile');
      }
    },

    openImageModal(image) {
      this.zoomedImage = image;
      this.showImageModal = true;
    },

    closeImageModal() {
      this.showImageModal = false;
    }
  },
  created() {
    this.fetchProfile();
  }
}
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

/* Profile Image Section */
.profile-image-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 3rem;
  padding: 2rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.profile-preview {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1.5rem;
  border: 4px solid #794646;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.profile-preview:hover {
  transform: scale(1.05);
}

.image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.upload-btn {
  background: #794646;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 600;
  font-size: 0.9rem;
}

.upload-btn:hover {
  background: #693c3c;
  transform: translateY(-2px);
}

.file-input {
  display: none;
}

/* Form Groups */
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
  transition: all 0.2s ease;
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

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.cancel-btn,
.submit-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.2s ease;
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
  transform: translateY(-1px);
}

.submit-btn:hover {
  background: #693c3c;
  transform: translateY(-1px);
}

/* Navbar Styles */
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
  border: 1px solid #ddd;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .form-container {
    padding: 1.5rem;
  }

  .profile-preview {
    width: 150px;
    height: 150px;
  }

  .form-group input,
  .form-group select {
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

  .profile-section {
    margin-right: 1rem;
  }

  .admin-profile-name {
    display: none;
  }
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
  max-height: 90%;
  background: none;
}

.zoomed-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
  background: none;
  box-shadow: none;
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
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.close-button:hover {
  color: #ddd;
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