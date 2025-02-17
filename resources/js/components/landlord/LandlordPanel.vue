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
        <h4 class="helper">LANDLORD DASHBOARD</h4>
        <p class="landlord-name">Welcome, {{ landlordName }}!</p>
      </div>

      <div class="dashboard-grid">
        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">🏠</div>
            <div class="card-title">Boarding Houses</div>
            <div class="card-subtitle">Total Properties</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalBoardingHouses }}</div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/boarding-houses" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Properties</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-bed"></i>
            </div>
            <div class="card-title">Rooms</div>
            <div class="card-subtitle">Total Rooms</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalRooms }}</div>
            <div class="stats">
              <div class="stat">
                <span class="stat-label">Available</span>
                <span class="stat-value">{{ availableRooms }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Occupied</span>
                <span class="stat-value">{{ occupiedRooms }}</span>
              </div>
              <div class="stat">
                <span class="stat-label">Maintenance</span>
                <span class="stat-value">{{ maintenanceRooms }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/rooms" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Rooms</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">📝</div>
            <div class="card-title">Bookings</div>
            <div class="card-subtitle">Active Bookings</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalBookings }}</div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/bookings" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Bookings</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">⭐</div>
            <div class="card-title">Reviews</div>
            <div class="card-subtitle">Total Reviews</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalReviews }}</div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/reviews" class="action-link">
              <img src="@/assets/images/Edit.png" alt="View" class="action-icon">
              <span>View Reviews</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">💰</div>
            <div class="card-title">Revenue</div>
            <div class="card-subtitle">{{ currentMonth }}</div>
          </div>
          <div class="card-content">
            <div class="number">₱{{ monthlyRevenue }}</div>
            <div class="revenue-details" v-if="revenueDetails">
              <div class="detail-item">
                <span class="detail-label">Month</span>
                <span class="detail-value">{{ revenueDetails.month }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Year</span>
                <span class="detail-value">{{ revenueDetails.year }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Total Payments</span>
                <span class="detail-value">{{ totalPayments }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Completed</span>
                <span class="detail-value completed">{{ paymentStats.completed || 0 }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Failed</span>
                <span class="detail-value cancelled">{{ paymentStats.failed || 0 }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/revenue" class="action-link">
              <img src="@/assets/images/Edit.png" alt="View" class="action-icon">
              <span>View Details</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">💳</div>
            <div class="card-title">Payments</div>
            <div class="card-subtitle">Payment Methods</div>
          </div>
          <div class="card-content">
            <div class="payment-methods">
              <div class="payment-method">
                <span>Cash</span>
                <div class="status active"></div>
              </div>
              <div class="payment-method">
                <span>GCash QR</span>
                <div class="status" :class="{ active: hasGcashQR }"></div>
              </div>
              <div class="payment-method">
                <span>PayMaya QR</span>
                <div class="status" :class="{ active: hasPaymayaQR }"></div>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/payment-settings" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage QR Codes</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">👥</div>
            <div class="card-title">Tenants</div>
            <div class="card-subtitle">Total Tenants</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalAccounts }}</div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/tenants" class="action-link">
              <img src="@/assets/images/Edit.png" alt="View" class="action-icon">
              <span>View Tenants</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">💸</div>
            <div class="card-title">Payment Management</div>
            <div class="card-subtitle">Payment Status</div>
          </div>
          <div class="card-content">
            <div class="payment-status">
              <div class="status-item">
                <span class="status-label">Pending</span>
                <span class="status-value pending">{{ paymentStats.pending || 0 }}</span>
              </div>
              <div class="status-item">
                <span class="status-label">Completed</span>
                <span class="status-value completed">{{ paymentStats.completed || 0 }}</span>
              </div>
              <div class="status-item">
                <span class="status-label">Failed</span>
                <span class="status-value cancelled">{{ paymentStats.failed || 0 }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/landlord/payment-management" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Payments</span>
            </router-link>
          </div>
        </div>
      </div>

      <div class="recent-activities">
        <h3>Recent Activities</h3>
        <div v-if="recentActivities.length" class="activity-list">
          <div v-for="activity in recentActivities" :key="activity.activity_id" class="activity-item">
            <div class="activity-icon" :class="activity.type">
              <i :class="getActivityIcon(activity.type)"></i>
            </div>
            <div class="activity-content">
              <div class="activity-user">
                <img 
                  :src="getActivityProfilePicture(activity.performer)"
                  :alt="activity.performer?.name || 'User'"
                  class="activity-user-avatar"
                  @error="handleImageError"
                />
                <div class="activity-details">
                  <p class="activity-text">
                    {{ formatActivityDescription(activity) }}
                  </p>
                  <span class="activity-time" :title="activity.timestamp">
                    {{ formatActivityTime(activity.timestamp) }}
                  </span>
                  <span v-if="activity.boarding_house" class="boarding-house-info">
                    {{ activity.boarding_house.name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="no-activities">No recent activities</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'LandlordPanel',
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
      totalBoardingHouses: 0,
      totalRooms: 0,
      availableRooms: 0,
      occupiedRooms: 0,
      totalBookings: 0,
      pendingBookings: 0,
      confirmedBookings: 0,
      totalReviews: 0,
      monthlyRevenue: '0.00',
      totalAccounts: 0,
      recentActivities: [],
      hasGcashQR: false,
      hasPaymayaQR: false,
      maintenanceRooms: 0,
      pendingPayments: 0,
      completedPayments: 0,
      failedPayments: 0,
      isLoadingStats: false,
      statsError: null,
      paymentStats: {
        pending: 0,
        completed: 0,
        failed: 0
      },
      revenueDetails: null,
      currentMonth: '',
      activityInterval: null,
    };
  },
  computed: {
    formattedRevenue() {
      return this.monthlyRevenue ? parseFloat(this.monthlyRevenue).toFixed(2) : '0.00';
    },
    totalPayments() {
      return (this.paymentStats.pending || 0) + 
             (this.paymentStats.completed || 0) + 
             (this.paymentStats.failed || 0);
    }
  },
  methods: {
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

    async fetchLandlordProfile() {
      try {
        const userId = this.landlordProfile.user_id;
        const response = await axios.get(`/landlord/fetch-profile/${userId}`);
        
        if (response.data.status === 'success') {
          const userData = response.data.data;
          this.landlordName = userData.name;
          this.landlordProfile.profile_picture = userData.profile_picture;
          
          localStorage.setItem('userName', userData.name);
          if (userData.profile_picture) {
            localStorage.setItem('profilePicture', userData.profile_picture);
          }
        }
      } catch (error) {
        console.error('Error fetching landlord profile:', error);
      }
    },

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/landlord/login') {
        this.logout();
      } else if (route !== '#') {
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

    async fetchDashboardData() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          console.error('No user ID found');
          this.$router.push('/landlord/login');
          return;
        }

        const [dashboardResponse, paymentStatsResponse] = await Promise.all([
          axios.get('/landlord/dashboard-data', {
            headers: { 'X-User-Id': userId }
          }),
          axios.get('/landlord/payment-stats', {
            headers: { 'X-User-Id': userId }
          })
        ]);

        if (dashboardResponse.data?.status === 'success' && dashboardResponse.data?.data) {
          const data = dashboardResponse.data.data;
          
          this.totalBoardingHouses = data.boarding_houses?.total ?? 0;
          this.totalRooms = data.rooms?.total ?? 0;
          this.availableRooms = data.rooms?.available ?? 0;
          this.occupiedRooms = data.rooms?.occupied ?? 0;
          this.maintenanceRooms = data.rooms?.maintenance ?? 0;
          this.totalBookings = data.bookings?.total ?? 0;
          this.pendingBookings = data.bookings?.pending ?? 0;
          this.confirmedBookings = data.bookings?.confirmed ?? 0;
          this.totalReviews = data.reviews?.total ?? 0;
          this.monthlyRevenue = data.revenue?.formatted ?? '0.00';
          this.totalAccounts = data.accounts?.total ?? 0;

          this.recentActivities = (data.recent_bookings ?? []).map(booking => ({
            activity_id: booking.id ?? Date.now(),
            type: 'booking_added',
            description: `${booking.user?.name || 'Someone'} booked ${booking.room?.room_name || 'a room'}`,
            performer: {
              name: booking.user?.name || 'Unknown User',
              profile_picture: booking.user?.profile_picture || null,
              user_type: booking.user?.user_type || 'user'
            },
            timestamp: booking.created_at,
            boarding_house: booking.boarding_house ?? null
          }));

          this.hasGcashQR = Boolean(data.payment_methods?.gcash);
          this.hasPaymayaQR = Boolean(data.payment_methods?.paymaya);
          
          this.pendingPayments = data.payments?.pending ?? 0;
          this.completedPayments = data.payments?.completed ?? 0;
          this.failedPayments = data.payments?.failed ?? 0;

          this.revenueDetails = {
            month: data.revenue?.month ?? '',
            year: data.revenue?.year ?? '',
            pending_amount: data.revenue?.pending_amount ? 
              parseFloat(data.revenue.pending_amount).toFixed(2) : '0.00',
            total_payments: this.totalPayments
          };
          console.log('Revenue Details:', this.revenueDetails);
          console.log('Raw Revenue Data:', data.revenue);
          this.currentMonth = `${this.revenueDetails.month} ${this.revenueDetails.year}`;
        }

        if (paymentStatsResponse.data?.status === 'success') {
          this.paymentStats = paymentStatsResponse.data.data;
          console.log('Payment Stats:', this.paymentStats);
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        this.resetDashboardData();
      }
    },

    resetDashboardData() {
      this.totalBoardingHouses = 0;
      this.totalRooms = 0;
      this.availableRooms = 0;
      this.occupiedRooms = 0;
      this.maintenanceRooms = 0;
      this.totalBookings = 0;
      this.pendingBookings = 0;
      this.confirmedBookings = 0;
      this.totalReviews = 0;
      this.monthlyRevenue = '0.00';
      this.totalAccounts = 0;
      this.recentActivities = [];
      this.pendingPayments = 0;
      this.completedPayments = 0;
      this.failedPayments = 0;
      this.revenueDetails = null;
      this.currentMonth = '';
    },

    getActivityDescription(activity) {
      if (!activity) return 'Unknown activity';
      
      const description = activity.description || 'No description available';
      
      switch (activity.type) {
        case 'profile_updated':
          return `Updated profile settings`;
        case 'boarding_house_added':
          return `Added new boarding house: ${description}`;
        case 'boarding_house_updated':
          return `Updated boarding house: ${description}`;
        case 'room_added':
          return `Added new room: ${description}`;
        case 'room_updated':
          return `Updated room: ${description}`;
        case 'booking_added':
          return `New booking received: ${description}`;
        case 'booking_updated':
          return `Updated booking: ${description}`;
        case 'review_received':
          return `Received a new review: ${description}`;
        default:
          return description;
      }
    },

    getActivityIcon(type) {
      const icons = {
        account_created: 'fas fa-user-plus',
        account_updated: 'fas fa-user-edit',
        boarding_house_added: 'fas fa-home',
        boarding_house_updated: 'fas fa-edit',
        room_added: 'fas fa-door-open',
        room_updated: 'fas fa-door-closed',
        booking_created: 'fas fa-calendar-plus',
        booking_updated: 'fas fa-calendar-check',
        payment_received: 'fas fa-money-bill-wave',
        review_added: 'fas fa-star',
        profile_updated: 'fas fa-user-edit',
        login: 'fas fa-sign-in-alt',
        logout: 'fas fa-sign-out-alt'
      };
      return icons[type] || 'fas fa-info-circle';
    },

    async fetchRecentActivities() {
      try {
        const userId = localStorage.getItem('userId');
        if (!userId) {
          console.error('No user ID found');
          return;
        }

        const response = await axios.get('/landlord/recent-activities', {
          headers: {
            'X-User-Id': userId,
            'Accept': 'application/json'
          }
        });

        if (response.data.status === 'success' && Array.isArray(response.data.data)) {
          this.recentActivities = response.data.data.map(activity => ({
            activity_id: activity.activity_id || Date.now(),
            type: activity.type || 'default',
            description: activity.description || '',
            created_at: activity.created_at || new Date().toISOString(),
            timestamp: activity.timestamp || new Date().toISOString(),
            performer: {
              name: activity.performer?.name || 'System',
              profile_picture: activity.performer?.profile_picture || this.defaultProfilePic,
              user_type: activity.performer?.user_type || 'system'
            },
            boarding_house: activity.boarding_house || null
          }));
        } else {
          console.error('Invalid response format:', response.data);
          this.recentActivities = [];
        }
      } catch (error) {
        console.error('Error fetching activities:', error);
        if (error.response) {
          console.error('Response data:', error.response.data);
          console.error('Response status:', error.response.status);
        }
        this.recentActivities = [];
      }
    },

    startActivityPolling() {
      // Clear any existing interval
      if (this.activityInterval) {
        clearInterval(this.activityInterval);
      }

      // Initial fetch
      this.fetchRecentActivities();

      // Set up polling interval
      this.activityInterval = setInterval(() => {
        this.fetchRecentActivities();
      }, 30000); // Poll every 30 seconds
    },

    formatDate(date) {
      if (!date) return 'No date available';
      
      try {
        const options = { timeZone: 'Asia/Manila' };
        const now = new Date().toLocaleString('en-US', options);
        const activityDate = new Date(date).toLocaleString('en-US', options);
        
        if (isNaN(new Date(activityDate).getTime())) {
          return 'Invalid date';
        }
        
        const nowDate = new Date(now);
        const actDate = new Date(activityDate);
        
        const diffInMs = nowDate - actDate;
        const diffInSeconds = Math.floor(diffInMs / 1000);
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        const diffInHours = Math.floor(diffInMinutes / 60);
        const diffInDays = Math.floor(diffInHours / 24);

        if (diffInSeconds < 60) {
          return 'Just now';
        }
        if (diffInMinutes < 60) {
          return `${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''} ago`;
        }
        if (diffInHours < 24) {
          return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`;
        }
        if (diffInDays < 7) {
          return `${diffInDays} day${diffInDays !== 1 ? 's' : ''} ago`;
        }

        return new Date(activityDate).toLocaleString('en-US', {
          timeZone: 'Asia/Manila',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });
      } catch (error) {
        console.error('Error formatting date:', error);
        return 'Date error';
      }
    },

    async fetchPaymentQRStatus() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get('/landlord/payment-qr-codes', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          const qrCodes = response.data.data;
          this.hasGcashQR = qrCodes.some(qr => qr.payment_type === 'gcash');
          this.hasPaymayaQR = qrCodes.some(qr => qr.payment_type === 'paymaya');
        }
      } catch (error) {
        console.error('Error fetching QR code status:', error);
      }
    },

    async fetchPaymentStats() {
      try {
        const userId = localStorage.getItem('userId');
        console.log('Fetching payment stats for user:', userId);
        
        const response = await axios.get('/landlord/payment-stats', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        console.log('Payment stats response:', response.data);
        
        if (response.data.status === 'success') {
          this.paymentStats = response.data.data;
          console.log('Updated payment stats:', this.paymentStats);
        }
      } catch (error) {
        console.error('Error fetching payment stats:', error);
      }
    },

    formatActivityDescription(activity) {
      if (!activity) return '';
      
      const description = activity.description || '';
      const performer = activity.performer?.name || 'System';
      
      switch (activity.type) {
        case 'boarding_house_added':
          return `${performer} added a new boarding house: ${description}`;
        case 'room_added':
          return `${performer} added a new room: ${description}`;
        case 'booking_created':
          return `${performer} created a new booking: ${description}`;
        case 'payment_received':
          return `${performer} received a payment: ${description}`;
        case 'profile_updated':
          return `${performer} updated their profile`;
        default:
          return description;
      }
    },

    formatActivityTime(timestamp) {
      if (!timestamp) return '';
      
      const date = new Date(timestamp);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      const diffInMinutes = Math.floor(diffInSeconds / 60);
      const diffInHours = Math.floor(diffInMinutes / 60);
      const diffInDays = Math.floor(diffInHours / 24);

      if (diffInSeconds < 60) return 'Just now';
      if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
      if (diffInHours < 24) return `${diffInHours}h ago`;
      if (diffInDays < 7) return `${diffInDays}d ago`;

      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    getActivityProfilePicture(performer) {
      if (!performer || !performer.profile_picture) {
        return this.defaultProfilePic;
      }
      return performer.profile_picture;
    }
  },
  created() {
    const isLandlord = localStorage.getItem('userType') === 'landlord';
    const hasToken = localStorage.getItem('token') === 'true';
    const userId = localStorage.getItem('userId');
    
    if (!isLandlord || !hasToken || !userId) {
      console.log('Not authenticated, redirecting to login');
      this.$router.push('/landlord/login');
      return;
    }

    this.fetchDashboardData();
    this.fetchPaymentQRStatus();
    this.fetchPaymentStats();
    this.startActivityPolling();
  },
  beforeDestroy() {
    if (this.activityInterval) {
      clearInterval(this.activityInterval);
    }
  }
};
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

/* Additional styles specific to LandlordPanel */
.recent-activities {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 1200px;
  margin: 2rem auto;
}

.recent-activities h3 {
  color: #794646;
  font-size: 1.25rem;
  margin-bottom: 1rem;
  font-weight: 600;
}

.no-activities {
  text-align: center;
  color: #666;
  padding: 2rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.activity-item:hover {
  background: #f0f0f0;
  transform: translateX(5px);
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
  font-size: 1.2rem;
  transition: transform 0.2s ease;
}

/* Activity type-specific colors */
.activity-icon.account_created,
.activity-icon.account_updated {
  background: #3b82f6; /* Blue */
}

.activity-icon.boarding_house_added,
.activity-icon.boarding_house_created,
.activity-icon.boarding_house_updated {
  background: #10b981; /* Green */
}

.activity-icon.room_added,
.activity-icon.room_created,
.activity-icon.room_updated {
  background: #f59e0b; /* Amber */
}

.activity-icon.booking_created,
.activity-icon.booking_updated,
.activity-icon.booking_cancelled {
  background: #8b5cf6; /* Purple */
}

.activity-icon.payment_received,
.activity-icon.payment_updated,
.activity-icon.payment_cancelled {
  background: #14b8a6; /* Teal */
}

.activity-icon.review_added,
.activity-icon.review_updated,
.activity-icon.review_deleted {
  background: #f97316; /* Orange */
}

.activity-icon.profile_updated {
  background: #06b6d4; /* Cyan */
}

.activity-icon.revenue,
.activity-icon.revenue_updated {
  background: #14b8a6; /* Teal */
}

.activity-icon.tenant_added,
.activity-icon.tenant_updated,
.activity-icon.tenant_removed {
  background: #8b5cf6; /* Purple */
}

.activity-icon.login,
.activity-icon.logout {
  background: #6366f1; /* Indigo */
}

.activity-icon.default {
  background: #6b7280; /* Gray */
}

.activity-content {
  flex-grow: 1;
}

.activity-user {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.activity-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #f0f0f0;
}

.activity-details {
  flex: 1;
}

.activity-text {
  margin: 0;
  line-height: 1.4;
  color: #374151;
}

.user-name {
  font-weight: 600;
  color: #374151;
}

.user-type {
  font-size: 0.85em;
  color: #666;
  margin-left: 0.5rem;
  text-transform: capitalize;
}

.activity-time {
  color: #666;
  font-size: 0.85em;
  display: block;
  margin-top: 0.25rem;
}

/* Add hover effect */
.activity-item:hover .activity-icon {
  transform: scale(1.1);
}

/* Animation for new activities */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.activity-item {
  animation: slideIn 0.3s ease-out;
}

/* Add these styles to position the profile section */
.profile-section {
  margin-left: auto;  /* This pushes the profile to the right */
  margin-right: 2rem; /* Add some space before the menu */
  display: flex;
  align-items: center;
}

.landlord-profile {
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

.landlord-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

/* Adjust menu container to not use margin-left: auto */
.menu-container {
  margin-left: 0;
}

.stats {
  display: flex;
  justify-content: space-around;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-label {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.stat-value {
  color: #794646;
  font-weight: 600;
  font-size: 1.25rem;
}

.card-content {
  padding: 1rem;  
}

.number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #794646;
  text-align: center;
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem;
}

.payment-method {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.status {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #dc3545;
}

.status.active {
  background: #198754;
}

.payment-method span {
  font-weight: 500;
  color: #333;
}

/* Update the card styles to match */
.dashboard-card:nth-child(6) { /* Payment card */
  background: linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%);
}

.dashboard-card:nth-child(6) .card-title {
  color: #3b82f6;
}

.dashboard-card:nth-child(6) .action-link {
  color: #3b82f6;
}

.dashboard-card:nth-child(6) .action-link:hover {
  background-color: rgba(59, 130, 246, 0.1);
}

.dashboard-card:nth-child(7) { /* Accounts card */
  background: linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%);
}

.dashboard-card:nth-child(7) .card-title {
  color: #4f46e5;
}

.dashboard-card:nth-child(7) .action-link {
  color: #4f46e5;
}

.dashboard-card:nth-child(7) .action-link:hover {
  background-color: rgba(79, 70, 229, 0.1);
}

/* Add these styles for Font Awesome icon */
.card-icon .fas {
  font-size: 1.5rem;
  color: #794646;
}

.payment-status {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.5rem;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.status-label {
  font-weight: 500;
  color: #333;
}

.status-value {
  font-weight: 600;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
}

.status-value.pending {
  background-color: #fff3cd;
  color: #856404;
}

.status-value.completed {
  background-color: #d4edda;
  color: #155724;
}

.status-value.cancelled {
  background-color: #f8d7da;
  color: #721c24;
}

/* Add this to match the styling of other cards */
.dashboard-card:nth-child(8) { /* Payment Management card */
  background: linear-gradient(145deg, #ffffff 0%, #fff5f5 100%);
}

.dashboard-card:nth-child(8) .card-title {
  color: #dc2626;
}

.dashboard-card:nth-child(8) .action-link {
  color: #dc2626;
}

.dashboard-card:nth-child(8) .action-link:hover {
  background-color: rgba(220, 38, 38, 0.1);
}

/* Add these new styles */
.revenue-details {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e9ecef;
}

.detail-item:last-child {
  border-bottom: none;
}

.detail-label {
  color: #6c757d;
  font-size: 0.875rem;
}

.detail-value {
  color: #794646;
  font-weight: 600;
}

/* Update existing card styles */
.dashboard-card:nth-child(5) { /* Revenue card */
  background: linear-gradient(145deg, #ffffff 0%, #fff5f5 100%);
}

.dashboard-card:nth-child(5) .card-title {
  color: #794646;
}

.dashboard-card:nth-child(5) .action-link {
  color: #794646;
}

.dashboard-card:nth-child(5) .action-link:hover {
  background-color: rgba(121, 70, 70, 0.1);
}

.detail-value.pending {
  color: #856404;
  background-color: #fff3cd;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.detail-value.completed {
  background-color: #d4edda;
  color: #155724;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.detail-value.cancelled {
  background-color: #f8d7da;
  color: #721c24;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

/* Add these styles if they don't exist */
.boarding-house-info {
  font-size: 0.85em;
  color: #666;
  display: block;
  margin-top: 0.25rem;
}

.activity-time {
  color: #666;
  font-size: 0.85em;
  display: block;
  margin-top: 0.25rem;
}

.activity-text {
  margin: 0;
  line-height: 1.4;
  color: #374151;
}

.activity-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #f0f0f0;
}

.activity-details {
  flex: 1;
  margin-left: 1rem;
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