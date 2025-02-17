<template>
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
            <th>Total Amount</th>
            <th>Paid Amount</th>
            <th>Status</th>
            <th>Method</th>
            <th>Reference No.</th>
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
              <div class="payment-progress">
                <div v-if="payment.status === 'failed'" class="payment-failed">
                  Failed Payment
                </div>
                <div v-else>
                  <div v-if="payment.status === 'pending'" class="payment-request-amount">
                    Requested: ₱{{ formatAmount(payment.amount) }}
                  </div>
                  <div v-else class="payment-paid-amount">
                    Paid: ₱{{ formatAmount(payment.total_paid) }}
                  </div>
                  <div v-if="payment.status !== 'failed'" class="payment-status-badge" :class="payment.payment_status">
                    {{ formattedPaymentStatus(payment.payment_status) }}
                  </div>
                </div>
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
              <span class="detail-label">Reference Number:</span>
              <span class="detail-value">{{ selectedPaymentDetails?.reference_number }}</span>
            </div>
            <div class="info-item">
              <span class="detail-label">Payment Method:</span>
              <span class="detail-value">{{ formatPaymentMethod(selectedPaymentDetails?.payment_method) }}</span>
            </div>
            <div class="info-item">
              <span class="detail-label">Amount:</span>
              <span class="detail-value">₱{{ formatAmount(selectedPaymentDetails?.amount) }}</span>
            </div>
            <div class="info-item">
              <span class="detail-label">Status:</span>
              <span class="status-badge" :class="selectedPaymentDetails?.status">
                {{ selectedPaymentDetails?.status }}
              </span>
            </div>
          </div>
        </div>
        <div class="image-container" @click="openFullImage">
          <img 
            :src="selectedPaymentProof" 
            alt="Payment Proof" 
            class="proof-image"
            @error="handleImageError"
            @load="handleImageLoad"
          >
          <div class="image-overlay">
            <i class="fas fa-search-plus"></i>
            <span>Click to view full size</span>
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
</template>

<script>
import axios from 'axios';

// Create a simple event bus if you don't have one
const toast = {
    show(message, type = 'success') {
        // You can implement your own toast notification logic here
        alert(message); // Simple fallback
    }
};

