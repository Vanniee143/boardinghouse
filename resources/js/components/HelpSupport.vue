<template>
    <div>
      <div class="navbar">
        <router-link to="/"><img src="@/assets/images/image.png" alt="logo" class="logo" /></router-link>
        <router-link to="/" class="nl"><h5>SBH BOOKING</h5></router-link>
        <div class="login-container">
          <router-link to="/login"><img src="@/assets/images/login.png" alt="login" class="loginlogo" /></router-link>
          <router-link to="/login" class="login">Login</router-link>
        </div>

        <div class="sign-container">
          <router-link to="/register"><img src="@/assets/images/login.png" alt="sign" class="signlogo" /></router-link>          
          <router-link to="/register" class="sign">Sign Up</router-link>
        </div>

        <div class="menu-container">
          <img src="@/assets/images/Menu.png" alt="menu" class="menuimg" />
          <select class="Menu" v-model="menu" @change="navigateMenu">
            <option value="#" disabled selected hidden>Menu</option>
            <option value="/recently_searched">Recently Searched</option>
            <option value="/help_support">Help & Support</option>
          </select>
        </div>
      </div>

      <!-- Add sidebar back -->
      <div class="sidebar">
        <ul>
          <li><router-link to="/" class="back-link">← &nbsp;Back</router-link></li>
          <li><router-link to="/recently_searched" class="recent">Recently Searched</router-link></li>
          <li><router-link to="/help_support" class="help">Help & Support</router-link></li>
        </ul>
      </div>

      <div class="account-container">
        <div class="personal-info">
          <h4 class="helper">Help & Support</h4>
          <div class="help-header">
            <img src="@/assets/images/Help circle.png" alt="Help" class="help-icon">
            <p class="tex">How can we help you?</p>
          </div>

          <div class="help-content">
            <p class="texts">
              To be able to quickly ask us a question you have to 
              <router-link to="/login" class="logins">log in</router-link> or 
              <router-link to="/register" class="logins">create an account</router-link> if you
              don't have one yet.
            </p>
            <p class="texts">
              No matter what, you can still try sending us your inquiry on our help center.
            </p>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  data() {
    return {
      menu: "#",
    };
  },
  methods: {
    navigateMenu() {
      if (this.menu) this.$router.push(this.menu);
    },
  },
  mounted() {
    // Refresh content every minute (60000 milliseconds)
    this.refreshInterval = setInterval(this.refreshContent, 60000);
  },
  beforeUnmount() {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
  },
  methods: {
    refreshContent() {
      // Reload any dynamic content
      this.loadFAQs();
      this.loadSupportInfo();
    },
    loadFAQs() {
      // Load FAQ data
      // Implementation depends on your data source
    },
    loadSupportInfo() {
      // Load support information
      // Implementation depends on your data source
    }
  }
};
</script>

<style scoped>
@import '@/assets/css/help.css';
.logo{
    margin-left: 15px;
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







  






