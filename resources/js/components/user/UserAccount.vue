<template>
  <UserLayout>
    <div class="account-container">
      <div class="personal-info">
        <h4 class="helper">Your Account</h4>
        <form @submit.prevent="updateProfile">
          <p class="tex">Personal Information</p>
          
          <div>
            <p class="label">Full Name</p>
            <input type="text" v-model="userData.fullName" placeholder="Full Name" class="inputs">
          </div>

          <div>
            <p class="label">Email</p>
            <input type="email" v-model="userData.email" placeholder="Email" class="inputs">
          </div>

          <div>
            <p class="label">Phone #</p>
            <input type="text" v-model="userData.phoneNo" placeholder="Phone Number" class="inputs">
          </div>

          <div>
            <p class="label">Birthday</p>
            <div class="date-container">
              <img src="@/assets/images/Calendar.png" alt="calendar" class="calendar-icon">
              <input type="date" v-model="userData.birthday" class="date">
            </div>
          </div>

          <div>
            <p class="gender">
              <input 
                type="radio" 
                v-model="userData.gender" 
                id="male" 
                value="Male"
              > Male 
              <input 
                type="radio" 
                v-model="userData.gender" 
                id="female" 
                value="Female"
              > Female
              <input 
                type="radio" 
                v-model="userData.gender" 
                id="other" 
                value="Other"
              > Other
            </p>
          </div>

          <div>
            <img :src="profilePreview || '@/assets/images/profile.png'" alt="profile" class="profile">
            <label for="fileInput" class="custom-file-upload">
              <span>Choose file</span>
              <img src="@/assets/images/upload.png" alt="Upload Icon" class="upload-icon">
            </label>
            <input 
              type="file" 
              id="fileInput" 
              class="file" 
              @change="handleFileUpload"
              accept="image/*"
            >
            <p class="file-name">{{ fileName }}</p>
          </div>
          <button type="submit" class="update">Update</button>
        </form>
      </div>

      <div class="password-change">
        <p class="password">Change Password</p>
        <form @submit.prevent="changePassword">
          <div>
            <p class="labels">Old Password</p>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="passwordData.oldPassword" 
              placeholder="Old Password" 
              class="input"
            >
          </div>

          <div>
            <p class="labels">New Password</p>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="passwordData.newPassword" 
              placeholder="New Password" 
              class="input"
            >
          </div>

          <div>
            <p class="labels">Confirm Password</p>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="passwordData.confirmPassword" 
              placeholder="Confirm Password" 
              class="input"
            >
          </div>
          <button type="submit" class="changes">Change</button>
        </form>
      </div>
    </div>
  </UserLayout>
</template>

<script>
import UserLayout from './layouts/UserLayout.vue'
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'UserAccount',
  components: {
    UserLayout
  },
  data() {
    return {
      isLoggedIn: true,
      userName: localStorage.getItem('userName') || 'User',
      showPassword: false,
      fileName: '',
      profilePreview: null,
      defaultProfilePic,
      userData: {
        fullName: '',
        email: '',
        phoneNo: '',
        birthday: '',
        gender: '',
        profilePicture: null
      },
      passwordData: {
        oldPassword: '',
        newPassword: '',
        confirmPassword: ''
      },
      loading: false,
      error: null
    }
  },
  methods: {
    async fetchUserData() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('User ID not found');
        }

        const response = await axios.get(`/user/fetch-profile/${userId}`);
        if (response.data.status === 'success') {
          const data = response.data.data;
          this.userData = {
            fullName: data.name || '',
            email: data.email || '',
            phoneNo: data.phone_number || '',
            birthday: data.birthdate || '',
            gender: data.gender || '',
            profilePicture: null
          };
          
          if (data.profile_picture) {
            this.profilePreview = data.profile_picture;
          }
        }
      } catch (error) {
        console.error('Error fetching user data:', error);
        alert('Failed to load user data');
      }
    },

    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB limit
          alert('File size should not exceed 2MB');
          event.target.value = '';
          return;
        }
        this.fileName = file.name;
        this.userData.profilePicture = file;
        this.profilePreview = URL.createObjectURL(file);
      }
    },

    async updateProfile() {
      try {
        this.loading = true;
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('User ID not found');
        }

        // First, handle profile picture if it exists
        if (this.userData.profilePicture) {
          const formData = new FormData();
          formData.append('profile_picture', this.userData.profilePicture);
          
          const uploadResponse = await axios.post('/user/profile-picture', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
          
          if (uploadResponse.data.status === 'success') {
            localStorage.setItem('profilePicture', uploadResponse.data.path);
          }
        }

        // Then update other user data
        const response = await axios.put(`/user/profile/${userId}`, {
          name: this.userData.fullName,
          email: this.userData.email,
          phone_number: this.userData.phoneNo,
          birthdate: this.userData.birthday,
          gender: this.userData.gender
        });

        if (response.data.status === 'success') {
          // Update localStorage
          localStorage.setItem('userName', this.userData.fullName);
          localStorage.setItem('userEmail', this.userData.email);
          localStorage.setItem('userPhone', this.userData.phoneNo);
          localStorage.setItem('userGender', this.userData.gender);
          localStorage.setItem('userBirthdate', this.userData.birthday);

          // Emit event for profile update
          window.dispatchEvent(new CustomEvent('profile-updated', {
            detail: {
              name: this.userData.fullName,
              email: this.userData.email,
              profile_picture: this.profilePreview
            }
          }));

          alert('Profile updated successfully');
        }
      } catch (error) {
        console.error('Error updating profile:', error);
        alert('Failed to update profile: ' + (error.response?.data?.message || error.message));
      } finally {
        this.loading = false;
      }
    },

    async changePassword() {
      if (this.passwordData.newPassword !== this.passwordData.confirmPassword) {
        alert('New passwords do not match');
        return;
      }

      try {
        this.loading = true;
        const userId = localStorage.getItem('userId');
        const response = await axios.put(`/user/change-password/${userId}`, {
          old_password: this.passwordData.oldPassword,
          new_password: this.passwordData.newPassword
        });
        
        if (response.data.status === 'success') {
          alert('Password changed successfully');
          this.passwordData = {
            oldPassword: '',
            newPassword: '',
            confirmPassword: ''
          };
        }
      } catch (error) {
        console.error('Error changing password:', error);
        alert('Failed to change password: ' + (error.response?.data?.message || error.message));
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.fetchUserData();
  }
}
</script>

<style scoped>
@import '@/assets/css/user_your_account.css';

.info-section {
  margin-top: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.info-row {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
  padding: 0.5rem;
  background: white;
  border-radius: 4px;
}

.info-label {
  font-weight: 600;
  color: #794646;
  width: 120px;
  flex-shrink: 0;
}

.info-value {
  color: #666;
}

.user-type {
  color: #794646;
  font-size: 0.9em;
  font-style: italic;
  margin-left: 0.5rem;
}
</style>