export default {
  name: 'PaymentManagement',
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
      selectedPaymentDetails: null,
      showFullImage: false
    }
  },
  computed: {
    filteredPayments() {
      console.log('Total payments before filtering:', this.payments.length);
      
      const filtered = this.payments.filter(payment => {
        const matchesStatus = !this.filters.status || payment.status === this.filters.status;
        const matchesMethod = !this.filters.paymentMethod || payment.payment_method === this.filters.paymentMethod;
        const matchesDate = this.isWithinDateRange(payment.created_at);
        
        if (!matchesStatus) console.log('Filtered out by status:', payment);
        if (!matchesMethod) console.log('Filtered out by method:', payment);
        if (!matchesDate) console.log('Filtered out by date:', payment);
        
        return matchesStatus && matchesMethod && matchesDate;
      });
      
      console.log('Payments after filtering:', filtered.length);
      return filtered;
    },
    formattedPaymentStatus() {
        return (status) => {
            switch (status) {
                case 'completed':
                    return 'Fully Paid';
                case 'partially_paid':
                    return 'Partially Paid';
                case 'unpaid':
                    return 'Unpaid';
                default:
                    return status.charAt(0).toUpperCase() + status.slice(1);
            }
        };
    }
  },
  methods: {
    async fetchPayments() {
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          console.error('No auth token found');
          return;
        }

        console.log('Initiating payment fetch request...');
        const response = await axios.get('/admin/fetch-payments', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        
        // Log the full response for debugging
        console.log('Full response:', {
          status: response.status,
          headers: response.headers,
          data: response.data
        });

        if (response.data.status === 'success' && response.data.data) {
          this.payments = response.data.data.payments || [];
          console.log('Loaded payments:', this.payments.length);
          
          // Log first few payments for debugging
          if (this.payments.length > 0) {
            console.log('Sample payments:', this.payments.slice(0, 3));
          }
          
          const stats = response.data.data.stats || {
            pending: 0,
            completed: 0,
            failed: 0
          };
          
          this.pendingPayments = parseInt(stats.pending || 0);
          this.completedPayments = parseInt(stats.completed || 0);
          this.failedPayments = parseInt(stats.failed || 0);
          
          console.log('Updated stats:', {
            pending: this.pendingPayments,
            completed: this.completedPayments,
            failed: this.failedPayments
          });
        } else {
          console.warn('Invalid response format:', response.data);
        }
      } catch (error) {
        console.error('Error fetching payments:', error);
        if (error.response) {
          console.error('Error response:', error.response.data);
          console.error('Error status:', error.response.status);
        }
        this.payments = [];
        this.pendingPayments = 0;
        this.completedPayments = 0;
        this.failedPayments = 0;
      }
    },

    updatePaymentStats() {
      this.pendingPayments = this.payments.filter(p => p.status === 'pending').length;
      this.completedPayments = this.payments.filter(p => p.status === 'completed').length;
      this.failedPayments = this.payments.filter(p => p.status === 'failed').length;
    },

    async updatePaymentStatus(paymentId, newStatus) {
      try {
        const response = await axios.patch(`/admin/update-payment/${paymentId}`, {
          status: newStatus
        });

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

          // Show success message using toast
          toast.show(
            `Payment ${newStatus === 'completed' ? 'confirmed' : 'rejected'} successfully`,
            'success'
          );

          // Refresh payments data
          await this.fetchPayments();
        }
      } catch (error) {
        console.error('Error updating payment status:', error);
        toast.show(
          'Failed to update payment status: ' + (error.response?.data?.message || error.message),
          'error'
        );
      }
    },

    async generateReceipt(paymentId) {
      try {
        console.log('Generating receipt for payment:', paymentId);
        const response = await axios.post(`/generate-receipt/${paymentId}`, {
          sendNotification: true
        });
        
        if (response.data.status === 'success') {
          console.log('Receipt generated successfully');
          // Open receipt in new tab
          window.open(response.data.data.receipt_url, '_blank');
          toast.show('Receipt generated and notification sent', 'success');
        }
      } catch (error) {
        console.error('Error generating receipt:', error);
        toast.show(
          'Failed to generate receipt: ' + (error.response?.data?.message || error.message),
          'error'
        );
      }
    },

    formatAmount(amount) {
      return parseFloat(amount).toFixed(2);
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    isWithinDateRange(date) {
      if (!this.filters.startDate && !this.filters.endDate) return true;
      
      const paymentDate = new Date(date);
      const startDate = this.filters.startDate ? new Date(this.filters.startDate) : null;
      const endDate = this.filters.endDate ? new Date(this.filters.endDate) : null;

      if (startDate && endDate) {
        return paymentDate >= startDate && paymentDate <= endDate;
      } else if (startDate) {
        return paymentDate >= startDate;
      } else if (endDate) {
        return paymentDate <= endDate;
      }
      return true;
    },

    viewProof(payment) {
      console.log('Payment proof URL:', payment.payment_proof);
      if (payment.payment_proof) {
        this.selectedPaymentProof = payment.payment_proof;
        this.selectedPaymentDetails = payment;
        this.showProofModal = true;
      } else {
        console.warn('No payment proof available');
        toast.show('No payment proof available', 'warning');
      }
    },

    handleImageError(e) {
      console.error('Error loading payment proof image');
      toast.show('Error loading payment proof image', 'error');
      this.showProofModal = false;
    },

    handleImageLoad() {
      console.log('Payment proof image loaded successfully');
    },

    goBack() {
      this.$router.push({ name: 'admin.dashboard' });
    },

    getTotalPaid(booking) {
      if (!booking?.payments) return 0;
      
      return booking.payments
        .filter(payment => 
          ['completed', 'confirmed'].includes(payment.status) && 
          payment.status !== 'failed'
        )
        .reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0);
    },

    getPaymentStatusClass(booking) {
      if (!booking?.payments) return 'unpaid';
      
      // Get only valid payments (not failed)
      const validPayments = booking.payments.filter(payment => 
        ['completed', 'confirmed'].includes(payment.status) && 
        payment.status !== 'failed'
      );
      
      if (!validPayments.length) return 'unpaid';
      
      const totalPrice = booking.room?.price || 0;
      const totalPaid = this.getTotalPaid(booking);
      
      console.log('Payment status calculation:', {
        bookingId: booking.booking_id,
        totalPrice,
        totalPaid,
        validPayments,
        allPayments: booking.payments
      });
      
      if (totalPaid >= totalPrice) return 'completed';
      if (totalPaid > 0) return 'partially_paid';
      return 'unpaid';
    },
    
    getPaymentStatusText(booking) {
      const status = this.getPaymentStatusClass(booking);
      switch (status) {
        case 'completed': return 'Fully Paid';
        case 'partially_paid': return 'Partially Paid';
        default: return 'Unpaid';
      }
    },

    formatPaymentMethod(method) {
      const methods = {
        'cash': 'Cash',
        'gcash_qr': 'GCash',
        'paymaya_qr': 'PayMaya'
      };
      return methods[method] || method;
    },

    openFullImage() {
      this.showFullImage = true;
    },

    closeFullImage() {
      this.showFullImage = false;
    }
  },
  created() {
    console.log('PaymentManagement component created');
    this.fetchPayments();
  },
  mounted() {
    console.log('PaymentManagement component mounted');
  }
}
</script>

