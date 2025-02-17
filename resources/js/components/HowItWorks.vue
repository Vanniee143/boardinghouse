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
    <div class="works-container">
      <div class="works-content">
        <h4 class="helper">HOW SBH BOOKING WORKS</h4>
        
        <div class="works-section">
          <div class="section-card">
            <h5>1. Search for Boarding Houses</h5>
            <div class="step-content">
              <i class="fas fa-search"></i>
              <div class="step-details">
                <p>Enter your desired location in Surigao City</p>
                <ul>
                  <li>Use filters to find your perfect match</li>
                  <li>View available rooms and amenities</li>
                  <li>Compare different boarding houses</li>
                  <li>Check real photos and descriptions</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="section-card">
            <h5>2. Create Your Account</h5>
            <div class="step-content">
              <i class="fas fa-user-plus"></i>
              <div class="step-details">
                <p>Sign up to access all features</p>
                <ul>
                  <li>Quick and easy registration process</li>
                  <li>Verify your student status</li>
                  <li>Complete your profile</li>
                  <li>Set your preferences</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="section-card">
            <h5>3. Book Your Room</h5>
            <div class="step-content">
              <i class="fas fa-home"></i>
              <div class="step-details">
                <p>Secure your preferred boarding house</p>
                <ul>
                  <li>Select your desired room</li>
                  <li>Choose your move-in date</li>
                  <li>Review booking details</li>
                  <li>Submit necessary requirements</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="section-card">
            <h5>4. Payment Process</h5>
            <div class="step-content">
              <i class="fas fa-money-bill-wave"></i>
              <div class="step-details">
                <p>Secure and flexible payment options</p>
                <ul>
                  <li>Choose your payment method</li>
                  <li>Review payment terms</li>
                  <li>Receive payment confirmation</li>
                  <li>Get official receipts</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="section-card">
            <h5>5. Move In & Support</h5>
            <div class="step-content">
              <i class="fas fa-key"></i>
              <div class="step-details">
                <p>Start your stay with confidence</p>
                <ul>
                  <li>Get move-in instructions</li>
                  <li>Meet your landlord</li>
                  <li>Access 24/7 support</li>
                  <li>Share your experience</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HowItWorks'
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

.works-container {
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

.works-section {
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

.step-content {
  display: flex;
  gap: 2rem;
  align-items: flex-start;
}

.step-content i {
  font-size: 2.5rem;
  color: #794646;
  flex-shrink: 0;
}

.step-details {
  flex-grow: 1;
}

.step-details p {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 1rem;
  font-weight: 500;
}

.step-details ul {
  color: #666;
  line-height: 1.6;
  font-size: 1rem;
  padding-left: 1.5rem;
}

.step-details li {
  margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
  .works-container {
    padding: 1rem;
    margin-top: 60px;
  }

  .navbar {
    padding: 0 1rem;
  }

  .logo {
    height: 32px;
  }

  .step-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .step-details ul {
    text-align: left;
  }
}
</style> 