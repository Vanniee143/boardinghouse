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
    <div class="personal-container">
      <div class="personal-content">
        <h4 class="helper">DO NOT SELL MY PERSONAL INFORMATION</h4>
        
        <div class="personal-section">
          <div class="section-card">
            <h5>Your Privacy Choices</h5>
            <p>
              At SBH BOOKING, we respect your right to control how your personal information is used. 
              This page explains your rights and how to exercise them regarding the sale or sharing of your personal data.
            </p>
          </div>

          <div class="section-card">
            <h5>What Information We Protect</h5>
            <ul>
              <li>Personal identifiers (name, address, email)</li>
              <li>Contact information</li>
              <li>Booking history and preferences</li>
              <li>Payment information</li>
              <li>Account credentials</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>Your Rights</h5>
            <ul>
              <li>Right to opt-out of personal information sales</li>
              <li>Right to know what personal information is collected</li>
              <li>Right to delete your personal information</li>
              <li>Right to non-discrimination for exercising your rights</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>How to Submit a Request</h5>
            <p>You can submit your request not to sell your personal information by:</p>
            <ul>
              <li>Emailing us at privacy@sbhbooking.com</li>
              <li>Contacting our support team</li>
              <li>Submitting a request through your account settings</li>
              <li>Using the opt-out form below</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>Verification Process</h5>
            <p>
              To protect your privacy and security, we will verify your identity before processing your request. 
              This may require you to:
            </p>
            <ul>
              <li>Provide additional information</li>
              <li>Log into your account</li>
              <li>Verify your email address</li>
              <li>Present valid identification</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PersonalInformation'
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

.personal-container {
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

.personal-section {
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
  .personal-container {
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