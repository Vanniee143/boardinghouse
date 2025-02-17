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
    <div class="help-container">
      <div class="help-content">
        <h4 class="helper">HELP CENTER</h4>
        
        <div class="help-section">
          <div class="section-card">
            <h5>Frequently Asked Questions</h5>
            <div class="faq-list">
              <div class="faq-item">
                <h6>How do I book a boarding house?</h6>
                <p>
                  Search for your desired location, select a boarding house, choose your room, 
                  and follow the booking process. You'll need to create an account if you haven't already.
                </p>
              </div>

              <div class="faq-item">
                <h6>What payment methods are accepted?</h6>
                <p>
                  We accept various payment methods including bank transfers, e-wallets, 
                  and cash payments directly to property owners.
                </p>
              </div>

              <div class="faq-item">
                <h6>How can I cancel my booking?</h6>
                <p>
                  Log into your account, go to your bookings, and follow the cancellation process. 
                  Note that cancellation policies may vary by property.
                </p>
              </div>
            </div>
          </div>

          <div class="section-card">
            <h5>Contact Support</h5>
            <div class="contact-options">
              <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h6>Email Support</h6>
                <p>clisondra@ssct.edu.ph</p>
                <p>imendoza@ssct.edu.ph</p>
                <p>Response time: Within 24 hours</p>
              </div>

              <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h6>Phone Support</h6>
                <p>+63 946 072 1827</p>
                <p>Available: Mon-Fri, 9AM-5PM</p>
              </div>
            </div>
          </div>

          <div class="section-card">
            <h5>Quick Guides</h5>
            <div class="guides-grid">
              <div class="guide-item">
                <i class="fas fa-search"></i>
                <h6>Finding the Right Boarding House</h6>
                <ul>
                  <li>Use filters to narrow your search</li>
                  <li>Compare multiple properties</li>
                  <li>Read reviews from other students</li>
                  <li>Check location and amenities</li>
                </ul>
              </div>

              <div class="guide-item">
                <i class="fas fa-book"></i>
                <h6>Booking Process</h6>
                <ul>
                  <li>Create an account</li>
                  <li>Select your preferred room</li>
                  <li>Submit necessary documents</li>
                  <li>Complete payment</li>
                </ul>
              </div>

              <div class="guide-item">
                <i class="fas fa-user-shield"></i>
                <h6>Safety Tips</h6>
                <ul>
                  <li>Verify property information</li>
                  <li>Keep communication on platform</li>
                  <li>Report suspicious activity</li>
                  <li>Follow safety guidelines</li>
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
  name: 'Help'
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

.help-container {
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

.help-section {
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
  text-align: center;
}

.faq-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.faq-item h6 {
  color: #794646;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.faq-item p {
  color: #666;
  line-height: 1.6;
}

.contact-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  text-align: center;
}

.contact-item {
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.contact-item i {
  font-size: 2rem;
  color: #794646;
  margin-bottom: 1rem;
}

.contact-item h6 {
  color: #794646;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.contact-item p {
  color: #666;
  margin-bottom: 0.5rem;
}

.guides-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.guide-item {
  text-align: center;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.guide-item i {
  font-size: 2rem;
  color: #794646;
  margin-bottom: 1rem;
}

.guide-item h6 {
  color: #794646;
  font-size: 1.1rem;
  margin-bottom: 1rem;
  font-weight: 600;
}

.guide-item ul {
  text-align: left;
  color: #666;
  padding-left: 1.5rem;
}

.guide-item li {
  margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
  .help-container {
    padding: 1rem;
    margin-top: 60px;
  }

  .navbar {
    padding: 0 1rem;
  }

  .logo {
    height: 32px;
  }

  .contact-options,
  .guides-grid {
    grid-template-columns: 1fr;
  }
}
</style> 