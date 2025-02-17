<template>
  <div class="landlord-panel">
    <div class="navbars">
      <router-link to="/landlord/dashboard" class="logo-link">
        <img src="@/assets/images/image.png" alt="logo" class="logos">
      </router-link>
      <router-link to="/landlord/dashboard" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>

      <div class="profile-section">
        <div class="landlord-profile">
          <img 
            :src="getProfilePicture(landlordProfile.profile_picture)"
            alt="profile" 
            class="profile-icon"
            @error="handleImageError"
            @load="handleImageLoad"
          />
          <span class="landlord-profile-name">{{ landlordName }}</span>
        </div>
      </div>

      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateMenu">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/landlord/profile">Profile Settings</option>
          <option value="/landlord/login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">REVIEWS</h4>
      </div>

      <div class="reviews-wrapper">
        <div class="filters">
          <select v-model="selectedHouse" class="filter-select">
            <option value="all">All Boarding Houses</option>
            <option v-for="house in boardingHouses" :key="house.id" :value="house.id">
              {{ house.name }}
            </option>
          </select>

          <select v-model="selectedRating" class="filter-select">
            <option value="all">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
          </select>
        </div>

        <div class="reviews-container">
          <div v-if="loading" class="status-container">
            <img src="@/assets/images/loading.gif" alt="Loading" class="status-icon">
            <p class="status-text">Loading reviews...</p>
          </div>

          <div v-else-if="filteredReviews.length === 0" class="status-container">
            <img src="@/assets/images/no-reviews.png" alt="No Reviews" class="status-icon">
            <p class="status-text">No reviews found</p>
            <p class="status-subtext">{{ getEmptyStateMessage() }}</p>
          </div>

          <div v-else class="reviews-grid">
            <div v-for="review in filteredReviews" :key="review.id" class="review-card">
              <div class="review-header">
                <div class="reviewer-info">
                  <div class="reviewer-avatar-container">
                    <img 
                      :src="review.user_avatar || defaultProfilePic" 
                      :alt="review.user_name" 
                      class="reviewer-avatar"
                      @error="handleUserImageError"
                    >
                  </div>
                  <div class="reviewer-details">
                    <div class="reviewer-main-info">
                      <span class="reviewer-name">{{ review.user_name }}</span>
                      <span class="user-type">{{ review.user_type || 'Tenant' }}</span>
                    </div>
                    <div class="reviewer-metadata">
                      <span class="review-date">{{ formatDate(review.created_at) }}</span>
                      <span class="review-location" v-if="review.user_location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ review.user_location }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="review-actions-top">
                  <button @click="deleteReview(review.id)" class="delete-btn">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>

              <div class="review-content">
                <h3 class="room-name">{{ review.room_name }}</h3>
                <p class="house-name">{{ review.house_name }}</p>
                <p class="review-text">{{ review.comment }}</p>
                
                <div class="review-images" v-if="review.images && review.images.length">
                  <div class="image-section" v-for="(image, index) in review.images" :key="index">
                    <img 
                      :src="image" 
                      :alt="`Review image ${index + 1}`"
                      class="review-image"
                      @error="handleImageError"
                      @click="openImage(image)"
                    >
                  </div>
                </div>

                <div class="review-metadata">
                  <div class="stay-duration" v-if="review.stay_duration">
                    <i class="fas fa-clock"></i>
                    <span>Stay Duration: {{ review.stay_duration }}</span>
                  </div>
                  <div class="verified-stay" v-if="review.is_verified">
                    <i class="fas fa-check-circle"></i>
                    <span>Verified Stay</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-if="showImageModal" class="image-modal" @click="closeModal">
    <div class="modal-content">
      <img :src="selectedImage" class="modal-image" alt="Full size image">
      <button class="modal-close" @click="closeModal">×</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'LandlordReviews',
  data() {
    return {
      landlordName: localStorage.getItem('userName') || 'Landlord',
      landlordProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
        email: localStorage.getItem('userEmail'),
        phone_number: localStorage.getItem('userPhone'),
        gender: localStorage.getItem('userGender'),
        birthdate: localStorage.getItem('userBirthdate')
      },
      defaultProfilePic,
      reviews: [],
      boardingHouses: [],
      selectedHouse: 'all',
      selectedRating: 'all',
      loading: true,
      error: null,
      selectedImage: null,
      showImageModal: false
    };
  },
  computed: {
    filteredReviews() {
      if (!Array.isArray(this.reviews)) return [];
      
      return this.reviews.filter(review => {
        const houseMatch = this.selectedHouse === 'all' || review.boarding_house_id === this.selectedHouse;
        const ratingMatch = this.selectedRating === 'all' || review.rating === parseInt(this.selectedRating);
        return houseMatch && ratingMatch;
      });
    }
  },
  methods: {
    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/landlord/login') {
        this.logout();
      } else {
        this.$router.push(route);
      }
    },
    async logout() {
      try {
        await axios.post('/landlord/logout');
        localStorage.clear();
        delete axios.defaults.headers.common['Authorization'];
        this.$router.push('/landlord/login');
      } catch (error) {
        console.error('Logout error:', error);
        this.$router.push('/landlord/login');
      }
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    getEmptyStateMessage() {
      if (this.selectedHouse !== 'all' || this.selectedRating !== 'all') {
        return 'Try adjusting your filters to see more reviews';
      }
      return 'When you receive reviews, they will appear here';
    },
    async fetchReviews() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('No user ID found');
        }

        console.log('Fetching reviews with userId:', userId);

        const response = await axios.get('/landlord/get-reviews', {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-User-Id': userId
          }
        });
        
        console.log('Review response:', response.data);

        if (response.data.status === 'success') {
          this.reviews = (response.data.data || []).map(review => ({
            ...review,
            user_name: review.name || 'Anonymous User',
            user_avatar: review.profile_picture || null,
            user_type: 'Tenant',
            user_location: null,
            is_verified: Boolean(review.is_verified)
          }));

          console.log('Processed reviews:', this.reviews);
          this.loading = false;
        } else {
          throw new Error(response.data.message || 'Failed to fetch reviews');
        }
      } catch (error) {
        console.error('Error fetching reviews:', error);
        this.error = error.response?.data?.message || 'Failed to load reviews';
        this.reviews = [];
        this.loading = false;
        
        if (error.response?.status === 401) {
          this.$router.push('/landlord/login');
        }
      }
    },
    calculateStayDuration(createdAt) {
      const reviewDate = new Date(createdAt);
      const now = new Date();
      const diffInMonths = (now.getFullYear() - reviewDate.getFullYear()) * 12 + 
                          (now.getMonth() - reviewDate.getMonth());
      
      if (diffInMonths < 1) return 'Less than a month';
      if (diffInMonths === 1) return '1 month';
      if (diffInMonths < 12) return `${diffInMonths} months`;
      
      const years = Math.floor(diffInMonths / 12);
      const remainingMonths = diffInMonths % 12;
      
      if (remainingMonths === 0) {
        return years === 1 ? '1 year' : `${years} years`;
      }
      return years === 1 
        ? `1 year ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}` 
        : `${years} years ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}`;
    },
    async fetchBoardingHouses() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('No user ID found');
        }

        const response = await axios.get('/landlord/fetch-boarding-houses', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data || [];
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        this.boardingHouses = [];
      }
    },
    getProfilePicture(profilePicture) {
      if (profilePicture && profilePicture.trim() !== '') {
        return profilePicture;
      }
      return this.defaultProfilePic;
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    handleImageLoad() {
      console.log('Image loaded successfully');
    },

    handleUserImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    async fetchLandlordProfile() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          throw new Error('No user ID found');
        }

        const response = await axios.get(`/landlord/fetch-profile/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.landlordName = userData.name;
          this.landlordProfile = {
            ...this.landlordProfile,
            profile_picture: userData.profile_picture
          };
          
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching landlord profile:', error);
      }
    },
    async deleteReview(reviewId) {
      try {
        if (!confirm('Are you sure you want to delete this review?')) {
          return;
        }

        const response = await axios.post(`/landlord/delete-review/${reviewId}`, {}, {
          headers: {
            'X-User-Id': localStorage.getItem('userId'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        if (response.data.status === 'success') {
          // Remove the review from the list
          this.reviews = this.reviews.filter(review => review.id !== reviewId);
          // Show success message
          alert('Review deleted successfully');
        }
      } catch (error) {
        console.error('Error deleting review:', error);
        alert('Failed to delete review');
      }
    },
    closeModal() {
      this.showImageModal = false;
      this.selectedImage = null;
      document.body.style.overflow = 'auto'; // Restore scrolling
    },
    openImage(image) {
      this.selectedImage = image;
      this.showImageModal = true;
    }
  },
  async created() {
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('userId');
    
    if (!token || !userId) {
      this.$router.push('/landlord/login');
      return;
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    try {
      await Promise.all([
        this.fetchLandlordProfile(),
        this.fetchReviews(),
        this.fetchBoardingHouses()
      ]);
    } catch (error) {
      console.error('Error in created hook:', error);
      if (error.response?.status === 401) {
        this.$router.push('/landlord/login');
      }
    }
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

.reviews-wrapper {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-top: 2rem;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.filter-select {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #374151;
  background-color: white;
  cursor: pointer;
  transition: border-color 0.2s;
}

.reviews-grid {
  display: grid;
  gap: 1.5rem;
}

.review-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1.5rem;
  position: relative;
}

.review-header {
  display: flex;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.reviewer-avatar-container {
  position: relative;
  width: 48px;
  height: 48px;
}

.reviewer-avatar {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #fff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.reviewer-details {
  display: flex;
  flex-direction: column;
}

.reviewer-main-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.reviewer-name {
  font-weight: 600;
  color: #374151;
  font-size: 1.2rem;
}

.user-type {
  font-size: 0.9rem;
  color: #6b7280;
  background: #f3f4f6;
  padding: 0.3rem 0.6rem;
  border-radius: 9999px;
}

.reviewer-metadata {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 1rem;
  color: #6b7280;
}

.review-location {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.review-metadata {
  display: flex;
  gap: 1.5rem;
  margin-top: 1.2rem;
  padding-top: 1.2rem;
  border-top: 1px solid #e5e7eb;
  font-size: 1rem;
  color: #6b7280;
}

.stay-duration,
.verified-stay {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
}

.verified-stay {
  color: #10b981;
}

.verified-stay i {
  font-size: 1.2rem;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
}

.action-btn i {
  font-size: 0.875rem;
}

.rating {
  color: #fbbf24;
  font-size: 1.25rem;
}

.star {
  color: #e5e7eb;
}

.star.filled {
  color: #fbbf24;
}

.room-name {
  color: #794646;
  font-size: 1.4rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.house-name {
  color: #6b7280;
  font-size: 1.1rem;
  margin-bottom: 1.2rem;
}

.review-text {
  color: #374151;
  font-size: 1.2rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.review-actions {
  display: flex;
  gap: 0.75rem;
}

.action-btn {
  flex: 1;
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.action-btn.reply {
  background: #794646;
  color: white;
}

.action-btn.reply:hover {
  background: #5d3535;
}

.action-btn.report {
  background: #fee2e2;
  color: #991b1b;
}

.action-btn.report:hover {
  background: #fecaca;
}

.status-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.status-icon {
  width: 120px;
  height: 120px;
  margin-bottom: 1.5rem;
  opacity: 0.8;
}

.status-text {
  color: #374151;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.status-subtext {
  color: #6b7280;
  font-size: 0.875rem;
}

@media (max-width: 640px) {
  .filters {
    flex-direction: column;
  }

  .reviews-wrapper {
    padding: 1rem;
  }

  .status-container {
    padding: 2rem 1rem;
  }

  .status-icon {
    width: 80px;
    height: 80px;
  }

  .reviewer-metadata {
    flex-direction: column;
    gap: 0.5rem;
  }

  .review-metadata {
    flex-direction: column;
    gap: 0.75rem;
  }
}

/* Profile section styles */
.profile-section {
  margin-left: auto;
  margin-right: 2rem;
  display: flex;
  align-items: center;
}

.landlord-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
}

.profile-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.landlord-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.95rem;
}

.menu-container {
  margin-left: 0;
}

@media (max-width: 768px) {
  .landlord-profile-name {
    display: none;
  }
  
  .profile-section {
    margin-right: 1rem;
  }
}

/* Add these new styles */
.review-actions-top {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.delete-btn {
  background: none;
  border: none;
  color: #ef4444;
  padding: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 50%;
}

.delete-btn:hover {
  background-color: #fee2e2;
  transform: scale(1.1);
}

/* Add modal styles if you want to implement image preview */
.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.modal-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
}

.modal-close {
  position: absolute;
  top: -2rem;
  right: -2rem;
  background: none;
  border: none;
  color: white;
  font-size: 2rem;
  cursor: pointer;
  padding: 0.5rem;
  line-height: 1;
  transition: transform 0.2s;
}

.modal-close:hover {
  transform: scale(1.1);
}

/* Add animation for the modal */
@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-content {
  animation: modalFadeIn 0.2s ease-out;
}

/* Update the review-images styles */
.review-images {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 0.5rem;
  overflow-x: auto;
}

.image-section {
  flex: 0 0 200px;
}

.review-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s;
}

.review-image:hover {
  transform: scale(1.02);
}

/* Add scrollbar styling */
.review-images::-webkit-scrollbar {
  height: 8px;
}

.review-images::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.review-images::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.review-images::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Update these styles */
.review-content-wrapper {
  display: block;
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