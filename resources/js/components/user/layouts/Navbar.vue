<template>
    <div>
      <div class="navbar">
        <router-link to="/user/home" class="logo-link">
          <img src="@/assets/images/image.png" alt="logo" class="logo">
        </router-link>
        <router-link to="/user/home" class="nl">
          <h5>SBH BOOKING</h5>
        </router-link>
  
        <div v-if="isLoggedIn" class="profile-section" style="margin-left: 1rem; margin-right: auto;">
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
  
        <div class="login-container">
          <router-link to="/user/view-booking/:id">
            <img src="@/assets/images/booking.png" alt="book" class="loginlogo">
          </router-link>
          <router-link to="/user/view-booking/:id" class="login">View Bookings</router-link>
        </div>
  
        <div class="sign-container">
          <router-link to="/user/boarding-houses">
            <img src="@/assets/images/bed.png" alt="bed" class="signlogo">
          </router-link>
          <router-link to="/user/boarding-houses" class="sign">Boarding Houses List</router-link>
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
    </div>
</template>
  
<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';
import NotificationBell from '@/components/shared/NotificationBell.vue'

export default {
  name: 'Navbar',
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

.router-link-active.logo-link,
.router-link-exact-active.logo-link,
.router-link-active.nl,
.router-link-exact-active.nl {
  background-color: transparent !important;
  text-decoration: none !important;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
}

.logo{
  transform: translate(0, -5%);
}

.profile-section {
  margin-left: auto;
  margin-right: 1rem;
  display: flex;
  align-items: center;
}

.user-profile {
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

.user-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .user-profile-name {
    display: none;
  }
}

.right-section {
  display: flex;
  align-items: center;
  gap: 1rem;
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
  