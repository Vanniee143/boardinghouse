<template>
  <div class="admin-panel">
    <div class="navbars">
      <router-link to="/admin" class="logo-link">
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
        <h4 class="helper">WELCOME TO ADMIN PANEL</h4>
        <p class="admin-name">Welcome, Admin {{ adminName }}!</p>
      </div>

      <div class="dashboard-grid">
        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">👥</div>
            <div class="card-title">Accounts</div>
            <div class="card-subtitle">Total Accounts</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalAccounts }}</div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/accounts" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Accounts</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">🏠️</div>
            <div class="card-title">Boarding Houses</div>
            <div class="card-subtitle">Total Boarding Houses</div>
          </div>
          <div class="card-content">
            <div class="number">{{ totalBoardingHouses }}</div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/boarding-houses" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Boarding Houses</span>
            </router-link>
          </div>
        </div>

        
        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="card-title">Revenue</div>
            <div class="card-subtitle">Total Revenue</div>
          </div>
          <div class="card-content">
            <div class="number" :class="{ 'loading': !totalRevenue }">
              ₱{{ formatRevenue(totalRevenue) }}
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/revenue" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>View Revenue</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">📅</div>
            <div class="card-title">Bookings</div>
            <div class="card-subtitle">Booking Status</div>
          </div>
          <div class="card-content">
            <div class="stats-breakdown">
              <div class="stat">
                <span class="label">Pending:</span>
                <span class="value pending">{{ pendingBookings }}</span>
              </div>
              <div class="stat">
                <span class="label">Confirmed:</span>
                <span class="value confirmed">{{ confirmedBookings }}</span>
              </div>
              <div class="stat">
                <span class="label">Cancelled:</span>
                <span class="value cancelled">{{ cancelledBookings }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/bookings" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Bookings</span>
            </router-link>
          </div>
        </div>

        <!-- Payment QR Codes Card -->
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
            <router-link to="/admin/payment-settings" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage QR Codes</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">💰</div>
            <div class="card-title">Payment Status</div>
            <div class="card-subtitle">Payment Overview</div>
          </div>
          <div class="card-content">
            <div class="stats-breakdown">
              <div class="stat">
                <span class="label">Pending:</span>
                <span class="value pending">{{ pendingPayments }}</span>
              </div>
              <div class="stat">
                <span class="label">Completed:</span>
                <span class="value completed">{{ completedPayments }}</span>
              </div>
              <div class="stat">
                <span class="label">Failed:</span>
                <span class="value failed">{{ failedPayments }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link :to="{ name: 'admin.payments' }" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Payments</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">🛏️</div>
            <div class="card-title">Rooms</div>
            <div class="card-subtitle">Room Status</div>
          </div>
          <div class="card-content">
            <div class="stats-breakdown">
              <div class="stat">
                <span class="label">Available:</span>
                <span class="value available">{{ availableRooms }}</span>
              </div>
              <div class="stat">
                <span class="label">Occupied:</span>
                <span class="value occupied">{{ occupiedRooms }}</span>
              </div>
              <div class="stat">
                <span class="label">Maintenance:</span>
                <span class="value maintenance">{{ maintenanceRooms }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/rooms" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Rooms</span>
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
            <div class="stats-breakdown">
              <div class="stat">
                <span class="label">Total:</span>
                <span class="value">{{ reviewStats.total }}</span>
              </div>
              <div class="stat">
                <span class="label">Average Rating:</span>
                <span class="value">{{ reviewStats.averageRating }}/5</span>
              </div>
              <div class="stat">
                <span class="label">Positive:</span>
                <span class="value completed">{{ reviewStats.positiveReviews }}</span>
              </div>
              <div class="stat">
                <span class="label">Negative:</span>
                <span class="value failed">{{ reviewStats.negativeReviews }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/reviews" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Reviews</span>
            </router-link>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <div class="card-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="card-title">Tenants</div>
            <div class="card-subtitle">Active Tenants</div>
          </div>
          <div class="card-content">
            <div class="stats-breakdown">
              <div class="stat">
                <span class="label">Total:</span>
                <span class="value">{{ totalTenants }}</span>
              </div>
              <div class="stat">
                <span class="label">Active:</span>
                <span class="value active">{{ activeTenants }}</span>
              </div>
            </div>
          </div>
          <div class="card-actions">
            <router-link to="/admin/tenants" class="action-link">
              <img src="@/assets/images/Edit.png" alt="Manage" class="action-icon">
              <span>Manage Tenants</span>
            </router-link>
          </div>
        </div>

      </div>

      <!-- Add Recent Activities Section -->
      <div class="recent-activities">
        <h3>Recent Activities</h3>
        <div v-if="recentActivities.length" class="activity-list">
          <div v-for="activity in recentActivities" :key="activity.activity_id" class="activity-item">
            <div class="activity-icon" :class="activity.type">
              <i :class="getActivityIcon(activity.type)"></i>
            </div>
            <div class="activity-content">
              <div class="activity-user">
                <div v-if="activity.performer.name === 'System'" class="activity-user-avatar system-avatar">
                  <i class="fas fa-cog"></i>
                </div>
                <img 
                  v-else
                  :src="activity.performer.profile_picture" 
                  :alt="activity.performer.name"
                  class="activity-user-avatar"
                  @error="handleImageError"
                />
                <div class="activity-details">
                  <p class="activity-text">
                    <span class="user-name">{{ activity.performer.name }}</span>
                    <span v-if="activity.performer.user_type" class="user-type">({{ activity.performer.user_type }})</span>
                    {{ activity.description }}
                  </p>
                  <span class="activity-time" :title="activity.timestamp">
                    {{ activity.created_at }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="no-activities">No recent activities</p>
      </div>

      <!-- Add this section after the recent activities section -->
      <div class="support-requests">
        <h3>Support Requests</h3>
        <div class="support-stats">
          <div class="stat-card">
            <div class="stat-title">Total Requests</div>
            <div class="stat-value">{{ supportStats.total }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-title">Pending</div>
            <div class="stat-value pending">{{ supportStats.pending }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-title">In Progress</div>
            <div class="stat-value in-progress">{{ supportStats.inProgress }}</div>
          </div>
          <div class="stat-card">
            <div class="stat-title">Resolved</div>
            <div class="stat-value resolved">{{ supportStats.resolved }}</div>
          </div>
        </div>

        <div class="support-list">
          <div v-if="supportRequests.length === 0" class="no-requests">
            No support requests found
          </div>
          <div v-else class="request-list">
            <div v-for="request in supportRequests" :key="request.support_id" class="request-item">
              <div class="request-header">
                <div class="user-info">
                  <img 
                    :src="request.user_profile_picture || defaultProfilePic" 
                    alt="user" 
                    class="user-avatar"
                    @error="handleImageError"
                  />
                  <div class="user-details">
                    <span class="user-name">{{ request.user_name }}</span>
                    <span class="request-time">{{ formatDate(request.created_at) }}</span>
                  </div>
                </div>
                <div class="request-status" :class="request.status">
                  {{ request.status }}
                </div>
              </div>

              <div class="request-content">
                <div class="request-subject">{{ request.subject }}</div>
                <p class="request-description">{{ request.description }}</p>
                <div class="request-priority" :class="request.priority">
                  Priority: {{ request.priority }}
                </div>
              </div>

              <div class="request-actions">
                <select 
                  v-model="request.status" 
                  @change="updateSupportStatus(request.support_id, $event.target.value)"
                  class="status-select"
                >
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                </select>
                <button 
                  v-if="request.status === 'resolved'" 
                  @click="deleteSupportRequest(request.support_id)"
                  class="delete-btn"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'AdminPanel',
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
      totalAccounts: 0,
      totalRooms: 0,
      totalReviews: 0,
      totalBoardingHouses: 0,
      totalRevenue: 0,
      totalBookings: 0,
      pendingBookings: 0,
      confirmedBookings: 0,
      cancelledBookings: 0,
      recentActivities: [],
      timeUpdateInterval: null,
      supportRequests: [],
      supportStats: {
        total: 0,
        pending: 0,
        inProgress: 0,
        resolved: 0
      },
      hasGcashQR: false,
      hasPaymayaQR: false,
      availableRooms: 0,
      occupiedRooms: 0,
      maintenanceRooms: 0,
      totalTenants: 0,
      activeTenants: 0,
      pendingPayments: 0,
      completedPayments: 0,
      failedPayments: 0,
      activityInterval: null,
      pendingAmount: 0,
      completedAmount: 0,
      failedAmount: 0,
      reviewStats: {
        total: 0,
        averageRating: 0,
        positiveReviews: 0,
        negativeReviews: 0
      },
      reportStats: {
        pending: 0,
        reviewed: 0,
        resolved: 0,
        dismissed: 0
      },
    }
  },
  methods: {
    ...mapActions({
      fetchAccountsAction: 'admin/fetchAccounts',
      fetchRoomsAction: 'admin/fetchRooms',
      fetchReviewsAction: 'admin/fetchReviews'
    }),

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.handleLogout();
      } else if (route !== '#') {
        this.$router.push(route);
      }
    },

    async handleLogout() {
      try {
        await axios.post('/logout');
        // Clear all auth data
        localStorage.removeItem('userName');
        localStorage.removeItem('userId');
        localStorage.removeItem('token');
        localStorage.removeItem('userType');
        
        // Redirect to admin login using the named route
        this.$router.push({ name: 'admin.login' });
      } catch (error) {
        console.error('Logout error:', error);
        // Still redirect even if the server request fails  
        this.$router.push({ name: 'admin.login' });
      }
    },

    async fetchTotalRooms() {
      try {
        const response = await axios.get('/admin/fetch-rooms', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        console.log('Room fetch response:', response.data);

        if (response.data.status === 'success') {
          const rooms = response.data.data;
          this.totalRooms = rooms.length;
          this.availableRooms = rooms.filter(room => room.status === 'available').length;
          this.occupiedRooms = rooms.filter(room => room.status === 'occupied').length;
          this.maintenanceRooms = rooms.filter(room => room.status === 'maintenance').length;
        } else {
          throw new Error(response.data.message || 'Failed to fetch rooms');
        }
      } catch (error) {
        console.error('Error fetching rooms:', error);
        if (error.response) {
          console.error('Error response:', error.response.data);
          console.error('Error status:', error.response.status);
        }
        this.totalRooms = 0;
        this.availableRooms = 0;
        this.occupiedRooms = 0;
        this.maintenanceRooms = 0;
      }
    },

    async fetchTotalReviews() {
      try {
        const reviews = await this.fetchReviewsAction();
        this.totalReviews = reviews?.length || 0;
      } catch (error) {
        console.error('Error fetching reviews:', error);
        this.totalReviews = 0;
      }
    },

    getProfilePicture(profilePicture) {
      console.log('Getting profile picture:', profilePicture);
      console.log('Profile from localStorage:', localStorage.getItem('profilePicture'));
      
      if (profilePicture && profilePicture.trim() !== '') {
        console.log('Using provided profile picture:', profilePicture);
        return profilePicture;
      }
      
      console.log('Using default picture:', this.defaultProfilePic);
      return this.defaultProfilePic;
    },

    handleImageError(e) {
      console.log('Image failed to load:', e.target.src);
      e.target.src = this.defaultProfilePic;
    },

    handleImageLoad(e) {
      console.log('Image loaded successfully:', e.target.src);
    },

    async fetchAdminProfile() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get(`/admin/fetch-account/${userId}`);
        if (response.data.status === 'success') {
          this.adminProfile = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching admin profile:', error);
        if (error.response?.status === 403) {
          // Unauthorized - redirect to login
          this.$router.push('/admin/admin_login');
        }
      }
    },

    formatRevenue(amount) {
      return parseFloat(amount || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },

    async fetchTotalRevenue() {
      try {
        const response = await axios.get('/admin/fetch-revenue');
        if (response.data.status === 'success') {
          this.totalRevenue = response.data.data.total || 0;
        }
      } catch (error) {
        console.error('Error fetching revenue:', error);
        this.totalRevenue = 0;
      }
    },

    async fetchTotalBookings() {
      try {
        const response = await axios.get('/admin/get-bookings');
        if (response.data.status === 'success') {
          const stats = response.data.stats;
          this.totalBookings = stats.total || 0;
          this.pendingBookings = stats.pending || 0;
          this.confirmedBookings = stats.confirmed || 0;
          this.cancelledBookings = stats.cancelled || 0;
        }
      } catch (error) {
        console.error('Error fetching bookings:', error);
        this.totalBookings = 0;
        this.pendingBookings = 0;
        this.confirmedBookings = 0;
        this.cancelledBookings = 0;
      }
    },

    getActivityIcon(type) {
      const icons = {
        account_created: 'fas fa-user-plus',
        account_updated: 'fas fa-user-edit',
        boarding_house_created: 'fas fa-home',
        boarding_house_updated: 'fas fa-edit',
        boarding_house_added: 'fas fa-plus-circle',
        room_created: 'fas fa-door-open',
        room_updated: 'fas fa-door-closed',
        room_added: 'fas fa-plus-square',
        booking_created: 'fas fa-calendar-plus',
        booking_updated: 'fas fa-calendar-check',
        payment_received: 'fas fa-money-bill-wave',
        review_added: 'fas fa-star',
        profile_updated: 'fas fa-user-cog',
        tenant_added: 'fas fa-user-plus',
        tenant_updated: 'fas fa-user-edit',
        support_created: 'fas fa-question-circle',
        support_updated: 'fas fa-comments',
        default: 'fas fa-info-circle'
      };
      return icons[type] || icons.default;
    },

    async fetchDashboardData() {
      try {
        const response = await axios.get('/admin/get-dashboard-data');
        if (response.data.status === 'success') {
          const data = response.data.data;
          
          // Update all the dashboard stats
          this.totalAccounts = data.totalAccounts;
          this.totalBoardingHouses = data.totalBoardingHouses;
          
          // Update booking stats
          this.pendingBookings = data.bookingStats.pending;
          this.confirmedBookings = data.bookingStats.confirmed;
          this.cancelledBookings = data.bookingStats.cancelled;
          
          // Update revenue
          this.totalRevenue = data.totalRevenue;
          
          // Update payment stats
          this.pendingPayments = data.paymentStats.pending;
          this.completedPayments = data.paymentStats.completed;
          this.failedPayments = data.paymentStats.failed;

          // Update room stats
          this.availableRooms = data.roomStats?.available ?? 0;
          this.occupiedRooms = data.roomStats?.occupied ?? 0;
          this.maintenanceRooms = data.roomStats?.maintenance ?? 0;

          // Update tenant stats
          this.totalTenants = data.tenantStats?.total ?? 0;
          this.activeTenants = data.tenantStats?.active ?? 0;

          // Update review stats
          this.reviewStats = data.reviewStats ?? {
            total: 0,
            averageRating: 0,
            positiveReviews: 0,
            negativeReviews: 0
          };

          await this.fetchReportedReviewsStats();
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString);
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

    startTimeUpdates() {
      this.timeUpdateInterval = setInterval(() => {
        if (this.recentActivities.length > 0) {
          // Update time ago for each activity
          this.recentActivities = this.recentActivities.map(activity => ({
            ...activity,
            timeAgo: this.formatDate(activity.created_at)
          }));
        }
      }, 60000); // Update every minute
    },

    getActivityUserProfile(activity) {
      // Try to get profile picture from activity data
      if (activity.user_profile_picture && activity.user_profile_picture.trim() !== '') {
        return activity.user_profile_picture;
      }
      return this.defaultProfilePic;
    },

    async fetchSupportRequests() {
      try {
        const response = await axios.get('/admin/support-requests');
        if (response.data.status === 'success') {
          // The response now includes both requests and stats
          this.supportRequests = response.data.data.requests;
          this.supportStats = response.data.data.stats;
        }
      } catch (error) {
        console.error('Error fetching support requests:', error);
      }
    },

    async updateSupportStatus(requestId, newStatus) {
      try {
        const response = await axios.put(`/admin/support-requests/${requestId}/status`, {
          status: newStatus
        });
        
        if (response.data.status === 'success') {
          const supportRequest = response.data.data;
          let message = '';
          
          // Create specific messages based on status
          switch (newStatus) {
            case 'pending':
              message = `Your support request "${supportRequest.subject}" has been received and is pending review.`;
              break;
            case 'in_progress':
              message = `Your support request "${supportRequest.subject}" is now being processed by our admin team.`;
              break;
            case 'resolved':
              message = `Your support request "${supportRequest.subject}" has been resolved. Thank you for your patience.`;
              break;
          }

          // Create notification for the specific user
          await this.createNotification({
            user_id: supportRequest.user_id,
            type: 'support',
            message: message,
            link: '/user/help'
          });
          
          await this.fetchSupportRequests();
          alert('Support request status updated successfully');
        }
      } catch (error) {
        console.error('Error updating support request:', error);
        alert('Failed to update support request status');
      }
    },

    async createNotification(notificationData) {
      try {
        const response = await axios.post('/notifications/create', notificationData);
        if (response.data.status === 'success') {
          console.log('Notification created successfully');
        }
      } catch (error) {
        console.error('Error creating notification:', error);
      }
    },

    async deleteSupportRequest(requestId) {
      if (confirm('Are you sure you want to delete this resolved support request?')) {
        try {
          console.log('Deleting support request:', requestId);
          const response = await axios.delete(`/admin/support-requests/${requestId}`);
          
          if (response.data.status === 'success') {
            await this.fetchSupportRequests(); // Refresh the list
            alert('Support request deleted successfully');
          }
        } catch (error) {
          console.error('Error deleting support request:', error);
          alert(error.response?.data?.message || 'Failed to delete support request');
        }
      }
    },

    async fetchPaymentQRStatus() {
      try {
        const response = await axios.get('/admin/payment-qr-codes');
        if (response.data.status === 'success') {
          const qrCodes = response.data.data;
          this.hasGcashQR = qrCodes.some(qr => qr.payment_type === 'gcash');
          this.hasPaymayaQR = qrCodes.some(qr => qr.payment_type === 'paymaya');
        }
      } catch (error) {
        console.error('Error fetching QR code status:', error);
      }
    },

    resetDashboardData() {
      this.totalBoardingHouses = 0;
      this.totalRooms = 0;
      this.totalAccounts = 0;
      this.totalBookings = 0;
      this.pendingBookings = 0;
      this.confirmedBookings = 0;
      this.cancelledBookings = 0;
      this.totalRevenue = 0;
      this.totalReviews = 0;
      this.recentActivities = [];
      this.supportStats = {
        total: 0,
        pending: 0,
        inProgress: 0,
        resolved: 0
      };
    },

    async addRoom() {
      try {
        const userId = localStorage.getItem('userId');
        const formData = new FormData();
        // ... add other form fields ...
        
        const response = await axios.post('/admin/add-room', formData, {
          headers: {
            'X-User-Id': userId,
            'Content-Type': 'multipart/form-data'
          }
        });
        
        // Handle response...
      } catch (error) {
        console.error('Error adding room:', error);
      }
    },

    async fetchRooms() {
      try {
        const response = await axios.get('/admin/fetch-rooms', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (response.data.status === 'success') {
          const rooms = response.data.data;
          this.availableRooms = rooms.filter(room => room.status === 'available').length;
          this.occupiedRooms = rooms.filter(room => room.status === 'occupied').length;
          this.maintenanceRooms = rooms.filter(room => room.status === 'maintenance').length;
        } else {
          throw new Error(response.data.message || 'Failed to fetch rooms');
        }
      } catch (error) {
        console.error('Error fetching rooms:', error);
        if (error.response) {
          console.error('Error response:', error.response.data);
          console.error('Error status:', error.response.status);
        }
        this.availableRooms = 0;
        this.occupiedRooms = 0;
        this.maintenanceRooms = 0;
      }
    },

    async fetchPaymentStats() {
      try {
        const response = await axios.get('/admin/payment-stats');
        if (response.data.status === 'success') {
          const stats = response.data.data;
          this.pendingAmount = stats.pending_amount || 0;
          this.completedAmount = stats.completed_amount || 0;
          this.failedAmount = stats.failed_amount || 0;
          this.totalRevenue = stats.total_revenue || 0;
        }
      } catch (error) {
        console.error('Error fetching payment stats:', error);
      }
    },

    async fetchRecentActivities() {
      try {
        const response = await axios.get('/admin/recent-activities');
        
        if (response.data.status === 'success') {
          this.recentActivities = response.data.data;
        } else {
          throw new Error(response.data.message || 'Failed to fetch activities');
        }
      } catch (error) {
        console.error('Error fetching activities:', error);
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

    async fetchRevenue() {
      try {
        const response = await axios.get('/admin/fetch-revenue');
        if (response.data.status === 'success') {
          this.totalRevenue = response.data.data.revenue.total || 0;
          console.log('Fetched revenue:', this.totalRevenue);
        }
      } catch (error) {
        console.error('Error fetching revenue:', error);
        this.totalRevenue = 0;
      }
    }
  },
  created() {
    // Check if user is authenticated and is admin
    const isAdmin = localStorage.getItem('userType') === 'admin';
    const hasToken = localStorage.getItem('token') === 'true';
    const userId = localStorage.getItem('userId');
    
    if (!isAdmin || !hasToken || !userId) {
      console.log('Not authenticated, redirecting to login');
      this.$router.push('/admin/admin_login');
      return;
    }
    
    // Set the user ID in adminProfile
    this.adminProfile = {
      ...this.adminProfile,
      user_id: userId
    };
    
    // Only fetch dashboard data once
    this.fetchDashboardData();
    this.fetchAdminProfile();
    this.startTimeUpdates();
    this.fetchSupportRequests();
    this.fetchPaymentQRStatus();
    this.fetchTotalRooms();
    this.fetchPaymentStats();
    this.fetchRecentActivities();
    // Start activity polling
    this.startActivityPolling();
  },
  beforeDestroy() {
    if (this.activityInterval) {
      clearInterval(this.activityInterval);
    }
    if (this.timeUpdateInterval) {
      clearInterval(this.timeUpdateInterval);
    }
  },
  // Add activated hook for when component is re-activated
  activated() {
    this.startActivityPolling();
  },
  // Add deactivated hook to clean up when component is deactivated
  deactivated() {
    if (this.activityInterval) {
      clearInterval(this.activityInterval);
    }
  }
};
</script>
<style scoped>
@import '@/assets/css/admin_panel.css';

/* Additional responsive styles specific to admin panel */
@media screen and (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-card {
    margin-bottom: 1rem;
  }
}

.overall {
  padding: 2rem;
  min-height: calc(100vh - 60px);
  margin-top: 65px;
}

.home {
  text-align: center;
  margin-bottom: 3rem;
}

.helper {
  color: #794646;
  font-size: 2rem;
  font-weight: 700;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
}

.admin-name {
  color: #794646;
  font-size: 1.2rem;
  font-weight: 600;
  opacity: 0.9;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.dashboard-card {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
  min-height: 250px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

.dashboard-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.card-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.card-title {
  color: #794646;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.card-subtitle {
  color: #666;
  font-size: 1rem;
  font-weight: 500;
}

.card-content {
  text-align: center;
  padding: 1.5rem 0;
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.number {
  font-size: 2.5rem;
  font-weight: bold;
  color: #794646;
  transform: translate(-49.5%, -10%);
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.card-actions {
  margin-top: auto;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
}

.action-link {
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: #794646;
  font-weight: 600;
  padding: 0.75rem;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.action-link:hover {
  background-color: rgba(121, 70, 70, 0.1);
  transform: translateY(-2px);
}

.action-icon {
  width: 20px;
  height: 20px;
  margin-right: 0.5rem;
  opacity: 0.8;
}

@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .helper {
    font-size: 1.5rem;
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .dashboard-card {
    min-height: 200px;
    padding: 1.5rem;
  }

  .number {
    font-size: 2.5rem;
  }

  .card-icon {
    font-size: 2rem;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.menu-container {
  margin-left: 0;
}

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
  border: none;
  background-color: #f8f9fa;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.dashboard-card .number {
  font-family: 'Arial', sans-serif;
  font-size: 2.2rem;
  font-weight: bold;
  color: #10b981; /* Green color for revenue */
}

.dashboard-card:nth-child(5) { /* Revenue card */
  background: linear-gradient(145deg, #ffffff 0%, #f0fff4 100%);
}

.dashboard-card:nth-child(5) .card-title {
  color: #10b981;
}

.dashboard-card:nth-child(5) .action-link {
  color: #10b981;
}

.dashboard-card:nth-child(5) .action-link:hover {
  background-color: rgba(16, 185, 129, 0.1);
}

/* Responsive styles */
@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-card .number {
    font-size: 1.8rem;
  }
}

/* Add these styles for the booking card */
.booking-stats {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 1rem;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #666;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 600;
}

.stat-value.pending {
  color: #f59e0b; /* Amber color for pending */
}

.stat-value.confirmed {
  color: #10b981; /* Green color for confirmed */
}

.dashboard-card:nth-child(6) { /* Booking card */
  background: linear-gradient(145deg, #ffffff 0%, #f0f7ff 100%);
}

.dashboard-card:nth-child(6) .card-title {
  color: #3b82f6; /* Blue color for bookings */
}

.dashboard-card:nth-child(6) .action-link {
  color: #3b82f6;
}

.dashboard-card:nth-child(6) .action-link:hover {
  background-color: rgba(59, 130, 246, 0.1);
}

/* Update grid layout for 6 cards */
.dashboard-grid {
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

@media (max-width: 1200px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .booking-stats {
    gap: 1rem;
  }
}

/* Recent Activities Styles */
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

.activity-icon.account_created,
.activity-icon.account_updated,
.activity-icon[class*="Updated admin account"],
.activity-icon[class*="Updated landlord account"] {
  background: #3b82f6; /* Blue */
}

.activity-icon.boarding_house_added,
.activity-icon.boarding_house_created,
.activity-icon.boarding_house_updated,
.activity-icon[class*="Added new boarding house"],
.activity-icon[class*="Updated boarding house"] {
  background: #10b981; /* Green */
}

.activity-icon.room_added,
.activity-icon.room_created,
.activity-icon.room_updated,
.activity-icon[class*="Added new room"],
.activity-icon[class*="Updated room"] {
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

.activity-content {
  flex-grow: 1;
}

.activity-text {
  color: #374151;
  margin: 0;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.activity-time {
  color: #6b7280;
  font-size: 0.875rem;
}

/* Add these styles */
.activity-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.user-type-badge {
  display: none;
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

/* Add these styles */
.support-requests {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.support-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
}

.stat-title {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #794646;
}

.stat-value.pending { color: #f59e0b; }
.stat-value.in-progress { color: #3b82f6; }
.stat-value.resolved { color: #10b981; }

.request-item {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #374151;
}

.request-time {
  font-size: 0.875rem;
  color: #6b7280;
}

.request-status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.request-status.pending { 
  background: #fef3c7; 
  color: #d97706; 
}

.request-status.in_progress { 
  background: #dbeafe; 
  color: #2563eb; 
}

.request-status.resolved { 
  background: #d1fae5; 
  color: #059669; 
}

.request-content {
  margin-bottom: 1rem;
}

.request-subject {
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.request-description {
  color: #4b5563;
  margin-bottom: 0.5rem;
}

.request-priority {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.request-priority.high { 
  background: #fee2e2; 
  color: #dc2626; 
}

.request-priority.medium { 
  background: #fef3c7; 
  color: #d97706; 
}

.request-priority.low { 
  background: #d1fae5; 
  color: #059669; 
}

.request-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.status-select {
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #374151;
}

.respond-btn {
  padding: 0.5rem 1rem;
  background: #794646;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.respond-btn:hover {
  background: #693c3c;
}

/* Add these styles */
.response-dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.response-dialog {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logos{
  transform: translateY(-2.2px);
}

.request-details {
  margin: 1rem 0;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.response-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  margin: 1rem 0;
  resize: vertical;
}

.dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-btn,
.submit-btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
}

.cancel-btn {
  background: #f3f4f6;
  color: #374151;
  border: none;
}

.submit-btn {
  background: #794646;
  color: white;
  border: none;
}

.submit-btn:hover {
  background: #693c3c;
}

.delete-btn {
  padding: 0.5rem 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-left: 0.5rem;
}

.delete-btn:hover {
  background: #dc2626;
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

.stats-breakdown {
  padding: 1rem;
  font-size: 0.875rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.stat {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  padding: 0.5rem;
}

.stat .label {
  color: #666;
  font-size: 1rem;
}

.stat .value {
  font-weight: 600;
  font-size: 1.25rem;
}

.stat .value.pending {
  color: #f59e0b;
}

.stat .value.confirmed {
  color: #10b981;
}

.stat .value.available {
  color: #10b981; /* green */
}

.stat .value.occupied {
  color: #f59e0b; /* amber */
}

.stat .value.maintenance {
  color: #ef4444; /* red */
}

/* Add these styles for the revenue icon */
.dashboard-card:nth-child(3) .card-icon i {
  color: #10b981;  /* Green color to match your revenue theme */
  font-size: 2rem;
}

.dashboard-card:nth-child(8) .card-icon i {
  color: #8b5cf6;  /* Purple color for tenants */
  font-size: 2rem;
}

.stat .value.active {
  color: #8b5cf6; /* Purple for active tenants */
}

.stat .value.completed {
  color: #10b981; /* green */
}

.stat .value.failed {
  color: #ef4444; /* red */
}

/* Revenue number styles */
.number.loading {
  opacity: 0.7;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { opacity: 0.7; }
  50% { opacity: 0.4; }
  100% { opacity: 0.7; }
}

.dashboard-card:nth-child(3) .number {
  color: #10b981;
  font-weight: bold;
  font-size: 2rem;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

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
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.detail-value.pending {
  background-color: #fff3cd;
  color: #856404;
}

.detail-value.completed {
  background-color: #d4edda;
  color: #155724;
}

.detail-value.failed {
  background-color: #f8d7da;
  color: #721c24;
}

.system-icon {
    margin-left: 0.5rem;
    color: #794646;
    font-size: 0.9rem;
    opacity: 0.8;
}

.info-value {
    color: #666;
    flex-grow: 1;
    display: flex;
    align-items: center;
}

.system-avatar {
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #e0e0e0;
}

.system-avatar i {
  color: #794646;
  font-size: 1.2rem;
}

.stat .value.in-progress {
  color: #3b82f6; /* blue */
}

.stat .value.resolved {
  color: #10b981; /* green */
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
  

