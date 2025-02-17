<template>
  <div class="admin-reviews">
    <div class="navbars">
      <router-link to="/admin">
        <img src="@/assets/images/image.png" alt="logo" class="logos">
      </router-link>
      <router-link to="/admin" class="nl">
        <h5>SBH BOOKING</h5>
      </router-link>

      <div class="profile-section">
        <div class="admin-profile">
          <img 
            :src="getProfilePicture(adminProfile.profile_picture)"
            alt="profile" 
            class="profile-icon"
            @error="handleImageError"
            @load="handleImageLoad"
          />
          <span class="admin-profile-name">{{ adminName }}</span>
        </div>
      </div>

      <div class="menu-container">
        <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg" id="menuImage">
        <select class="Menu" id="menuSelect" @change="navigateMenu">
          <option value="#" disabled selected hidden>Menu</option>
          <option value="/admin/accounts">Accounts</option>
          <option value="/admin/profile">Profile Settings</option>
          <option value="/admin/admin_login">Logout</option>
        </select>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">ROOM REVIEWS</h4>
      </div>

      <div class="section">
        <div class="empty-message" v-if="!reviews.length">
          No room reviews available
        </div>

        <div class="reviews-container">
          <div class="contains" v-for="review in reviews" :key="review.id">
            <div class="review-header">
              <div class="user-info">
                <img 
                  :src="review.user_profile || defaultProfilePic" 
                  alt="User Profile"
                  class="reviewer-pic"
                  @error="e => e.target.src = defaultProfilePic"
                />
                <div class="reviewer-details">
                  <span class="reviewer">{{ review.user_name }}</span>
                  <span class="date">{{ formatDate(review.created_at) }}</span>
                </div>
              </div>
              <img 
                src="@/assets/images/Trash.png" 
                alt="Trash" 
                class="Trash"
                @click="deleteReview(review.id)"
              >
            </div>

            <div class="review-content">
              <div class="sets">{{ review.room_name }}</div>
              <div class="boarding-house">{{ review.boarding_house_name }}</div>
              
              <div class="rating">
                <span class="stars">
                  <span v-for="n in 5" :key="n" class="star" :class="{ active: n <= review.rating }">★</span>
                </span>
                <span class="rating-text">{{ review.rating }}/5</span>
              </div>

              <div class="comment">{{ review.comment }}</div>

              <div class="review-images" v-if="review.images && review.images.length">
                <img 
                  v-for="(image, index) in review.images" 
                  :key="index"
                  :src="image"
                  alt="Review image"
                  class="review-image"
                  @click="openImageModal(image)"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="selectedImage" class="image-modal" @click="closeImageModal">
      <div class="modal-content">
        <button class="modal-close" @click="closeImageModal">&times;</button>
        <img :src="selectedImage" alt="Full size image" @click.stop>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'Reviews',
  data() {
    return {
      adminName: localStorage.getItem('userName') || 'Admin',
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
        email: localStorage.getItem('userEmail'),
        phone_number: localStorage.getItem('userPhone'),
        gender: localStorage.getItem('userGender'),
        birthdate: localStorage.getItem('userBirthdate')
      },
      defaultProfilePic,
      reviews: [],
      loading: false,
      error: null,
      selectedImage: null,
    }
  },
  methods: {
    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.logout();
      } else if (route !== '#') {
        this.$router.push(route);
      }
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    async deleteReview(id) {
      if (confirm('Are you sure you want to delete this review?')) {
        try {
          const response = await axios.delete(`/admin/delete-review/${id}`);
          if (response.data.status === 'success') {
            await this.fetchReviews();
            alert('Review deleted successfully');
          } else {
            throw new Error(response.data.message || 'Failed to delete review');
          }
        } catch (error) {
          console.error('Error deleting review:', error);
          alert('Failed to delete review. Please try again.');
        }
      }
    },

    async fetchReviews() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/admin/get-reviews');
        if (response.data.status === 'success') {
          this.reviews = response.data.data;
        } else {
          throw new Error(response.data.message || 'Failed to fetch reviews');
        }
      } catch (error) {
        console.error('Error fetching reviews:', error);
        this.error = error.response?.data?.message || 'Failed to load reviews';
        this.reviews = [];
      } finally {
        this.loading = false;
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

    async logout() {
      try {
        await axios.post('/admin/logout');
        localStorage.clear();
        this.$router.push('/admin/admin_login');
      } catch (error) {
        console.error('Logout error:', error);
        this.$router.push('/admin/admin_login');
      }
    },

    openImageModal(imageUrl) {
      this.selectedImage = imageUrl;
      document.body.style.overflow = 'hidden';
    },

    closeImageModal() {
      this.selectedImage = null;
      document.body.style.overflow = 'auto';
    }
  },
  created() {
    // Check authentication
    const isAdmin = localStorage.getItem('userType') === 'admin';
    const hasToken = localStorage.getItem('token') === 'true';
    const userId = localStorage.getItem('userId');
    
    if (!isAdmin || !hasToken || !userId) {
      console.log('Not authenticated, redirecting to login');
      this.$router.push('/admin/admin_login');
      return;
    }

    this.fetchReviews();
  }
};
</script>

