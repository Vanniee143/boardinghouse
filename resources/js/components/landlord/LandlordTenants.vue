<template>
  <div class="landlord-panel">
    <div class="navbars">
      <div class="nav-left">
        <router-link to="/landlord/dashboard" class="logo-link">
          <img src="@/assets/images/image.png" alt="logo" class="logos">
        </router-link>
        <router-link to="/landlord/dashboard" class="nl">
          <h5>SBH BOOKING</h5>
        </router-link>
      </div>

      <div class="nav-right">
        <div class="profile-section">
          <div class="landlord-profile">
            <img 
              :src="getProfilePicture(landlordProfile.profile_picture)"
              alt="profile" 
              class="profile-icon"
              @error="handleImageError"
            />
            <span class="landlord-profile-name">{{ landlordName }}</span>
          </div>
        </div>

        <div class="menu-container">
          <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg">
          <select class="Menu" @change="navigateMenu">
            <option value="#" disabled selected hidden>Menu</option>
            <option value="/landlord/profile">Profile Settings</option>
            <option value="/landlord/login">Logout</option>
          </select>
        </div>
      </div>
    </div>

    <div class="overall">
      <div class="home">
        <h4 class="helper">Tenants</h4>
      </div>

      <div class="tenants-container">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          Loading tenants...
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
          {{ error }}
        </div>

        <!-- Empty State -->
        <div v-else-if="tenants.length === 0" class="empty-state">
          No tenants found
        </div>

        <!-- Tenants List -->
        <div v-else class="tenants-grid">
          <div v-for="tenant in tenants" :key="tenant.user_id" class="tenant-card">
            <div class="tenant-header">
              <img 
                :src="getTenantImage(tenant.profile_picture)"
                :alt="tenant.name"
                class="tenant-avatar"
                @error="handleImageError"
              />
              <div class="tenant-info">
                <h3 class="tenant-name">{{ tenant.name }}</h3>
                <p class="tenant-contact">{{ tenant.phone_number || 'No contact' }}</p>
                <p class="tenant-email">{{ tenant.email }}</p>
              </div>
            </div>

            <div class="booking-details">
              <h4>Current Booking</h4>
              <p><strong>Room:</strong> {{ tenant.current_booking?.room_name || 'None' }}</p>
              <p><strong>Status:</strong> 
                <span :class="['booking-status', tenant.current_booking?.status]">
                  {{ tenant.current_booking?.status || 'No active booking' }}
                </span>
              </p>
              <p v-if="tenant.current_booking">
                <strong>Check-in:</strong> {{ formatDate(tenant.current_booking.check_in_date) }}
              </p>
              <p v-if="tenant.current_booking">
                <strong>Check-out:</strong> {{ formatDate(tenant.current_booking.check_out_date) }}
              </p>
            </div>

            <div class="tenant-stats">
              <div class="stat">
                <span class="stat-label">Total Bookings</span>
                <span class="stat-value">{{ tenant.total_bookings }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Reviews</span>
                <span class="stat-value">{{ tenant.total_reviews }}</span>
              </div>
            </div>

            <div class="tenant-actions">
              <button 
                @click="confirmDelete(tenant)" 
                class="delete-btn"
                title="Delete tenant records"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add confirmation modal -->
    <div v-if="showDeleteModal" class="modal-overlay">
      <div class="delete-modal">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete all records for {{ selectedTenant?.name }}?</p>
        <p class="warning">This will remove all booking records for this tenant.</p>
        <div class="modal-actions">
          <button @click="deleteTenant" class="confirm-delete">Delete</button>
          <button @click="showDeleteModal = false" class="cancel">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'LandlordTenants',
  data() {
    return {
      landlordName: localStorage.getItem('userName') || 'Landlord',
      landlordProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId')
      },
      tenants: [],
      loading: true,
      error: null,
      defaultProfilePic,
      showDeleteModal: false,
      selectedTenant: null
    };
  },
  methods: {
    getProfilePicture(profilePicture) {
      return profilePicture || this.defaultProfilePic;
    },

    getTenantImage(profilePicture) {
      return profilePicture || this.defaultProfilePic;
    },

    handleImageError(e) {
      e.target.src = this.defaultProfilePic;
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/landlord/login') {
        this.logout();
      } else if (route !== '#') {
        this.$router.push(route);
      }
    },

    async fetchTenants() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get(`/landlord/get-tenants/${userId}`);
        
        if (response.data.status === 'success') {
          this.tenants = response.data.data;
        } else {
          throw new Error(response.data.message);
        }
      } catch (error) {
        console.error('Error fetching tenants:', error);
        this.error = 'Failed to load tenants';
      } finally {
        this.loading = false;
      }
    },

    confirmDelete(tenant) {
      this.selectedTenant = tenant;
      this.showDeleteModal = true;
    },

    async deleteTenant() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.delete(
          `/landlord/delete-tenant/${userId}/${this.selectedTenant.user_id}`
        );
        
        if (response.data.status === 'success') {
          // Remove tenant from the list
          this.tenants = this.tenants.filter(
            t => t.user_id !== this.selectedTenant.user_id
          );
          this.showDeleteModal = false;
          this.selectedTenant = null;
          alert('Tenant records deleted successfully');
        }
      } catch (error) {
        console.error('Error deleting tenant:', error);
        alert(error.response?.data?.message || 'Failed to delete tenant');
      }
    }
  },
  async created() {
    await this.fetchTenants();
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

.overall {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  transform: translateY(70px);
}

.home {
  margin-bottom: 2rem;
}

.helper {
  color: #794646;
  font-size: 24px;
  font-weight: 700;
}

.tenants-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tenants-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.tenant-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #eee;
  transition: transform 0.2s, box-shadow 0.2s;
}

