<template>
  <div>
    <!-- Navbar -->
    <div class="navbars">
      <router-link to="/"><img src="@/assets/images/image.png" alt="logo" class="logo"></router-link>
      <router-link to="/" class="nl"><h5>SBH BOOKING</h5></router-link>
      <div class="sign-container">
        <router-link to="/register"><img src="@/assets/images/login.png" alt="sign" class="signlogo"></router-link>
        <router-link to="/register" class="sign">Sign Up</router-link>
      </div>
      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimg">
        <select class="Menu" @change="navigateToMenu($event)">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/recently_searched">Recently Searched</option>
          <option value="/help_support">Help & Support</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <!-- Left side content -->
      <div class="contain">
        <p class="title">SBH BOOKING</p>
        <img src="@/assets/images/con.png" alt="con" class="conimg">
        <div class="infors">
          <p class="info">Login to:</p>
          <p class="info1">
            ✓ See more boarding houses at lower costs.<br><br>
            ✓ Retrieve your searches from any device.
          </p>
        </div>
      </div>

      <!-- Login Form -->
      <div class="inputs">
        <form @submit.prevent="login">
          <div v-if="error" class="error-message">{{ error }}</div>
          <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

          <p class="text">Email</p>
          <input 
            v-model="formData.email" 
            type="email" 
            placeholder="Enter Email" 
            required 
            class="input1s"
          >

          <p class="text">Password</p>
          <div class="password-input-container">
            <input 
              v-model="formData.password" 
              :type="showPassword ? 'text' : 'password'" 
              placeholder="Enter Password" 
              required 
              class="input1s"
            >
            <span class="password-toggle" @click="showPassword = !showPassword">
            <svg v-if="showPassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
            <svg v-else class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </span>
          </div>

          <button type="submit" class="input2">Log In</button>
          
          <a href="#" class="text2" @click.prevent="handleForgotPassword">
            Forgot Password?
          </a>

          <p class="text1">
            Don't have an account yet? 
            <router-link to="/register" class="a">Sign Up</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const error = ref(null)
const successMessage = ref(null)
const showPassword = ref(false)

const formData = ref({
email: '',
password: '',
userType: 'user'
})

// Setup axios defaults
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Initialize CSRF token
const initCsrf = async () => {
  try {
    // Get CSRF cookie from Laravel
    await axios.get('/sanctum/csrf-cookie')
    
    // Get the token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.content
    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token
    }
  } catch (error) {
    console.error('CSRF initialization error:', error)
  }
}

onMounted(() => {
  initCsrf()
})

// Handle login form submission
const login = async () => {
try {
  error.value = null
  successMessage.value = null

  const response = await axios.post('/login', formData.value)
  console.log('Login response:', response.data)

  // Check if login was successful based on the updated controller response
  if (response.data.status === 'success') {
    successMessage.value = "Login successful! Redirecting..."

    // Save user information based on the updated response structure
    const userData = response.data.data
    localStorage.setItem('userName', userData.userName)
    localStorage.setItem('userId', userData.userId)
    localStorage.setItem('userType', userData.userType)
    localStorage.setItem('token', 'true')
    localStorage.setItem('profilePicture', userData.profile_picture)
    localStorage.setItem('userEmail', userData.email)
    localStorage.setItem('userPhone', userData.phone_number)
    localStorage.setItem('userGender', userData.gender)
    localStorage.setItem('userBirthdate', userData.birthdate)
    
    console.log('Stored in localStorage:', {
      userName: localStorage.getItem('userName'),
      userId: localStorage.getItem('userId'),
      userType: localStorage.getItem('userType'),
      token: localStorage.getItem('token')
    })

    // Redirect to user homepage after success
    setTimeout(() => {
      router.push('/user/home')
    }, 2000)
  } else {
    throw new Error(response.data.message || 'Login failed')
  }
} catch (err) {
  console.error('Login error:', err)
  error.value = err.response?.data?.message || 'Invalid email or password'
}
}

// Handle forgot password functionality
const handleForgotPassword = () => {
alert('Forgot password functionality will be implemented soon')
}

// Handle menu navigation
const navigateToMenu = (event) => {
const route = event.target.value
if (route !== '#') {
  router.push(route)
}
}
</script>

<style scoped>
@import '@/assets/css/login.css';

.password-input-container {
  position: relative;
  width: 100%;
}

.logo{
    margin-left: 15px;
}

.password-toggle {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translate(-500%,-50%);
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.error-message {
  width: 74%;
  background: #fde8e8;
  color: #dc2626;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
}

.success-message {
  width: 74%;
  background: #f0fdf4;
  color: #16a34a;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
}
</style> 