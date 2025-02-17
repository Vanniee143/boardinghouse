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
    <div class="legal-container">
      <div class="legal-content">
        <h4 class="helper">LEGAL INFORMATION</h4>
        
        <div class="legal-section">
          <div class="section-card">
            <h5>1. Company Information</h5>
            <p>
              SBH BOOKING operates as a student boarding house booking platform in Surigao City. 
              We are committed to providing transparent and lawful services to all our users.
            </p>
          </div>

          <div class="section-card">
            <h5>2. Intellectual Property Rights</h5>
            <ul>
              <li>All content on SBH BOOKING is protected by copyright laws</li>
              <li>The SBH BOOKING name and logo are registered trademarks</li>
              <li>Users may not reproduce or distribute our content without permission</li>
              <li>Property images remain the property of their respective owners</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>3. Liability Limitations</h5>
            <ul>
              <li>SBH BOOKING acts as an intermediary platform</li>
              <li>We are not responsible for property conditions or disputes</li>
              <li>Users engage with property owners at their own discretion</li>
              <li>We recommend thorough verification before making commitments</li>
            </ul>
          </div>

          <div class="section-card">
            <h5>4. Dispute Resolution</h5>
            <p>
              Any disputes arising from the use of our platform will be resolved through 
              mediation or appropriate legal channels in accordance with Philippine law.
            </p>
          </div>

          <div class="section-card">
            <h5>5. Legal Compliance</h5>
            <ul>
              <li>We comply with all applicable Philippine laws and regulations</li>
              <li>Users must adhere to local housing and tenancy laws</li>
              <li>We cooperate fully with legal authorities when required</li>
              <li>Regular audits ensure ongoing legal compliance</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LegalInformation'
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

.legal-container {
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

.legal-section {
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
  .legal-container {
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