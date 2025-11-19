<template>
  <div>
    <div class="navbars">
      <router-link to="/admin/admin_register" class="logo-link">
        <img src="@/assets/images/image.png" alt="logo" class="logo hover-effect">
      </router-link>
      <router-link to="/admin/admin_login" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>
    </div>

    <div class="overall">
      <div class="inputs">
        <div class="image-section">
          <img src="@/assets/images/cons.png" alt="con" class="conimg hover-scale">
          <div class="infors">
            <p class="info">Login to:</p>
            <p class="info1">
              ✓ Manage boarding houses and user accounts.<br><br>
              ✓ Access administrative features.
            </p>
          </div>
        </div>

        <form @submit.prevent="login">
          <p class="title">SBH BOOKING</p>
          
          <!-- Success Message -->
          <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
          
          <!-- Error Message -->
          <div v-if="error" class="error-message">{{ error }}</div>
          
          <p class="text">Email</p>
          <input 
            v-model="email" 
            type="email" 
            placeholder="Enter Email" 
            required 
            class="input1"
          >
          
          <p class="text">Password</p>
          <input 
            v-model="password" 
            type="password" 
            placeholder="Enter Password" 
            required 
            class="input1"
          >
          
          <button type="submit" class="input2">Log In</button>
          
          <a href="#" class="text2" @click.prevent="handleForgotPassword">
            Forgot Password?
          </a>

        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { onMounted } from 'vue';

export default {
  name: 'AdminLogin',
  data() {
    return {
      email: '',
      password: '',
      error: null,
      successMessage: null,
    };
  },
  methods: {
    async initCsrf() {
      try {
        // Get CSRF cookie from Laravel
        await axios.get('/sanctum/csrf-cookie');
        
        // Get the token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) {
          axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
      } catch (error) {
        console.error('CSRF initialization error:', error);
      }
    },
    async login() {
      try {
        this.error = null;
        this.successMessage = null;
        
        // Ensure CSRF token is set
        await this.initCsrf();
        
        const response = await axios.post('/admin/admin_login', {
          email: this.email,
          password: this.password,
          userType: 'admin'
        });

        if (response.data.status === 'success' && response.data.data.userType === 'admin') {
          this.successMessage = 'Login successful! Redirecting...';
          
          // Store all user data in localStorage
          localStorage.setItem('userName', response.data.data.userName);
          localStorage.setItem('userId', response.data.data.userId);
          localStorage.setItem('userType', response.data.data.userType);
          localStorage.setItem('token', 'true');
          
          // Store profile data if available
          if (response.data.data.profile_picture) {
            localStorage.setItem('profilePicture', response.data.data.profile_picture);
          }
          
          // Store other profile data
          localStorage.setItem('userEmail', response.data.data.email);
          localStorage.setItem('userPhone', response.data.data.phone_number || '');
          localStorage.setItem('userGender', response.data.data.gender || '');
          localStorage.setItem('userBirthdate', response.data.data.birthdate || '');

          // Redirect to admin panel after a short delay
          setTimeout(() => {
            this.$router.push({ name: 'admin.dashboard' });
          }, 1500);
        } else {
          this.error = 'Unauthorized. This account does not have admin privileges.';
          this.clearStorage();
        }
      } catch (error) {
        console.error('Login error:', error);
        this.error = error.response?.data?.message || 'Login failed. Please try again.';
        this.clearStorage();
      }
    },
    clearStorage() {
      localStorage.removeItem('userName');
      localStorage.removeItem('userId');
      localStorage.removeItem('userType');
      localStorage.removeItem('token');
      localStorage.removeItem('profilePicture');
      localStorage.removeItem('userEmail');
      localStorage.removeItem('userPhone');
      localStorage.removeItem('userGender');
      localStorage.removeItem('userBirthdate');
    },
    handleForgotPassword() {
      alert('Forgot password functionality will be implemented soon');
    }
  },
  async mounted() {
    await this.initCsrf();
  },
  created() {
    // Clear any existing auth data when loading login page
    this.clearStorage();
    
    // Check if user is already logged in as admin
    const isAdmin = localStorage.getItem('userType') === 'admin';
    const hasToken = localStorage.getItem('token') === 'true';
    
    if (isAdmin && hasToken) {
      this.$router.push({ name: 'admin.dashboard' });
    }
  }
};
</script>

<style scoped>
@import '@/assets/css/admin_login.css';

/* Success message styles */
.success-message {
  color: green;
  background-color: #e6f4ea;
  padding: 10px;
  border: 1px solid green;
  border-radius: 4px;
  margin-bottom: 1rem;
  text-align: center;
}

/* Error message styles */
.error-message {
  color: red;
  background-color: #fce4e4;
  padding: 10px;
  border: 1px solid red;
  border-radius: 4px;
  margin-bottom: 1rem;
  text-align: center;
}

/* Override imported styles */
.inputs {
  width: 800px; /* Increased width to accommodate both sections */
  display: flex;
  gap: 2rem;
  padding: 2rem;
}

.image-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding-right: 2rem;
  border-right: 1px solid #e0e0e0;
}

form {
  flex: 1;
}

.conimg {
  max-width: 250px;
  margin-bottom: 2rem;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .inputs {
    width: 100%;
    max-width: 800px;
    flex-direction: column;
  }

  .image-section {
    padding-right: 0;
    padding-bottom: 2rem;
    border-right: none;
    border-bottom: 1px solid #e0e0e0;
  }
}

@media (max-width: 480px) {
  .inputs {
    padding: 1rem;
  }
}

/* Hover effect styles */
.hover-effect {
  transition: transform 0.3s ease, filter 0.3s ease;
}

.hover-effect:hover {
  transform: scale(1.05);
  filter: brightness(1.1);
}

.hover-scale {
  transition: transform 0.4s ease-in-out;
}

.hover-scale:hover {
  transform: scale(1.08);
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