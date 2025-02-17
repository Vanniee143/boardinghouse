<template>
  <div>
    <!-- Navbar -->
    <div class="navbar">
      <router-link to="/"><img src="@/assets/images/image.png" alt="logo" class="logo"></router-link>
      <router-link to="/" class="nl"><h5>SBH BOOKING</h5></router-link>
      <div class="sign-container">
        <router-link to="/login"><img src="@/assets/images/login.png" alt="sign" class="signlogo"></router-link>
        <router-link to="/login" class="sign">Login</router-link>
      </div>
      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateToMenu($event)">
          <option value="Menu" disabled selected hidden>Menu</option>
          <option value="/recently_searched">Recently Searched</option>
          <option value="/help_support">Help & Support</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <!-- Main content -->
      <div class="contain">
        <p class="title">SBH BOOKING</p>
        <img src="@/assets/images/con.png" alt="con" class="conimg">

        <div class="infors">
          <p class="info">Register to:</p>
          <p class="info1">
            ✓ See more boarding houses at lower costs.<br><br>
            ✓ Retrieve your searches from any device.
          </p>
        </div>
      </div>

      <!-- Registration Form -->
      <div class="forms">
        <form @submit.prevent="register">
          <div v-if="error" class="error-message">{{ error }}</div>
          <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

          <p class="text">Full Name</p>
          <input 
            v-model="formData.fullName" 
            type="text" 
            placeholder="Full Name" 
            class="email" 
            required 
          />

          <p class="text">Email</p>
          <input 
            v-model="formData.email" 
            type="email" 
            placeholder="Email" 
            class="email" 
            required 
          />

          <p class="text">Password</p>
          <div class="password-field">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="formData.password" 
              placeholder="Password" 
              class="emails" 
              required 
            />
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

          <p class="text">Confirm Password</p>
          <div class="password-field">
            <input 
              :type="showConfirmPassword ? 'text' : 'password'" 
              v-model="formData.confirmPassword" 
              placeholder="Confirm Password" 
              class="emails" 
              required 
            />
            <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
              <svg v-if="showConfirmPassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0="></path>
              </svg>
            </span>
          </div>

          <button type="submit" class="button" :disabled="isSubmitting">
            {{ isSubmitting ? 'Registering...' : 'Register' }}
          </button>

          <p class="text1">
            Already have an account? 
            <router-link to="/login" class="an">Login</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      formData: {
        fullName: '',
        email: '',
        password: '',
        confirmPassword: '',
        userType: 'user',
      },
      error: null,
      successMessage: null,
      isSubmitting: false,
      showPassword: false,
      showConfirmPassword: false,
    };
  },
  methods: {
    async register() {
      if (this.formData.password !== this.formData.confirmPassword) {
        this.error = 'Passwords do not match.';
        return;
      }
      if (this.isSubmitting) return;

      this.isSubmitting = true;
      this.error = null;
      this.successMessage = null;

      try {
        const formData = new FormData();
        formData.append('fullName', this.formData.fullName);
        formData.append('email', this.formData.email);
        formData.append('password', this.formData.password);
        formData.append('userType', this.formData.userType);

        const response = await axios.post('/register', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json',
          },
        });

        if (response.data.user) {
          this.successMessage = response.data.message || 'Registration successful! Please log in.';
          this.formData = {
            fullName: '',
            email: '',
            password: '',
            confirmPassword: '',
            userType: 'user',
          };

          setTimeout(() => {
            this.$router.push('/login');
          }, 2000);
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'An error occurred. Please try again later.';
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>


  <style scoped>
  @import '@/assets/css/register.css';
  .error-message {
    width: 74%;
    background-color: #fde8e8;
    color: #dc2626;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    text-align: center;
  }

  .success-message {
    width: 74%;
    background-color: #def7ec;
    color: #03543f;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    text-align: center;
  }

  .password-field {
    position: relative;
    width: 98%;
  }

  .password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
  }

  .eye-icon {
    transform: translate(-480%, 0%);
    width: 20px;
    height: 20px;
    opacity: 0.6;
    transition: opacity 0.2s;
  }

  .eye-icon:hover {
    opacity: 1;
  }

  /* Adjust input padding to prevent text from going under the icon */
  .password-field input {
    padding-right: 40px;
  }

  .gender-group {
    display: flex;
    gap: 2.5rem;
    margin-bottom: 1.5rem;
    padding: 0.5rem 0;
  }

  .gender-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
  }

  .gender-option input[type="radio"] {
    width: 18px;
    height: 18px;
    accent-color: #794646;
    cursor: pointer;
  }

  .logo{
    margin-left: 15px;
}

  .gender-label {
    color: #666;
    font-size: 16px;
    user-select: none;
  }

  .gender-option:hover .gender-label {
    color: #794646;
  }

  .profile-field {
    position: relative;
    width: 100%;
    margin-bottom: 1.5rem;
    transform: translateY(-15px);
  }

  .profile-upload {
    display: flex;
    align-items: center;
    width: 80%;
    cursor: pointer;
    gap: 1rem;
    transform: translateY(-10px);
  }

  .preview-container {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #794646;
  }

  .image-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .upload-info {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border: 1px solid rgb(190, 189, 189);
    border-radius: 6px;
    background-color: white;
  }

  .upload-info span {
    color: #666;
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .upload-button {
    display: flex;
    align-items: center;
  }

  .upload-icon {
    width: 20px;
    height: 20px;
    opacity: 0.6;
    transition: opacity 0.2s;
  }

  .profile-upload:hover .upload-icon {
    opacity: 1;
  }

  .profile-upload:hover .upload-info {
    border-color: #794646;
  }
  </style>