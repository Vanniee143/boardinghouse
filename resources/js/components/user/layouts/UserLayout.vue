<template>
  <div>
    <!-- Navbar -->
    <div class="navbar">
      <div class="left-section">
        <router-link to="/user/home" class="logo-link">
          <img src="@/assets/images/image.png" alt="logo" class="logo">
        </router-link>
        <router-link to="/user/home" class="nl">
          <h5>SBH BOOKING</h5>
        </router-link>
        <div v-if="isLoggedIn" class="profile-section">
          <div class="user-profile">
            <img 
              :src="getProfilePicture(userProfile.profile_picture)"
              alt="profile" 
              class="profile-icon"
              @error="handleImageError"
              @load="handleImageLoad"
            />
            <span class="user-profile-name">{{ userName }}</span>
          </div>
        </div>
      </div>

      <div class="right-section">
        <NotificationBell />
        <div class="menu-container">
          <img src="@/assets/images/Menu.png" alt="menu" class="menuimg">
          <select class="Menu" @change="navigate">
            <option value="#" disabled selected hidden>Menu</option>
            <option value="/user/account">Your Account</option>
            <option value="/user/recently_searched">Recently Searched</option>
            <option value="/user/help">Help & Support</option>
            <option value="/logout">Logout</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
      <ul>
        <li><router-link to="/user/home" class="back-link">← &nbsp;Back</router-link></li>
        <li><router-link to="/user/account" class="recent">Your Account</router-link></li>
        <li><router-link to="/user/recently_searched" class="recent">Recently Searched</router-link></li>
        <li><router-link to="/user/help" class="help">Help & Support</router-link></li>
      </ul>
    </div>

    <!-- Main Content -->
    <slot></slot>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';
import NotificationBell from '@/components/shared/NotificationBell.vue'

export default {
  name: 'UserLayout',
  components: {
    NotificationBell
  },
  data() {
    return {
      isLoggedIn: false,
      userName: '',
      defaultProfilePic,
      userProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
        email: localStorage.getItem('userEmail'),
        phone_number: localStorage.getItem('userPhone'),
        gender: localStorage.getItem('userGender'),
        birthdate: localStorage.getItem('userBirthdate')
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

    async fetchUserProfile() {
      try {
        const userId = this.userProfile.user_id;
        if (!userId) {
          throw new Error('No user ID found');
        }
        const response = await axios.get(`/user/fetch-profile/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.userName = userData.name;
          this.userProfile.profile_picture = userData.profile_picture;
          
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching user profile:', error);
      }
    },

    checkAuth() {
      const token = localStorage.getItem('token')
      const userId = localStorage.getItem('userId')
      const userName = localStorage.getItem('userName')
      
      if (token && userId && userName) {
        this.isLoggedIn = true
        this.userName = userName
        this.fetchUserProfile()
      } else {
        this.handleLogout()
      }
    },

    navigate(event) {
      const route = event.target.value;
      if (route === '/logout') {
        this.handleLogout();
      } else {
        this.$router.push(route);
      }
    },

    async handleLogout() {
      try {
        await axios.post('/logout');
        // Clear all auth data
        localStorage.clear();
        delete axios.defaults.headers.common['Authorization'];
        
        this.isLoggedIn = false;
        this.userName = '';
        this.userProfile = {
          profile_picture: null,
          user_id: null,
          email: null,
          phone_number: null,
          gender: null,
          birthdate: null
        };
        
        // Redirect to login
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout error:', error);
        this.$router.push('/login');
      }
    }
  },
  created() {
    this.checkAuth()
  },
  mounted() {
    window.addEventListener('storage', (e) => {
      if (e.key === 'token' || e.key === 'userName' || e.key === 'userId') {
        this.checkAuth()
      }
    })
  },
  beforeUnmount() {
    window.removeEventListener('storage', this.checkAuth)
  }
}
</script>

<style scoped>
@import '@/assets/css/user_your_account.css';

.left-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.profile-section {
  margin-left: 0.5rem;
  display: flex;
  align-items: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.5rem;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.profile-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}

.user-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.logo-link.router-link-active,
.brand-link.router-link-active {
  background-color: transparent !important;
  font-weight: normal !important;
}

.logo-link:hover,
.brand-link:hover {
  background-color: transparent !important;
  text-decoration: none !important;
}

.right-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-left: auto;
}

.notification-container {
  margin: 0 0.5rem;
}

.menu-container {
  display: flex;
  align-items: center;
}

@media (max-width: 768px) {
  .right-section {
    gap: 0.5rem;
  }

  .notification-container {
    margin: 0 0.25rem;
  }
}
</style> 


