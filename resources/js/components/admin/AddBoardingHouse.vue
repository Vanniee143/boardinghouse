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
          <option value="/admin/boarding-houses">Boarding Houses</option>
          <option value="/admin/rooms">Rooms</option>
          <option value="/admin/reviews">Reviews</option>
          <option value="/admin/profile">Profile Settings</option>
          <option value="/admin/admin_login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">{{ isEditing ? 'EDIT BOARDING HOUSE' : 'ADD BOARDING HOUSE' }}</h4>
      </div>
      <div class="form-container">
        <form @submit.prevent="handleSubmit">
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
              <div class="preview-container" v-if="imagePreview">
                <img :src="imagePreview" alt="Preview" class="image-preview">
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
            <button type="button" class="cancel-btn" @click="$router.push('/admin/rooms')">Cancel</button>
            <button type="submit" class="submit-btn">Add Boarding House</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'AddBoardingHouse',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const fileInput = ref(null);
    
    const isEditing = ref(false);
    const formData = ref({
      name: '',
      description: '',
      address: '',
      landlord_name: '',
      landlord_phone: '',
      bh_images: null,
      user_id: localStorage.getItem('userId')
    });
    const imagePreview = ref(null);
    const adminName = ref(localStorage.getItem('userName') || 'Admin');
    const adminProfile = reactive({
      profile_picture: localStorage.getItem('profilePicture'),
      user_id: localStorage.getItem('userId'),
      email: localStorage.getItem('userEmail'),
      phone_number: localStorage.getItem('userPhone'),
      gender: localStorage.getItem('userGender'),
      birthdate: localStorage.getItem('userBirthdate')
    });

    const triggerFileInput = () => {
      if (fileInput.value) {
        fileInput.value.click();
      }
    };

    const handleImageUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        formData.value.bh_images = file;
        imagePreview.value = URL.createObjectURL(file);
      }
    };

    const removeImage = () => {
      formData.value.bh_images = null;
      imagePreview.value = null;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    };

    const handleSubmit = async () => {
      try {
        const formDataToSend = new FormData();
        
        // Append all form fields
        Object.keys(formData.value).forEach(key => {
          if (key === 'bh_images' && formData.value[key]) {
            formDataToSend.append('bh_images', formData.value[key]);
          } else if (key === 'user_id') {
            const userId = localStorage.getItem('userId');
            formDataToSend.append('user_id', userId);
            formDataToSend.append('created_by', userId);
            formDataToSend.append('updated_by', userId);
          } else {
            formDataToSend.append(key, formData.value[key]);
          }
        });

        console.log('Form data being sent:', Object.fromEntries(formDataToSend));

        const response = await axios.post('/admin/add-boarding-house', formDataToSend, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (response.data.status === 'success') {
          alert('Boarding house added successfully');
          router.push('/admin/boarding-houses');
        } else {
          throw new Error(response.data.message || 'Failed to add boarding house');
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        console.error('Error details:', error.response?.data);
        alert(error.response?.data?.message || error.message || 'Failed to add boarding house');
      }
    };

    const navigateMenu = (event) => {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.logout();
      } else if (route !== '#') {
        this.$router.push(route);
      }
    };

    onMounted(async () => {
      const { edit, id } = route.query;
      if (edit && id) {
        isEditing.value = true;
        try {
          const response = await axios.get(`/admin/fetch-boarding-house/${id}`);
          formData.value = response.data;
        } catch (error) {
          console.error('Error loading boarding house:', error);
        }
      }
    });

    const logout = async () => {
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
    };

    const getProfilePicture = (profilePicture) => {
      if (profilePicture && profilePicture.trim() !== '') {
        return profilePicture;
      }
      return defaultProfilePic;
    };

    const handleImageError = (e) => {
      e.target.src = defaultProfilePic;
    };

    const handleImageLoad = () => {
      console.log('Image loaded successfully');
    };

    return {
      isEditing,
      formData,
      handleSubmit,
      navigateMenu,
      adminName,
      adminProfile,
      getProfilePicture,
      handleImageError,
      handleImageLoad,
      imagePreview,
      fileInput,
      triggerFileInput,
      handleImageUpload,
      removeImage
    };
  }
};
</script>

<style scoped>
@import '@/assets/css/add_boarding.css';

.boarding-form {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
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
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-btn,
.submit-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

.cancel-btn {
  background: #ddd;
  color: #333;
}

.submit-btn {
  background: #000000;
  color: white;
}

.cancel-btn:hover {
  background: #ccc;
}

.submit-btn:hover {
  background: #323232;
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

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 120px;
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

.price-range {
  display: flex;
  gap: 1rem;
  width: 97%;
}

.price-input {
  flex: 1;
}

.price-input label {
  display: block;
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 14px;
}

.price-input input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.price-input input:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.form-group input[type="number"] {
  width: 97%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-family: Arial;
  font-size: 16px;
}

.form-group input[type="number"]:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
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
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  transition: background-color 0.2s;
}

.remove-image:hover {
  background: rgba(220, 38, 38, 1);
}

.upload-area {
  border: 2px dashed #794646;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-area:hover {
  background-color: #f3f4f6;
  border-color: #693c3c;
}

.upload-area i {
  font-size: 2rem;
  color: #794646;
  margin-bottom: 1rem;
}

.upload-area p {
  color: #794646;
  margin: 0;
  font-size: 0.875rem;
}

.file-input {
  display: none;
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