<style scoped>
.payment-management {
  padding: 2rem;
}

.back-section {
  margin-bottom: 2rem;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
}

.arrow-left::before {
  content: "←";
  font-size: 1.2rem;
}

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

.stat-icon i {
  font-size: 1.5rem;
}

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

.payments-table {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #f8f9fa;
  font-weight: 600;
  color: #374151;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.pending { background: #fff7ed; color: #f59e0b; }
.status-badge.completed { background: #f0fdf4; color: #10b981; }
.status-badge.failed { background: #fef2f2; color: #ef4444; }

.payment-method {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
}

.payment-method.cash { background: #f3f4f6; }
.payment-method.gcash_qr { background: #e0f2fe; color: #0284c7; }
.payment-method.paymaya_qr { background: #faf5ff; color: #7c3aed; }

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-complete,
.btn-view {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-complete {
  background: #10b981;
  color: white;
}

.btn-view {
  background: #f3f4f6;
  color: #374151;
}

.btn-reject {
  background: #ef4444;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-reject:hover {
  background: #dc2626;
}

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
  overflow: auto;
  min-width: 400px;
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

.close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  z-index: 1;
}

.close:hover {
  color: #000;
}

.payment-progress {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
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

/* Update table styles for better alignment */
td {
  vertical-align: middle;
}

.payment-progress {
  white-space: nowrap;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
  
  .payment-progress {
    min-width: 120px;
  }
}

/* Add these payment status classes */
.payment-status.fully-paid {
    background: #dcfce7;
    color: #15803d;
}

.payment-status.partially-paid {
    background: #fef9c3;
    color: #854d0e;
}

.payment-status.unpaid {
    background: #fee2e2;
    color: #991b1b;
}

.payment-request-amount {
  font-size: 0.9rem;
  color: #374151;  /* Neutral dark gray color */
  background: #f3f4f6;  /* Light gray background */
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
}

.payment-paid-amount {
  font-size: 0.9rem;
  color: #374151;  /* Same neutral dark gray */
  background: #f3f4f6;  /* Same light gray background */
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
}

.payment-progress {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 150px;
  white-space: normal;
}

.payment-failed {
  background: #fee2e2;
  color: #dc2626;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
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
  border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item span {
  color: #374151;
  font-weight: 500;
}

.info-item strong {
  color: #794646;
  font-weight: 600;
}

.info-item .status-badge {
  font-size: 0.8rem;
  padding: 0.25rem 0.75rem;
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

/* Add styles for receipt button */
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

/* Add animation for receipt generation */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.generating-receipt {
  animation: pulse 1s infinite;
}
</style> 