.tenant-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tenant-header {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f3f4f6;
}

.tenant-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #794646;
}

.tenant-info {
  flex: 1;
}

.tenant-name {
  color: #374151;
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.tenant-contact,
.tenant-email {
  color: #6b7280;
  font-size: 0.95rem;
  margin: 0.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tenant-contact i,
.tenant-email i {
  color: #794646;
  width: 16px;
}

.booking-details {
  padding: 1.25rem;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.booking-details h4 {
  color: #794646;
  font-size: 1.1rem;
  margin: 0 0 1rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.booking-details p {
  margin: 0.75rem 0;
  font-size: 0.95rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.booking-details strong {
  color: #4b5563;
}

.booking-status {
  padding: 0.35rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: capitalize;
}

.booking-status.pending {
  background: #fef3c7;
  color: #d97706;
}

.booking-status.confirmed {
  background: #d1fae5;
  color: #059669;
}

.booking-status.cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.tenant-stats {
  display: flex;
  justify-content: space-around;
  padding: 1rem 0.5rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.stat {
  text-align: center;
  flex: 1;
  padding: 0.5rem;
}

.stat:first-child {
  border-right: 1px solid #e5e7eb;
}

.stat-label {
  display: block;
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  color: #794646;
  font-weight: 600;
  font-size: 1.25rem;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
  font-size: 1.1rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-state img {
  width: 48px;
  height: 48px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .tenants-grid {
    grid-template-columns: 1fr;
  }

  .tenant-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }

  .tenant-info {
    width: 100%;
  }

  .booking-details p {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .tenant-stats {
    flex-direction: column;
    gap: 1rem;
  }

  .stat:first-child {
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
  }
}

/* Updated Navbar Styles */
.navbars {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 65px;
  background: #F5F2F2;
  padding: 0 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.nav-left {
  display: flex;
  align-items: center;
}

.nav-right {
  display: flex;
  align-items: center;
}
/* Profile Section */
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .navbars {
    padding: 0 1rem;
  }

  .nav-right {
    gap: 0.5rem;
  }

  .landlord-profile-name {
    display: none;
  }

  .profile-section {
    margin-right: 1rem;
  }

  .nl h5 {
    font-size: 1.1rem;
  }

  .Menu {
    padding: 0.5rem;
    font-size: 0.875rem;
  }
}

@media (max-width: 480px) {
  .logos {
    height: 32px;
  }

  .profile-icon {
    width: 32px;
    height: 32px;
  }

  .menu-container {
    margin-left: 0.5rem;
  }
}

.tenant-actions {
  display: flex;
  justify-content: flex-end;
  padding: 0.5rem;
}

.delete-btn {
  background: #fee2e2;
  color: #dc2626;
  border: none;
  padding: 0.5rem;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
}

.delete-btn:hover {
  background: #fecaca;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.delete-modal {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 400px;
}

.delete-modal h3 {
  color: #dc2626;
  margin-bottom: 1rem;
}

.delete-modal .warning {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.confirm-delete {
  background: #dc2626;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
}

.cancel {
  background: #e5e7eb;
  color: #374151;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  cursor: pointer;
}

.confirm-delete:hover {
  background: #b91c1c;
}

.cancel:hover {
  background: #d1d5db;
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