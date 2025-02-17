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
    <div class="security-container">
      <div class="security-content">
        <h4 class="helper">CYBER SECURITY</h4>
        
        <div class="security-section">
          <div class="section-card">
            <h5>Account Security</h5>
            <ul>
              <li>Use strong, unique passwords</li>
              <li>Enable two-factor authentication when available</li>
              <li>Never share your login credentials</li>
              <li>Log out when using shared devices</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>Safe Browsing Tips</h5>
            <ul>
              <li>Always verify you're on the official SBH BOOKING website</li>
              <li>Look for 'https://' in the website address</li>
              <li>Keep your browser and devices updated</li>
              <li>Use secure and private internet connections</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>Payment Security</h5>
            <ul>
              <li>Only make payments through official channels</li>
              <li>Never send payment information via email or chat</li>
              <li>Keep payment receipts and confirmation numbers</li>
              <li>Report suspicious payment requests immediately</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>Personal Information Protection</h5>
            <ul>
              <li>Share only necessary personal information</li>
              <li>Review privacy settings regularly</li>
              <li>Be cautious with third-party applications</li>
              <li>Monitor your account for suspicious activity</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>Reporting Security Issues</h5>
            <div class="contact-info">
              <p>If you notice any suspicious activity or security concerns, please contact us immediately:</p>
              <p><i class="fas fa-envelope"></i> clisondra@ssct.edu.ph</p>
              <p><i class="fas fa-envelope"></i> imendoza@ssct.edu.ph</p>
              <p><i class="fas fa-phone"></i> +63 946 072 1827</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CyberSecurity'
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

.security-container {
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

.security-section {
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

.section-card ul {
  color: #666;
  line-height: 1.6;
  font-size: 1rem;
  padding-left: 1.5rem;
}

.section-card li {
  margin-bottom: 0.5rem;
}

.contact-info {
  color: #666;
  line-height: 1.6;
}

.contact-info p {
  margin-bottom: 0.5rem;
}

.contact-info i {
  color: #794646;
  width: 20px;
  margin-right: 0.5rem;
}

@media (max-width: 768px) {
  .security-container {
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