<template>
    <div class="admin-panel">
      <div class="navbars">
        <div class="nav-left">
          <router-link to="/admin" class="logo-link">
            <img src="@/assets/images/image.png" alt="logo" class="logos">
          </router-link>
          <router-link to="/admin" class="nl">
            <h5>SBH BOOKING</h5>
          </router-link>
        </div>
  
        <div class="nav-right">
          <div class="profile-section">
            <div class="admin-profile">
              <img 
                :src="getProfilePicture(adminProfile.profile_picture)"
                alt="profile" 
                class="profile-icon"
                @error="handleImageError"
              />
              <span class="admin-profile-name">{{ adminName }}</span>
            </div>
          </div>
  
          <div class="menu-container">
            <img src="@/assets/images/Menu.png" alt="menu" class="menuimgg">
            <select class="Menu" @change="navigateMenu">
              <option value="#" disabled selected hidden>Menu</option>
              <option value="/admin/accounts">Accounts</option>
              <option value="/admin/profile">Profile Settings</option>
              <option value="/admin/admin_login">Logout</option>
            </select>
          </div>
        </div>
      </div>
  
      <div class="overall">
        <div class="home">
          <h4 class="helper">TENANT MANAGEMENT</h4>
          <p class="subtitle">View and manage all tenant records</p>
        </div>
  
        <div class="controls-container">
          <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search tenant name..."
              class="search-input"
            >
          </div>
          
          <div class="filter-box">
            <select v-model="statusFilter" class="filter-select">
              <option value="all">All Tenants</option>
              <option value="active">Active Bookings</option>
              <option value="inactive">No Active Bookings</option>
            </select>
          </div>
        </div>
  
        <div class="tenants-container">
          <!-- Loading State -->
          <div v-if="loading" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading tenants...</span>
          </div>
  
          <!-- Error State -->
          <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ error }}</span>
          </div>
  
          <!-- Empty State -->
          <div v-else-if="tenants.length === 0" class="empty-state">
            <i class="fas fa-users-slash"></i>
            <span>No tenants found</span>
          </div>
  
          <!-- No Search Results -->
          <div v-else-if="filteredTenants.length === 0" class="no-results">
            <i class="fas fa-search"></i>
            <span>No tenants found matching "{{ searchQuery }}"</span>
            <button @click="clearSearch" class="clear-search">
              Clear Search
            </button>
          </div>
  
          <!-- Tenants Grid -->
          <div v-else class="tenants-grid">
            <div v-for="tenant in filteredTenants" 
                 :key="tenant.id" 
                 class="tenant-card">
              <div class="tenant-header">
                <div class="avatar-container">
                  <img 
                    :src="tenant.profile_picture"
                    :alt="tenant.name"
                    class="tenant-avatar"
                    @error="handleImageError"
                  />
                  <div class="status-indicator" :class="{ 'active': tenant.is_active }"></div>
                </div>
                <div class="tenant-info">
                  <h3 class="tenant-name">{{ tenant.name }}</h3>
                  <div class="contact-info">
                    <p class="tenant-email">
                      <i class="fas fa-envelope"></i>
                      {{ tenant.email }}
                    </p>
                    <p class="tenant-phone">
                      <i class="fas fa-phone"></i>
                      {{ tenant.phone }}
                    </p>
                    <p class="tenant-gender">
                      <i class="fas fa-user"></i>
                      {{ formatGender(tenant.gender) }}
                    </p>
                  </div>
                </div>
              </div>
  
              <div class="tenant-details">
                <div class="stats-grid">
                  <div class="stat-item">
                    <i class="fas fa-history"></i>
                    <span class="stat-label">Total Bookings</span>
                    <span class="stat-value">{{ tenant.total_bookings }}</span>
                  </div>
                  
                  <div class="stat-item" v-if="tenant.active_booking">
                    <i class="fas fa-home"></i>
                    <span class="stat-label">Current Stay</span>
                    <span class="stat-value">{{ tenant.active_booking.boarding_house }}</span>
                  </div>
                </div>
  
                <div class="booking-dates" v-if="tenant.active_booking">
                  <div class="date-item">
                    <span class="date-label">Check In:</span>
                    <span class="date-value">{{ formatDate(tenant.active_booking.check_in) }}</span>
                  </div>
                  <div class="date-item">
                    <span class="date-label">Check Out:</span>
                    <span class="date-value">{{ formatDate(tenant.active_booking.check_out) }}</span>
                  </div>
                </div>
              </div>
  
              <div class="card-actions">
                <button 
                  class="delete-btn" 
                  @click="confirmDelete(tenant)"
                  title="Delete tenant"
                >
                  <i class="fas fa-trash"></i>
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import defaultProfilePic from '@/assets/images/Profile.png';
  import { emitter } from '@/eventBus'
  
  export default {
    name: 'AdminTenants',
    data() {
      return {
        adminName: localStorage.getItem('userName') || 'Admin',
        adminProfile: {
          profile_picture: localStorage.getItem('profilePicture')
        },
        tenants: [],
        loading: true,
        error: null,
        defaultProfilePic,
        searchQuery: '',
        statusFilter: 'all'
      }
    },
    computed: {
      filteredTenants() {
        return this.tenants.filter(tenant => {
          // Apply search filter
          const searchMatch = tenant.name.toLowerCase().includes(this.searchQuery.toLowerCase());
          
          // Apply status filter
          let statusMatch = true;
          if (this.statusFilter === 'active') {
            statusMatch = tenant.total_bookings > 0;
          } else if (this.statusFilter === 'inactive') {
            statusMatch = tenant.total_bookings === 0;
          }
          
          return searchMatch && statusMatch;
        });
      }
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
        if (route === '/admin/admin_login') {
          this.logout();
        } else if (route !== '#') {
          this.$router.push(route);
        }
      },
  
      async fetchTenants() {
        try {
          const userId = localStorage.getItem('userId');
          const response = await axios.get('/admin/tenants/data', {
            headers: {
              'X-User-Id': userId,
              'Accept': 'application/json'
            }
          });
          
          if (response.data.status === 'success') {
            this.tenants = response.data.data.tenants;
          } else {
            throw new Error(response.data.message || 'Failed to fetch tenants');
          }
        } catch (error) {
          console.error('Error fetching tenants:', error);
          this.error = error.response?.data?.message || 'Failed to load tenants';
        } finally {
          this.loading = false;
        }
      },
      clearSearch() {
        this.searchQuery = '';
        this.statusFilter = 'all';
      },
      formatGender(gender) {
        if (!gender) return 'Not specified';
        return gender.charAt(0).toUpperCase() + gender.slice(1);
      },
  
      async confirmDelete(tenant) {
        if (confirm(`Are you sure you want to delete all bookings for tenant ${tenant.name}? This will remove all their bookings and related data.`)) {
          await this.deleteTenant(tenant);
        }
      },
  
      async deleteTenant(tenant) {
        try {
          const response = await axios.delete(`/admin/tenants/${tenant.id}`);
          
          if (response.data.status === 'success') {
            // Update the tenant's booking count to 0
            this.tenants = this.tenants.map(t => {
              if (t.id === tenant.id) {
                return {
                  ...t,
                  total_bookings: 0,
                  is_active: false,
                  active_booking: null
                };
              }
              return t;
            });
            
            // Emit event using emitter
            if (response.data.updated_room_ids) {
              emitter.emit('rooms-status-updated', response.data.updated_room_ids);
            }
            
            alert('Tenant bookings deleted successfully');
          } else {
            throw new Error(response.data.message || 'Failed to delete tenant bookings');
          }
        } catch (error) {
          console.error('Error deleting tenant bookings:', error);
          alert(error.response?.data?.message || 'Failed to delete tenant bookings');
        }
      },
    },
    async created() {
      await this.fetchTenants();
    }
  }
  </script>
  
  <style scoped>
  .overall {
    padding: 2rem;
    min-height: calc(100vh - 60px);
    margin-top: 65px;
    background: #f8f9fa;
  }
  
  .helper {
    color: #794646;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 0.5rem;
  }
  
  .subtitle {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 2rem;
  }
  
  .controls-section {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }
  
  .search-box {
    flex: 1;
    position: relative;
  }
  
  .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
  }
  
  .filter-select {
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #374151;
    min-width: 150px;
  }
  
  .tenants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 1rem;
  }
  
  .tenant-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }
  
  .tenant-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  
  .tenant-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 1rem;
  }
  
  .tenant-avatar {
    width: 80px;
    height: 80px;
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
    margin-bottom: 0.75rem;
  }
  
  .contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .tenant-email,
  .tenant-phone,
  .tenant-gender {
    color: #6b7280;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .tenant-stats {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
  }
  
  .stat {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
  }
  
  .stat-value {
    color: #794646;
    font-size: 1.5rem;
    font-weight: 600;
  }
  
  .fas {
    color: #794646;
  }
  
  .loading-state,
  .error-state,
  .empty-state {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }
  
  .loading-state i,
  .error-state i,
  .empty-state i {
    font-size: 2rem;
    color: #794646;
  }
  
  @media (max-width: 768px) {
    .controls-section {
      flex-direction: column;
    }
    
    .tenants-grid {
      grid-template-columns: 1fr;
    }
    
    .tenant-header {
      flex-direction: column;
      text-align: center;
    }
    
    .contact-info {
      align-items: center;
    }
  }
  
  .navbars {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 68px;
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
  
  .logo-link {
    text-decoration: none;
  }
  
  .logos {
    height: 40px;
    margin-right: 1rem;
  }
  
  .nl {
    text-decoration: none;
  }
  
  .nl h5 {
    color: #794646;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    transform: translate(0%, 10%);
  }
  
  .profile-section {
    margin-right: 2rem;
  }
  
  .admin-profile {
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
  
  .admin-profile-name {
    color: #794646;
    font-weight: 600;
    font-size: 0.95rem;
  }
  
  .menu-container {
    position: relative;
  }
  
  .menuimgg {
    height: 24px;
    margin-right: 0.5rem;
  }
  
  @media (max-width: 768px) {
    .navbars {
      padding: 0 1rem;
    }
  
    .nav-right {
      gap: 0.5rem;
    }
  
    .admin-profile-name {
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
  
  .controls-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    display: flex;
    gap: 1rem;
    align-items: center;
  }
  
  .search-box {
    flex: 1;
    position: relative;
  }
  
  .search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #794646;
  }
  
  .search-input {
    width: 90%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #374151;
  }
  
  .search-input:focus {
    outline: none;
    border-color: #794646;
    box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
  }
  
  .filter-box {
    min-width: 200px;
  }
  
  .filter-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 0.95rem;
    cursor: pointer;
  }
  
  .filter-select:focus {
    outline: none;
    border-color: #794646;
    box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
  }
  
  @media (max-width: 768px) {
    .controls-container {
      flex-direction: column;
      padding: 1rem;
    }
  
    .search-box,
    .filter-box {
      width: 100%;
    }
  }
  
  .no-results {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  }
  
  .no-results i {
    font-size: 2rem;
    color: #794646;
  }
  
  .clear-search {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #794646;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background-color 0.2s;
  }
  
  .clear-search:hover {
    background: #8b5353;
  }
  
  .avatar-container {
    position: relative;
    width: 80px;
    height: 80px;
  }
  
  .status-indicator {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #9ca3af;
    border: 2px solid white;
  }
  
  .status-indicator.active {
    background-color: #10b981;
  }
  
  .tenant-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
  }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
  }
  
  .stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 8px;
    gap: 0.5rem;
  }
  
  .stat-item i {
    font-size: 1.25rem;
    color: #794646;
  }
  
  .stat-label {
    font-size: 0.75rem;
    color: #6b7280;
  }
  
  .stat-value {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
  }
  
  .booking-dates {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    background: #f9fafb;
    padding: 0.75rem;
    border-radius: 8px;
  }
  
  .date-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .date-label {
    font-size: 0.75rem;
    color: #6b7280;
  }
  
  .date-value {
    font-size: 0.875rem;
    color: #374151;
    font-weight: 500;
  }
  
  @media (max-width: 640px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .booking-dates {
      grid-template-columns: 1fr;
    }
  }
  
  .card-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
  }
  
  .delete-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .delete-btn:hover {
    background-color: #dc2626;
  }
  
  .delete-btn i {
    color: white;
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