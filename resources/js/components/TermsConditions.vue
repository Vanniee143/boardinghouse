<template>
  <div>
    <!-- Navbar -->
    <div class="navbar">
      <router-link to="/">
        <img src="@/assets/images/image.png" alt="logo" class="logo" />
      </router-link>
      <router-link to="/" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>
    </div>

    <!-- Main Content -->
    <div class="terms-container">
      <div class="terms-content">
        <h4 class="helper">TERMS & CONDITIONS</h4>
        
        <div class="terms-section">
          <div class="section-card">
            <h5>1. Acceptance of Terms</h5>
            <p>
              By accessing and using SBH BOOKING, you agree to be bound by these Terms & Conditions. 
              If you do not agree with any part of these terms, please do not use our services.
            </p>
          </div>

          <div class="section-card">
            <h5>2. User Responsibilities</h5>
            <ul>
              <li>Provide accurate and complete information during registration</li>
              <li>Maintain the confidentiality of your account credentials</li>
              <li>Use the platform responsibly and legally</li>
              <li>Respect other users and property owners</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>3. Booking and Payments</h5>
            <ul>
              <li>All bookings are subject to availability</li>
              <li>Payment terms are set by individual property owners</li>
              <li>Cancellation policies may vary by property</li>
              <li>Users must honor their booking commitments</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>4. Privacy and Data</h5>
            <p>
              We respect your privacy and handle your data in accordance with our Privacy Policy. 
              By using our services, you consent to our data collection and usage practices.
            </p>
          </div>

          <div class="section-card">
            <h5>5. Platform Rules</h5>
            <ul>
              <li>No fraudulent or misleading activities</li>
              <li>No harassment or discriminatory behavior</li>
              <li>No unauthorized commercial use</li>
              <li>Respect intellectual property rights</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TermsConditions'
}
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: #F5F2F2;
  display: flex;
  align-items: center;
  padding: 0 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.logo {
  margin-left: 15px;
}

.terms-container {
  padding: 1rem;
  margin-left: 0;
  width: 100%;
  max-width: 1200px;
  margin: 70px auto 0;
}

.helper {
  font-family: inter;
  color: #794646;
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 2rem;
}

.terms-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.section-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.section-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.section-card h5 {
  color: #794646;
  font-size: 1.3rem;
  margin-bottom: 1.5rem;
  font-weight: 700;
}

.section-card p {
  color: #666;
  line-height: 1.6;
  font-size: 1rem;
}

.section-card ul {
  color: #666;
  line-height: 1.6;
  font-size: 1rem;
  padding-left: 1.5rem;
}

.section-card li {
  margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
  .terms-container {
    padding: 1rem;
    margin-top: 60px;
  }

  .navbar {
    padding: 0 1rem;
  }

  .logo {
    height: 32px;
  }
}
</style> 