<style scoped>
@import '@/assets/css/user_room_review.css';

.overall {
  margin-top: 5%;
  height: calc(100vh - 100px); /* Adjust based on your navbar height */
  overflow: hidden;
}

.section {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  height: calc(100% - 3rem);
}

.reviews-container {
  max-height: calc(100vh - 250px); /* Adjust based on your layout */
  overflow-y: auto;
  padding-right: 10px;
}

.reviews-container::-webkit-scrollbar {
  width: 8px;
}

.reviews-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.reviews-container::-webkit-scrollbar-thumb {
  background: #794646;
  border-radius: 4px;
}

.reviews-container::-webkit-scrollbar-thumb:hover {
  background: #633939;
}

.contains {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  position: relative;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
  position: relative;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  margin-right: 1rem;
}

.reviewer-pic {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #794646;
}

.reviewer-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.reviewer {
  font-size: 1.2rem;
  color: #333;
  font-weight: 600;
}

.date {
  font-size: 0.9rem;
  color: #666;
}

.review-content {
  padding-top: 0.5rem;
}

.sets {
  font-weight: 600;
  color: #794646;
  font-size: 1.3rem;
  margin-bottom: 0.5rem;
}

.boarding-house {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 1.2rem;
}

.rating {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.2rem;
}

.stars {
  display: flex;
  gap: 2px;
}

.star {
  color: #ddd;
  font-size: 1.4rem;
}

.star.active {
  color: #ffd700;
}

.rating-text {
  color: #666;
  font-weight: 500;
  font-size: 1.1rem;
}

.comment {
  color: #333;
  line-height: 1.7;
  margin-bottom: 1.2rem;
  font-size: 1.1rem;
  white-space: pre-line;
}

.review-images {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.review-image {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
  cursor: zoom-in;
  transition: transform 0.2s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.review-image:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.review-footer {
  display: none;
}

/* Add these styles for the empty message */
.empty-message {
  text-align: center;
  color: #666;
  font-style: italic;
  padding: 2rem;
  background: #f8f9fa;
  border-radius: 8px;
}

/* Add back the profile section styles */
.profile-section {
  margin-left: auto;
  margin-right: 2rem;
  display: flex;
  align-items: center;
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.profile-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0;
}

.Trash {
  width: 24px;
  height: 24px;
  cursor: pointer;
  opacity: 0.7;
  transition: all 0.2s ease;
  padding: 4px;
  border-radius: 4px;
  background: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.Trash:hover {
  opacity: 1;
  transform: scale(1.1);
  background: #ffebeb;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Add these new styles for the image modal */
.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  cursor: pointer;
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
  margin: auto;
}

.modal-content img {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 8px;
  cursor: default;
}

.modal-close {
  position: absolute;
  top: -40px;
  right: -40px;
  background: none;
  border: none;
  color: white;
  font-size: 30px;
  cursor: pointer;
  padding: 10px;
  z-index: 1001;
}

.modal-close:hover {
  color: #ddd;
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