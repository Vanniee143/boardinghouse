<template>
    <div class="admin-accounts">
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
        <div class="home">
          <h4 class="helper">ADD USER</h4>
        </div>
        <div class="form-container">
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label>Full Name</label>
              <input 
                type="text" 
                v-model="formData.name" 
                required
                placeholder="Enter full name"
              >
            </div>
  
            <div class="form-group">
              <label>Email</label>
              <input 
                type="email" 
                v-model="formData.email" 
                required
                placeholder="Enter email address"
              >
            </div>
  
            <div class="form-group">
              <label>Password</label>
              <input 
                type="password" 
                v-model="formData.password" 
                required
                placeholder="Enter password"
              >
            </div>
  
            <div class="form-group">
              <label>User Type</label>
              <select v-model="formData.userType" required>
                <option value="" disabled>Select user type</option>
                <option value="user">User</option>
                <option value="landlord">Landlord</option>
                <option value="admin">Admin</option>
              </select>
            </div>
  
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="$router.push('/admin/accounts')">Cancel</button>
              <button type="submit" class="submit-btn">Add User</button>
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
    name: 'AddUser',
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
          email: '',
          password: '',
          userType: ''
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
          await axios.post('/admin/logout');
          localStorage.clear();
          delete axios.defaults.headers.common['Authorization'];
          this.$router.push('/admin/admin_login');
        } catch (error) {
          console.error('Logout error:', error);
          this.$router.push('/admin/admin_login');
        }
      },
  
      async handleSubmit() {
        try {
          const adminId = localStorage.getItem('userId');
          
          const userData = {
            name: this.formData.name,
            email: this.formData.email,
            password: this.formData.password,
            userType: this.formData.userType,
            created_by: adminId,
            updated_by: adminId
          };

          const response = await axios.post('/admin/add-user', userData);

          if (response.data.status === 'success') {
            alert('User added successfully');
            this.$router.push('/admin/accounts');
          }
        } catch (error) {
          console.error('Error adding user:', error);
          alert(error.response?.data?.message || 'Failed to add user');
        }
      }
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
    font-size: 16px;
  }
  
  .form-group input:focus,
  .form-group select:focus {
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