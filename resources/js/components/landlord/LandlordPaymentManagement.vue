<template>
  <div>
    <!-- Navigation Bar -->
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

    <div class="payment-management">
      <div class="back-section">
        <button class="back-btn" @click="goBack">
          <span class="arrow-left"></span>
          Back
        </button>
      </div>

      <h2>Payment Management</h2>

      <!-- Payment Statistics -->
      <div class="payment-stats">
        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-details">
            <span class="stat-value">{{ pendingPayments }}</span>
            <span class="stat-label">Pending</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-details">
            <span class="stat-value">{{ completedPayments }}</span>
            <span class="stat-label">Completed</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon failed">
            <i class="fas fa-times-circle"></i>
          </div>
          <div class="stat-details">
            <span class="stat-value">{{ failedPayments }}</span>
            <span class="stat-label">Failed</span>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters">
        <div class="filter-group">
          <label>Status:</label>
          <select v-model="filters.status">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Payment Method:</label>
          <select v-model="filters.paymentMethod">
            <option value="">All</option>
            <option value="cash">Cash</option>
            <option value="gcash_qr">GCash</option>
            <option value="paymaya_qr">PayMaya</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Date Range:</label>
          <input type="date" v-model="filters.startDate">
          <input type="date" v-model="filters.endDate">
        </div>
      </div>

      <!-- Payments Table -->
      <div class="payments-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Booking ID</th>
              <th>Tenant</th>
              <th>Room</th>
              <th>Room Price</th>
              <th>Paid Amount</th>
              <th>Status</th>
              <th>Method</th>
              <th>Reference</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in filteredPayments" :key="payment.payment_id">
              <td>#{{ payment.payment_id }}</td>
              <td>#{{ payment.booking_id }}</td>
              <td>{{ payment.booking?.user?.name || 'N/A' }}</td>
              <td>{{ payment.booking?.room?.room_name || 'N/A' }}</td>
              <td>₱{{ formatAmount(payment.booking?.room?.price || 0) }}</td>
              <td>
                <div class="paid-amount" :class="{ 'failed': payment.status === 'failed' }">
                  <template v-if="payment.status === 'failed'">
                    Failed Payment
                  </template>
                  <template v-else>
                    <span class="amount-text">₱{{ formatAmount(payment.amount) }} / ₱{{ formatAmount(payment.booking?.room?.price || 0) }}</span>
                    <div :class="getPaymentStatusClass(payment)">
                      {{ getPaymentStatusText(payment) }}
                    </div>
                  </template>
                </div>
              </td>
              <td>
                <span class="status-badge" :class="payment.status">
                  {{ payment.status }}
                </span>
              </td>
              <td>
                <span class="payment-method" :class="payment.payment_method">
                  {{ formatPaymentMethod(payment.payment_method) }}
                </span>
              </td>
              <td>
                <span class="reference-number" :title="payment.reference_number">
                  {{ payment.reference_number }}
                </span>
              </td>
              <td>{{ formatDate(payment.created_at) }}</td>
              <td>
                <div class="action-buttons">
                  <button 
                    v-if="payment.status === 'pending'"
                    @click="updatePaymentStatus(payment.payment_id, 'completed')"
                    class="btn-complete"
                  >
                    Complete
                  </button>
                  <button 
                    v-if="payment.status === 'pending'"
                    @click="updatePaymentStatus(payment.payment_id, 'failed')"
                    class="btn-reject"
                  >
                    Reject
                  </button>
                  <button 
                    v-if="payment.payment_proof"
                    @click="viewProof(payment)"
                    class="btn-view"
                  >
                    View Proof
                  </button>
                  <button 
                    v-if="payment.status === 'completed'"
                    @click="generateReceipt(payment.payment_id)"
                    class="btn-view"
                  >
                    <i class="fas fa-file-pdf"></i>
                    Receipt
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Payment Proof Modal -->
      <div v-if="showProofModal" class="modal">
        <div class="modal-content">
          <span class="close" @click="showProofModal = false">&times;</span>
          <h3>Payment Proof</h3>
          <div class="proof-details">
            <div class="payment-info">
              <div class="info-item">
                <strong>Reference Number:</strong> 
                <span>{{ selectedPayment?.reference_number || 'No reference number' }}</span>
              </div>
              <div class="info-item">
                <strong>Amount:</strong> 
                <span>₱{{ formatAmount(selectedPayment?.amount || 0) }}</span>
              </div>
              <div class="info-item">
                <strong>Status:</strong> 
                <span class="status-badge" :class="selectedPayment?.status">
                  {{ selectedPayment?.status }}
                </span>
              </div>
              <div class="info-item">
                <strong>Payment Method:</strong> 
                <span>{{ formatPaymentMethod(selectedPayment?.payment_method) }}</span>
              </div>
            </div>
            <div class="image-container" @click="openFullImage">
              <img :src="selectedPaymentProof" alt="Payment Proof" class="proof-image">
              <div class="image-overlay">
                <i class="fas fa-search-plus"></i>
                <span>Click to view full size</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Full Size Image Modal -->
      <div v-if="showFullImage" class="full-image-modal" @click="closeFullImage">
        <span class="close" @click="closeFullImage">&times;</span>
        <img 
          :src="selectedPaymentProof" 
          alt="Full Size Payment Proof" 
          class="full-size-image"
          @click.stop
        >
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
  name: 'LandlordPaymentManagement',
  data() {
    return {
      payments: [],
      pendingPayments: 0,
      completedPayments: 0,
      failedPayments: 0,
      filters: {
        status: '',
        paymentMethod: '',
        startDate: '',
        endDate: ''
      },
      showProofModal: false,
      selectedPaymentProof: null,
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
      selectedPayment: null,
      showFullImage: false
    }
  },
  computed: {
    filteredPayments() {
      return this.payments.filter(payment => {
        const matchesStatus = !this.filters.status || payment.status === this.filters.status;
        const matchesMethod = !this.filters.paymentMethod || payment.payment_method === this.filters.paymentMethod;
        const matchesDate = this.isWithinDateRange(payment.created_at);
        return matchesStatus && matchesMethod && matchesDate;
      });
    },
    totalAmount() {
      return this.payments.reduce((sum, payment) => {
        return sum + (payment.amount || 0);
      }, 0);
    }
  },
  methods: {
    async fetchPayments() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get('/landlord/payments', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          this.payments = response.data.data.payments.map(payment => ({
            ...payment,
            amount: payment.amount ? parseFloat(payment.amount) : 0,
            booking: {
              ...payment.booking,
              room: {
                ...payment.booking?.room,
                price: payment.booking?.room?.price ? parseFloat(payment.booking.room.price) : 0
              }
            }
          }));

          const stats = response.data.data.stats;
          this.pendingPayments = parseInt(stats.pending) || 0;
          this.completedPayments = parseInt(stats.completed) || 0;
          this.failedPayments = parseInt(stats.failed) || 0;

          console.log('Fetched payments:', this.payments);
          console.log('Payment stats:', {
            pending: this.pendingPayments,
            completed: this.completedPayments,
            failed: this.failedPayments
          });
        }
      } catch (error) {
        console.error('Error fetching payments:', error);
        this.payments = [];
        this.pendingPayments = 0;
        this.completedPayments = 0;
        this.failedPayments = 0;
      }
    },

    async updatePaymentStatus(paymentId, newStatus) {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.post(`/landlord/update-payment/${paymentId}`, 
          { status: newStatus },
          {
            headers: {
              'X-User-Id': userId,
              'Content-Type': 'application/json'
            }
          }
        );

        if (response.data.status === 'success') {
          // Update local payment status
          const payment = this.payments.find(p => p.payment_id === paymentId);
          if (payment) {
            payment.status = newStatus;
          }
          
          // Generate receipt and send notification if payment is completed
          if (newStatus === 'completed') {
            await this.generateReceipt(paymentId);
          }
          
          // Update payment stats
          await this.fetchPayments();
          
          // Show success message
          alert(`Payment ${newStatus === 'completed' ? 'confirmed' : 'rejected'} successfully`);
        }
      } catch (error) {
        console.error('Error updating payment status:', error);
        alert(error.response?.data?.message || 'Failed to update payment status');
      }
    },

    async generateReceipt(paymentId) {
      try {
        const response = await axios.post(`/generate-receipt/${paymentId}`, {
          sendNotification: true
        });
        
        if (response.data.status === 'success') {
          console.log('Receipt generated successfully');
          window.open(response.data.data.receipt_url, '_blank');
          
          // Show success message
          alert('Receipt generated and notification sent to tenant');
          
          // Optionally refresh the payments list
          await this.fetchPayments();
        }
      } catch (error) {
        console.error('Error generating receipt:', error);
        alert('Failed to generate receipt: ' + (error.response?.data?.message || error.message));
      }
    },

    formatAmount(amount) {
      return amount ? amount.toFixed(2) : '0.00';
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    formatPaymentMethod(method) {
      const methods = {
        cash: 'Cash',
        gcash_qr: 'GCash',
        paymaya_qr: 'PayMaya'
      };
      return methods[method] || method;
    },

    formatPaymentStatus(status) {
      const statuses = {
        pending: 'Pending',
        completed: 'Completed',
        failed: 'Failed',
        partially_paid: 'Partially Paid'
      };
      return statuses[status] || status;
    },

    getPaymentStatusClass(payment) {
      const totalPaid = payment.amount || 0;
      const totalPrice = payment.booking.room.price;
      
      if (payment.status === 'failed') return 'failed';
      if (totalPaid >= totalPrice) return 'paid';
      if (totalPaid > 0) return 'partially-paid';
      return 'unpaid';
    },

    getPaymentStatusText(payment) {
      const totalPaid = payment.amount || 0;
      const totalPrice = payment.booking.room.price;
      
      if (payment.status === 'failed') return 'Failed';
      if (totalPaid >= totalPrice) return 'Paid';
      if (totalPaid > 0) return 'Partially Paid';
      return 'Unpaid';
    },

    isWithinDateRange(date) {
      if (!this.filters.startDate && !this.filters.endDate) return true;
      
      const paymentDate = new Date(date);
      const startDate = this.filters.startDate ? new Date(this.filters.startDate) : null;
      const endDate = this.filters.endDate ? new Date(this.filters.endDate) : null;
      
      if (startDate && paymentDate < startDate) return false;
      if (endDate && paymentDate > endDate) return false;
      return true;
    },

    viewProof(payment) {
      if (payment.payment_proof) {
        this.selectedPaymentProof = `/storage/${payment.payment_proof}`;
        this.selectedPayment = payment;
        this.showProofModal = true;
      }
    },

    goBack() {
      this.$router.push('/landlord/dashboard');
    },

    async navigateMenu(event) {
      const route = event.target.value;
      if (route === '/landlord/login') {
        await this.logout();
      } else {
        this.$router.push(route);
      }
    },

    async logout() {
      try {
        const response = await axios.post('/landlord/logout');
        
        if (response.data.status === 'success') {
          // Clear all localStorage items
          localStorage.clear(); // This will clear all items at once
          
          // Remove Authorization header
          delete axios.defaults.headers.common['Authorization'];
          
          // Use replace instead of push to prevent back navigation
          this.$router.replace('/landlord/login');
        } else {
          throw new Error('Logout failed');
        }
      } catch (error) {
        console.error('Logout error:', error);
        // Still clear storage and redirect even if the server request fails
        localStorage.clear();
        this.$router.replace('/landlord/login');
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

    openFullImage() {
      this.showFullImage = true;
    },

    closeFullImage() {
      this.showFullImage = false;
    }
  },
  created() {
    this.fetchLandlordProfile();
    this.fetchPayments();
  }
}
</script>

<style scoped>
@import '@/assets/css/landlord_panel.css';

/* Payment Management Styles */
.payment-management {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  margin-top: 80px;
  min-height: 100vh;
  background: #f8f9fa;
  position: relative;
}

/* Back Button */
.back-section {
  margin-bottom: 1rem;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: none;
  border: none;
  color: #794646;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
}

.back-btn:hover {
  transform: translateX(-5px);
}

.arrow-left {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-left: 2px solid #794646;
  border-bottom: 2px solid #794646;
  transform: rotate(45deg);
  margin-right: 5px;
}

/* Stats Cards */
.payment-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon.pending { background: #fff7ed; color: #f59e0b; }
.stat-icon.completed { background: #f0fdf4; color: #10b981; }
.stat-icon.failed { background: #fef2f2; color: #ef4444; }

.stat-details {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.stat-label {
  color: #666;
  font-size: 0.875rem;
}

/* Filters */
.filters {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group select,
.filter-group input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 6px;
}

/* Table Styles */
.payments-table {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow-x: auto;
  width: 100%;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1200px;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
  min-width: fit-content;
}

td:nth-child(1),
td:nth-child(2) {
  width: 80px;
}

td:nth-child(3),
td:nth-child(4) {
  width: 120px;
}

td:nth-child(5),
td:nth-child(6) {
  width: 150px;
}

td:nth-child(7),
td:nth-child(8) {
  width: 100px;
}

td:nth-child(9) {
  width: 120px;
}

td:nth-child(10) {
  width: 100px;
}

td:nth-child(11) {
  width: 200px;
}

/* Status Badges */
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending { background: #fff7ed; color: #f59e0b; }
.status-badge.completed { background: #f0fdf4; color: #10b981; }
.status-badge.failed { background: #fef2f2; color: #ef4444; }

/* Payment Method Badges */
.payment-method {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
}

.payment-method.cash { background: #f3f4f6; }
.payment-method.gcash_qr { background: #e0f2fe; color: #0284c7; }
.payment-method.paymaya_qr { background: #faf5ff; color: #7c3aed; }

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: nowrap;
  white-space: nowrap;
}

.btn-complete,
.btn-reject,
.btn-view {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  white-space: nowrap;
}

.btn-complete {
  background: #10b981;
  color: white;
}

.btn-reject {
  background: #ef4444;
  color: white;
}

.btn-view {
  background: #f3f4f6;
  color: #374151;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  max-width: 90vw;
  max-height: 90vh;
  position: relative;
  width: 100%;
  max-width: 600px;
}

.close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.proof-image {
  max-width: 100%;
  max-height: calc(70vh - 150px);
  object-fit: contain;
  display: block;
  margin: 0 auto;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.proof-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-info {
  background: #f8fafc;
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin: 0;
  font-size: 0.95rem;
  border-left: 4px solid #794646;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item strong {
  color: #794646;
  font-weight: 600;
}

.info-item span {
  color: #374151;
  font-weight: 500;
}

.info-item .status-badge {
  font-size: 0.8rem;
  padding: 0.25rem 0.75rem;
}

.modal-content {
  min-width: 450px;
}

/* Payment Progress */
.payment-progress {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.payment-progress span {
  font-family: monospace;
  white-space: nowrap;
  font-size: 0.9rem;
  color: #374151;
}

.payment-status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.payment-status-badge.completed {
  background: #dcfce7;
  color: #15803d;
}

.payment-status-badge.partially_paid {
  background: #fef9c3;
  color: #854d0e;
}

.payment-status-badge.unpaid {
  background: #fee2e2;
  color: #991b1b;
}

/* Responsive Styles */
@media (max-width: 1024px) {
  .payment-management {
    padding: 1rem 0.5rem;
    margin-top: 80px;
  }

  .payments-table {
    margin: 0 -0.5rem;
    border-radius: 0;
  }

  table {
    display: block;
    overflow-x: auto;
  }
}

/* Navbar Layout Styles */
.navbars {
  display: flex;
  align-items: center;
  padding: 0rem 2rem;
  background: #F5F2F2;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
}


.landlord-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

/* Menu Container Styles */
.menu-container {
  display: flex;
  align-items: center;
  margin-left: 0;
}

.paid-amount {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.paid-amount.failed {
  color: #dc2626;
  font-weight: 500;
}

.paid {
  background-color: #d1fae5;
  color: #059669;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.partially-paid {
  background-color: #fef3c7;
  color: #d97706;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.unpaid {
  background-color: #fee2e2;
  color: #dc2626;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.failed {
  background-color: #fee2e2;
  color: #dc2626;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.amount-text {
  font-family: monospace;
  white-space: nowrap;
  font-size: 0.9rem;
  color: #374151;
}

.image-container {
  position: relative;
  cursor: zoom-in;
  transition: transform 0.2s;
}

.image-container:hover .image-overlay {
  opacity: 1;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.2s;
  border-radius: 8px;
}

.image-overlay i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.full-image-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.full-size-image {
  max-width: 95vw;
  max-height: 95vh;
  object-fit: contain;
  cursor: default;
}

.full-image-modal .close {
  position: fixed;
  top: 1rem;
  right: 1rem;
  color: white;
  font-size: 2rem;
  cursor: pointer;
  z-index: 1101;
}

.full-image-modal .close:hover {
  color: #ddd;
}

.reference-number {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  background: #f3f4f6;
  border-radius: 4px;
  font-family: monospace;
  font-size: 0.875rem;
  color: #374151;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.reference-number:hover {
  background: #e5e7eb;
  cursor: default;
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

.btn-view i {
  margin-right: 5px;
}

.btn-view {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  background: #f3f4f6;
  color: #374151;
  transition: all 0.2s ease;
}

.btn-view:hover {
  background: #e5e7eb;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.generating-receipt {
  animation: pulse 1s infinite;
}
</style>
