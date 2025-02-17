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
    <div class="privacy-container">
      <div class="privacy-content">
        <h4 class="helper">PRIVACY NOTICE</h4>
        
        <div class="privacy-section">
          <div class="section-card">
            <h5>1. Information We Collect</h5>
            <ul>
              <li>Personal information (name, email, contact number)</li>
              <li>Account credentials and authentication data</li>
              <li>Booking history and preferences</li>
              <li>Device and browser information</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>2. How We Use Your Information</h5>
            <ul>
              <li>To process your bookings and transactions</li>
              <li>To improve our services and user experience</li>
              <li>To communicate important updates and notifications</li>
              <li>To ensure platform security and prevent fraud</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>3. Information Sharing</h5>
            <p>
              We share your information only with:
            </p>
            <ul>
              <li>Property owners for booking purposes</li>
              <li>Service providers who assist our operations</li>
              <li>Legal authorities when required by law</li>
              <li>Third parties with your explicit consent</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>4. Data Security</h5>
            <ul>
              <li>Implementation of industry-standard security measures</li>
              <li>Regular security audits and updates</li>
              <li>Encrypted data transmission and storage</li>
              <li>Strict access controls and monitoring</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>5. Your Privacy Rights</h5>
            <ul>
              <li>Right to access your personal data</li>
              <li>Right to correct or update your information</li>
              <li>Right to request data deletion</li>
              <li>Right to withdraw consent at any time</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PrivacyNotice'
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

.privacy-container {
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

.privacy-section {
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
  margin-bottom: 1rem;
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
  .privacy-container {
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