<template>
  <div>
    <!-- Navbar - Following the same pattern as other components -->
    <div class="navbar">
      <router-link to="/">
        <img src="@/assets/images/image.png" alt="logo" class="logo" />
      </router-link>
      <router-link to="/" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>
    </div>

    <!-- Main Content -->
    <div class="about-container">
      <div class="about-content">
        <h4 class="helper">ABOUT SBH BOOKING</h4>
        
        <div class="about-section">
          <div class="section-card team-section">
            <h5>Our Team</h5>
            <div class="team-grid">
              <div class="team-member">
                <div class="member-image">
                  <img src="@/assets/images/clarence.jpg" alt="Clarence Angelo D. Lisondra" />
                </div>
                <i class="fas fa-code"></i>
                <h6>Clarence Angelo D. Lisondra</h6>
                <p class="role">Developer & Programmer</p>
                <p class="description">Beginner Developer & Programmer specializing in web applications and system architecture.</p>
              </div>
              <div class="team-member">
                <div class="member-image">
                  <img src="@/assets/images/ivan.jpg" alt="Ivann M. Mendoza" />
                </div>
                <i class="fas fa-paint-brush"></i>
                <h6>Ivann M. Mendoza</h6>
                <p class="role">Designer</p>
                <p class="description">Beginner Designer focused on creating intuitive and engaging user interfaces.</p>
              </div>
            </div>
          </div>

          <div class="mission-vision">
            <div class="section-card">
              <h5>Our Mission</h5>
              <p>
                To provide a seamless and reliable platform connecting students with quality 
                boarding houses in Surigao City, making the search for accommodation easier, 
                safer, and more efficient.
              </p>
            </div>

            <div class="section-card">
              <h5>Our Vision</h5>
              <p>
                To be the leading boarding house booking platform in Surigao City, 
                revolutionizing how students find their ideal living spaces while 
                pursuing their education.
              </p>
            </div>
          </div>

          <div class="section-card">
            <h5>Who We Are</h5>
            <p>
              SBH BOOKING is Surigao City's premier boarding house booking system, 
              dedicated to helping students find comfortable and affordable accommodation. 
              Our platform bridges the gap between property owners and students, 
              creating a trusted community for safe and convenient housing solutions.
            </p>
          </div>

          <div class="section-card">
            <h5>Our Values</h5>
            <div class="values-grid">
              <div class="value-item">
                <i class="fas fa-shield-alt"></i>
                <h6>Trust & Safety</h6>
                <p>We prioritize the security and well-being of our users.</p>
              </div>
              <div class="value-item">
                <i class="fas fa-handshake"></i>
                <h6>Reliability</h6>
                <p>Consistent and dependable service for all users.</p>
              </div>
              <div class="value-item">
                <i class="fas fa-users"></i>
                <h6>Community</h6>
                <p>Building strong connections between landlords and students.</p>
              </div>
              <div class="value-item">
                <i class="fas fa-heart"></i>
                <h6>Care</h6>
                <p>Dedicated support for a positive booking experience.</p>
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
  name: 'AboutUs',
  data() {
    return {
      menu: "#"
    };
  },
  methods: {
    navigateMenu() {
      if (this.menu) this.$router.push(this.menu);
    }
  }
};
</script>

<style scoped>
/* Update navbar styles to match other components */
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

/* Add logo positioning */
.logo {
  margin-left: 15px;
}

/* Update container positioning */
.about-container {
  padding: 1rem;
  margin-left: 0; /* Remove sidebar margin */
  width: 100%;
  max-width: 1200px;
  margin: 70px auto 0;
}

/* Update router link styles */
.router-link-active.logo-link,
.router-link-exact-active.logo-link,
.router-link-active.nl,
.router-link-exact-active.nl {
  background-color: transparent !important;
  text-decoration: none !important;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
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

/* Update responsive styles */
@media (max-width: 768px) {
  .about-container {
    padding: 1rem;
    margin-top: 60px;
  }

  .navbar {
    padding: 0 1rem;
  }

  .logo {
    height: 32px;
  }

  .nl h5 {
    font-size: 1.1rem;
  }
}

.helper {
  font-family: inter;
  color: #794646;
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 2rem;
}

.about-section {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.mission-vision {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2.5rem;
  margin-bottom: 2.5rem;
}

.section-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.section-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
}

.section-card h5 {
  color: #794646;
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  font-weight: 700;
  text-align: center;
  position: relative;
  padding-bottom: 1rem;
  user-select: none;
}

.section-card h5::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background-color: #794646;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.section-card:hover h5::after {
  width: 120px;
}

.section-card p {
  color: #666;
  line-height: 1.6;
  text-align: center;
  font-size: 1.1rem;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.value-item {
  text-align: center;
  padding: 2rem 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.value-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.value-item i {
  font-size: 2.5rem;
  color: #794646;
  margin-bottom: 1.5rem;
}

.value-item h6 {
  color: #794646;
  font-size: 1.25rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.value-item p {
  color: #666;
  font-size: 1rem;
  line-height: 1.5;
}

.team-section {
  margin-top: 2rem;
}

.team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

.team-member {
  text-align: center;
  padding: 2.5rem 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.team-member:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.team-member i {
  font-size: 2.5rem;
  color: #794646;
  margin-bottom: 1.5rem;
}

.team-member h6 {
  color: #794646;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.team-member .role {
  color: #333;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.team-member .description {
  color: #666;
  font-size: 0.95rem;
  line-height: 1.5;
}

.member-image {
  width: 150px;
  height: 150px;
  margin: 0 auto 1.5rem;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #794646;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.member-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.team-member:hover .member-image img {
  transform: scale(1.1);
}

/* Add responsive styles */
@media (max-width: 768px) {
  .team-grid {
    grid-template-columns: 1fr;
  }
}
</style>