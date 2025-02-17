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
      <div class="bookings-container">
        <h2 class="section-title">Bookings</h2>

        <!-- Filters Section -->
        <div class="filters-section">
          <div class="filter-group">
            <label>Status:</label>
            <select v-model="selectedStatus" @change="filterBookings">
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Boarding House:</label>
            <select v-model="selectedBoardingHouse" @change="filterBookings">
              <option value="all">All Boarding Houses</option>
              <option 
                v-for="house in boardingHouses" 
                :key="house.boarding_house_id" 
                :value="house.boarding_house_id"
              >
                {{ house.name }} ({{ house.occupied_rooms_count || 0 }} occupied, {{ house.active_bookings || 0 }} bookings)
              </option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="status-container">
          <img src="@/assets/images/loading.gif" alt="Loading" class="status-icon">
          <p class="status-text">Loading bookings...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!displayedBookings || displayedBookings.length === 0" class="status-container">
          <img src="@/assets/images/no-bookings.png" alt="No Bookings" class="status-icon">
          <p class="status-text">No bookings found</p>
        </div>

        <!-- Bookings Grid -->
        <div v-else class="booking-cards">
          <div v-for="booking in displayedBookings" :key="booking.id" class="booking-card">
            <div class="booking-header">
              <div class="booking-id">Booking #{{ booking.id }}</div>
              <div class="booking-status" :class="booking.status">
                {{ formatStatus(booking.status) }}
              </div>
            </div>

            <div class="booking-details">
              <div class="payment-status-section">
                <div class="payment-status" :class="booking.payment_status">
                  {{ formatPaymentStatus(booking) }}
                </div>
                <div class="payment-amount">
                  ₱{{ formatPrice(booking.total_paid) }} / ₱{{ formatPrice(booking.room.price) }}
                </div>
              </div>

              <div class="tenant-info">
                <img 
                  :src="getTenantProfilePicture(booking.user.profile_picture)"
                  :alt="booking.user.name"
                  class="tenant-icon"
                  @error="handleImageError"
                />
                <div class="tenant-details">
                  <span class="tenant-name">{{ booking.user.name }}</span>
                  <div class="tenant-info-grid">
                    <span class="tenant-detail">
                      <i class="fas fa-envelope"></i>
                      {{ booking.user.email }}
                    </span>
                    <span class="tenant-detail" v-if="booking.user.phone">
                      <i class="fas fa-phone"></i>
                      {{ booking.user.phone }}
                    </span>
                    <span class="tenant-detail">
                      <i class="fas fa-venus-mars"></i>
                      {{ formatGender(booking.user.gender) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="location-info">
                <div class="house-info">
                  <i class="fas fa-home"></i>
                  <span>{{ booking.room.boarding_house }}</span>
                </div>
                <div class="room-info">
                  <i class="fas fa-door-open"></i>
                  <span>{{ booking.room.name }}</span>
                </div>
              </div>

              <div class="dates-info">
                <div class="date-group">
                  <label>Check-in:</label>
                  <span>{{ formatDate(booking.check_in_date) }}</span>
                </div>
                <div class="date-group">
                  <label>Check-out:</label>
                  <span>{{ formatDate(booking.check_out_date) }}</span>
                </div>
              </div>

              <div class="payment-history" v-if="booking.payments && booking.payments.length > 0">
                <h4>Payment History</h4>
                <div class="payment-records">
                  <div v-for="payment in booking.payments" 
                       :key="payment.payment_id" 
                       class="payment-record">
                    <div class="payment-record-header">
                      <span class="payment-date">{{ formatDate(payment.created_at) }}</span>
                      <span class="payment-status" :class="payment.status">{{ payment.status }}</span>
                    </div>
                    <div class="payment-record-body">
                      <div class="payment-detail">
                        <i class="fas fa-money-bill"></i>
                        <span>₱{{ formatPrice(payment.amount) }}</span>
                      </div>
                      <div class="payment-detail">
                        <i class="fas fa-credit-card"></i>
                        <span>{{ formatPaymentMethod(payment.payment_method) }}</span>
                      </div>
                      <button 
                        v-if="payment.payment_proof" 
                        @click="viewPaymentProof(payment)"
                        class="view-proof-btn">
                        <i class="fas fa-receipt"></i>
                        View Proof
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="booking-actions">
              <button 
                v-if="booking.status === 'pending'"
                @click="confirmBooking(booking.id)" 
                class="action-btn confirm"
              >
                Confirm
              </button>
              <button 
                v-if="booking.status === 'pending'"
                @click="cancelBooking(booking.id)" 
                class="action-btn cancel"
              >
                Cancel
              </button>
              <button 
                @click="viewDetails(booking)" 
                class="action-btn view"
              >
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="selectedBooking" class="details-modal" @click="closeDetailsModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>{{ getModalTitle() }}</h2>
          <button class="close-modal" @click="closeDetailsModal">&times;</button>
        </div>

        <div class="modal-body">
          <!-- Payment Status Section -->
          <div class="detail-section payment-overview">
            <div class="payment-status-large" :class="selectedBooking.payment_status">
              {{ formatPaymentStatus(selectedBooking) }}
              <div class="payment-amount-large">
                ₱{{ formatPrice(selectedBooking.total_paid) }} / ₱{{ formatPrice(selectedBooking.room.price) }}
              </div>
            </div>
          </div>

          <!-- Tenant Profile Section -->
          <div class="detail-section tenant-profile-section">
            <div class="tenant-profile-header">
              <img 
                :src="getTenantProfilePicture(selectedBooking.user.profile_picture)"
                :alt="selectedBooking.user.name"
                class="tenant-profile-image"
                @error="handleImageError"
              />
              <div class="tenant-profile-info">
                <h3>{{ selectedBooking.user.name }}</h3>
                <span class="tenant-status" :class="selectedBooking.status">
                  {{ formatStatus(selectedBooking.status) }}
                </span>
              </div>
            </div>
            
            <div class="tenant-contact-info">
              <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <div class="contact-details">
                  <span class="contact-label">Email</span>
                  <span class="contact-value">{{ selectedBooking.user.email }}</span>
                </div>
              </div>
              <div class="contact-item">
                <i class="fas fa-phone"></i>
                <div class="contact-details">
                  <span class="contact-label">Phone</span>
                  <span class="contact-value">{{ selectedBooking.user.phone || 'Not provided' }}</span>
                </div>
              </div>
              <div class="contact-item">
                <i class="fas fa-venus-mars"></i>
                <div class="contact-details">
                  <span class="contact-label">Gender</span>
                  <span class="contact-value">
                    {{ formatGender(selectedBooking.user.gender) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Room Information -->
          <div class="detail-section">
            <h3>Room Information</h3>
            <div class="detail-grid">
              <div class="detail-row">
                <span class="detail-label">Room:</span>
                <span class="detail-value">{{ selectedBooking.room.name }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Boarding House:</span>
                <span class="detail-value">{{ selectedBooking.room.boarding_house }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Price:</span>
                <span class="detail-value">₱{{ formatPrice(selectedBooking.room.price) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment History Section -->
          <div class="detail-section">
            <h3>Payment History</h3>
            <div class="modal-payment-history">
              <div v-if="selectedBooking.payments && selectedBooking.payments.length > 0" 
                   class="modal-payment-records">
                <div v-for="payment in selectedBooking.payments" 
                     :key="payment.payment_id" 
                     class="modal-payment-record">
                  <div class="modal-payment-record-header">
                    <span class="payment-date">{{ formatDate(payment.created_at) }}</span>
                    <span class="payment-status" :class="payment.status">{{ payment.status }}</span>
                  </div>
                  <div class="modal-payment-record-body">
                    <div class="payment-detail">
                      <i class="fas fa-money-bill"></i>
                      <span>₱{{ formatPrice(payment.amount) }}</span>
                    </div>
                    <div class="payment-detail">
                      <i class="fas fa-credit-card"></i>
                      <span>{{ formatPaymentMethod(payment.payment_method) }}</span>
                    </div>
                    <button 
                      v-if="payment.payment_proof" 
                      @click="viewPaymentProof(payment)"
                      class="view-proof-btn">
                      <i class="fas fa-receipt"></i>
                      View Proof
                    </button>
                  </div>
                </div>
              </div>
              <div v-else class="no-payments">
                No payment records found
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <div v-if="selectedBooking.status === 'pending'" class="modal-actions">
            <button @click="confirmBooking(selectedBooking.booking_id)" class="action-btn confirm">
              Confirm Booking
            </button>
            <button @click="cancelBooking(selectedBooking.booking_id)" class="action-btn cancel">
              Cancel Booking
            </button>
          </div>
          <div v-if="selectedBooking.status === 'confirmed'" class="modal-actions">
            <button @click="deleteBooking(selectedBooking.booking_id)" class="action-btn delete">
              Delete Booking
            </button>
          </div>
          <button @click="closeDetailsModal" class="action-btn close">Close</button>
        </div>
      </div>
    </div>

    <!-- Payment Proof Modal -->
    <div v-if="showProofModal" class="proof-modal" @click="closeProofModal">
      <div class="proof-modal-content" @click.stop>
        <div class="proof-modal-header">
          <h3>Payment Proof</h3>
          <button class="close-modal" @click="closeProofModal">&times;</button>
        </div>
        <div class="proof-modal-body">
          <img 
            :src="selectedProofUrl" 
            alt="Payment Proof" 
            class="proof-image"
            @error="handleProofImageError"
            @click="zoomImage"
          />
        </div>
        <div class="proof-modal-footer">
          <div class="proof-details">
            <p><strong>Amount:</strong> ₱{{ formatPrice(selectedPayment?.amount) }}</p>
            <p><strong>Date:</strong> {{ formatDate(selectedPayment?.created_at) }}</p>
            <p><strong>Method:</strong> {{ formatPaymentMethod(selectedPayment?.payment_method) }}</p>
            <p><strong>Status:</strong> 
              <span class="payment-status" :class="selectedPayment?.status">
                {{ selectedPayment?.status }}
              </span>
            </p>
          </div>
          <button class="close-btn" @click="closeProofModal">Close</button>
        </div>
      </div>
    </div>

    <!-- Add this to your template section, after the proof modal -->
    <div v-if="showZoomedImage" class="zoom-modal" @click="closeZoomModal">
      <div class="zoom-content">
        <button class="zoom-close" @click.stop="closeZoomModal">&times;</button>
        <img 
          :src="selectedProofUrl" 
          alt="Zoomed Payment Proof" 
          class="zoomed-image"
          @error="handleProofImageError"
        />
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png';
import { EventBus } from '@/eventBus';

export default {
  name: 'AdminBookings',
  data() {
    return {
      loading: true,
      error: null,
      selectedBooking: null,
      adminProfile: {
        profile_picture: localStorage.getItem('profilePicture'),
        user_id: localStorage.getItem('userId'),
      },
      adminName: localStorage.getItem('userName') || 'Admin',
      defaultProfilePic: defaultProfilePic,
      selectedStatus: 'all',
      selectedBoardingHouse: 'all',
      boardingHouses: [],
      filteredBookings: [],
      allBookings: [],
      showProofModal: false,
      selectedProofUrl: null,
      selectedPayment: null,
      showZoomedImage: false,
    };
  },
  computed: {
    displayedBookings() {
      const bookings = this.filteredBookings;
      console.log('Displayed bookings:', bookings); // Debug log
      return bookings;
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

    navigateMenu(event) {
      const route = event.target.value;
      if (route === '/admin/admin_login') {
        this.logout();
      } else {
        this.$router.push(route);
      }
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

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    async fetchBoardingHouses() {
      try {
        const response = await axios.get('/admin/get-boarding-houses');
        console.log('Boarding houses response:', response.data);
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data;
          console.log('Stored boarding houses:', this.boardingHouses);
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
      }
    },

    async fetchBookings() {
      try {
        this.loading = true;
        const response = await axios.get('/admin/get-bookings');
        
        if (response.data.status === 'success') {
          console.log('Raw bookings data:', response.data.data);
          
          this.allBookings = response.data.data.map(booking => ({
            ...booking,
            payments: booking.payments || [],
            total_paid: booking.total_paid || 0,
            user: booking.user || {
              email: 'Email not provided',
              gender: 'Not specified'
            },
            room: {
              ...booking.room,
              price: booking.room?.price || 0
            }
          }));
          
          this.filterBookings();
        } else {
          throw new Error(response.data.message || 'Failed to fetch bookings');
        }
      } catch (error) {
        console.error('Error fetching bookings:', error);
        this.allBookings = [];
      } finally {
        this.loading = false;
      }
    },

    getRoomPrice(booking) {
      // Debug log to see the full booking object
      console.log('Full booking object:', booking);

      if (!booking) {
        console.warn('Booking is null or undefined');
        return 0;
      }

      // Try to get price from different possible paths
      const price = booking.room?.price || 
                    booking.room?.rental_price || 
                    booking.price || 
                    booking.room_price;

      console.log('Found price:', {
        bookingId: booking.booking_id,
        roomPrice: booking.room?.price,
        rentalPrice: booking.room?.rental_price,
        directPrice: booking.price,
        roomPrice2: booking.room_price,
        finalPrice: price
      });

      if (!price) {
        console.warn('No room price found for booking:', booking.booking_id);
        return 0;
      }

      const numPrice = parseFloat(price);
      return isNaN(numPrice) ? 0 : numPrice;
    },

    filterBookings() {
      let filtered = [...this.allBookings];

      // Filter by status
      if (this.selectedStatus !== 'all') {
        filtered = filtered.filter(booking => booking.status === this.selectedStatus);
      }

      // Filter by boarding house
      if (this.selectedBoardingHouse !== 'all') {
        filtered = filtered.filter(booking => 
          booking.room?.boarding_house_id === this.selectedBoardingHouse
        );
      }

      this.filteredBookings = filtered;
    },

    async confirmBooking(bookingId) {
      try {
        if (!confirm('Are you sure you want to confirm this booking?')) {
          return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await axios.post(`/admin/confirm-booking/${bookingId}`, null, {
          headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        if (response.data.status === 'success') {
          // Create notification for the user
          if (response.data.data && response.data.data.user_id) {
            await this.createNotification({
              user_id: response.data.data.user_id,
              type: 'booking',
              message: `Your booking #${bookingId} has been confirmed.`,
              link: '/user/bookings',
              status: 'confirmed'
            });
          }

          alert('Booking confirmed successfully');
          await this.fetchBookings();
          
          if (this.selectedBooking?.booking_id === bookingId) {
            this.closeDetailsModal();
          }
        } else {
          throw new Error(response.data.message || 'Failed to confirm booking');
        }
      } catch (error) {
        console.error('Error confirming booking:', error);
        alert(error.response?.data?.message || error.message || 'Failed to confirm booking. Please try again.');
        await this.fetchBookings();
      }
    },

    async cancelBooking(bookingId) {
      try {
        if (!confirm('Are you sure you want to cancel this booking?')) {
          return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await axios.post(`/admin/cancel-booking/${bookingId}`, null, {
          headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        if (response.data.status === 'success') {
          // Create notification for the user
          if (response.data.data && response.data.data.user_id) {
            await this.createNotification({
              user_id: response.data.data.user_id,
              type: 'booking',
              message: `Your booking #${bookingId} has been cancelled by the admin.`,
              link: '/user/bookings',
              status: 'cancelled'
            });
          }

          alert('Booking cancelled successfully');
          await this.fetchBookings();
          
          if (this.selectedBooking?.booking_id === bookingId) {
            this.closeDetailsModal();
          }
        } else {
          throw new Error(response.data.message || 'Failed to cancel booking');
        }
      } catch (error) {
        console.error('Error cancelling booking:', error);
        alert(error.response?.data?.message || error.message || 'Failed to cancel booking. Please try again.');
        await this.fetchBookings();
      }
    },

    viewDetails(booking) {
      console.log('Opening modal for booking:', booking);
      
      // Create a deep copy of the booking to avoid reactivity issues
      this.selectedBooking = {
        ...JSON.parse(JSON.stringify(booking)),
        booking_id: booking.booking_id,
        room_name: booking.room_name,
        boarding_house_name: booking.boarding_house_name,
        tenant_name: booking.tenant_name,
        tenant_email: booking.tenant_email,
        tenant_phone: booking.tenant_phone,
        tenant_gender: booking.tenant_gender,
        tenant_profile: booking.tenant_profile,
        status: booking.status,
        payments: booking.payments || [],
        room: {
          ...booking.room,
          price: booking.room?.price || 0
        }
      };
      
      console.log('Selected booking data:', this.selectedBooking);
    },

    closeDetailsModal() {
      this.selectedBooking = null;
    },

    getTenantProfilePicture(profilePicture) {
      if (profilePicture && profilePicture.trim() !== '') {
        // If the path doesn't start with http or https, assume it's a storage path
        if (!profilePicture.startsWith('http') && !profilePicture.startsWith('https')) {
          return `/storage/${profilePicture}`;
        }
        return profilePicture;
      }
      return this.defaultProfilePic;
    },

    async createNotification(notificationData) {
      try {
        const response = await axios.post('/notifications/create', notificationData);
        if (response.data.status !== 'success') {
          console.error('Failed to create notification:', response.data.message);
        }
      } catch (error) {
        console.error('Error creating notification:', error);
        // Don't throw the error - we don't want notification failures to affect the booking confirmation
      }
    },

    calculateDuration(checkIn, checkOut) {
      if (!checkIn || !checkOut) return 'N/A';
      
      const start = new Date(checkIn);
      const end = new Date(checkOut);
      
      // Check if dates are valid
      if (isNaN(start.getTime()) || isNaN(end.getTime())) {
        return 'Invalid dates';
      }
      
      const diffTime = Math.abs(end - start);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      if (diffDays === 1) {
        return '1 day';
      }
      return `${diffDays} days`;
    },

    async deleteBooking(bookingId) {
      try {
        if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
          return;
        }

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await axios.delete(`/admin/delete-booking/${bookingId}`, {
          headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        if (response.data.status === 'success') {
          // Create notification for the user
          if (response.data.data && response.data.data.user_id) {
            await this.createNotification({
              user_id: response.data.data.user_id,
              type: 'booking',
              message: `Your booking #${bookingId} has been deleted by the admin.`,
              link: '/user/bookings',
              status: 'deleted'
            });
          }

          alert('Booking deleted successfully');
          await this.fetchBookings();
          
          if (this.selectedBooking?.booking_id === bookingId) {
            this.closeDetailsModal();
          }
        } else {
          throw new Error(response.data.message || 'Failed to delete booking');
        }
      } catch (error) {
        console.error('Error deleting booking:', error);
        alert(error.response?.data?.message || error.message || 'Failed to delete booking. Please try again.');
        await this.fetchBookings();
      }
    },

    formatGender(gender) {
      if (!gender) return 'Not specified';
      return gender.charAt(0).toUpperCase() + gender.slice(1);
    },

    formatStatus(bookingStatus) {
      return bookingStatus.charAt(0).toUpperCase() + bookingStatus.slice(1);
    },

    calculatePaymentStatus(booking) {
      const payments = booking.payments || [];
      if (payments.length === 0) {
        return 'unpaid';
      }
      
      const totalAmount = parseFloat(booking.room.price);
      const completedPayments = payments.filter(payment => 
        ['confirmed', 'completed'].includes(payment.status)
      );
      
      const paidAmount = completedPayments.reduce((sum, payment) => 
        sum + parseFloat(payment.amount), 0
      );
      
      if (paidAmount >= totalAmount) return 'paid';
      if (paidAmount > 0) return 'partially_paid';
      return 'unpaid';
    },

    calculateTotalPaid(booking) {
      if (!booking?.payments) return '0.00';
      
      try {
        const completedPayments = booking.payments.filter(payment => 
          payment.status === 'completed' || payment.status === 'confirmed'
        );
        
        const total = completedPayments.reduce((sum, payment) => {
          const amount = parseFloat(payment.amount || 0);
          return isNaN(amount) ? sum : sum + amount;
        }, 0);
        
        return total.toFixed(2);
      } catch (error) {
        console.error('Error calculating total paid:', error);
        return '0.00';
      }
    },

    async refreshBookings() {
      await this.fetchBookings();
    },

    getPaymentStatus(booking) {
      if (!booking?.payments?.length) return 'unpaid';
      
      const totalPrice = this.getRoomPrice(booking);
      const totalPaid = parseFloat(this.calculateTotalPaid(booking)) || 0;
      
      console.log('Payment status calculation:', {
        bookingId: booking.booking_id,
        totalPrice,
        totalPaid,
        payments: booking.payments
      });
      
      if (totalPaid >= totalPrice) return 'paid';
      if (totalPaid > 0) return 'partially_paid';
      return 'unpaid';
    },
    
    formatPaymentStatus(booking) {
      const status = this.getPaymentStatus(booking);
      switch (status) {
        case 'paid': return 'Fully Paid';
        case 'partially_paid': return 'Partially Paid';
        default: return 'Unpaid';
      }
    },
    
    calculateTotalPaid(booking) {
      if (!booking?.payments) return '0.00';
      
      const completedPayments = booking.payments.filter(payment => 
        payment.status === 'completed' || payment.status === 'confirmed'
      );
      
      const total = completedPayments.reduce((sum, payment) => {
        const amount = parseFloat(payment.amount) || 0;
        return sum + amount;
      }, 0);
      
      return total.toFixed(2);
    },
    
    formatPaymentMethod(method) {
      const methods = {
        cash: 'Cash',
        gcash_qr: 'GCash',
        paymaya_qr: 'PayMaya'
      };
      return methods[method] || method;
    },

    formatPrice(price) {
      const numPrice = parseFloat(price);
      return isNaN(numPrice) ? '0.00' : numPrice.toFixed(2);
    },

    // Add this method to check if modal should be shown
    showModal() {
      return this.selectedBooking !== null;
    },

    // Add this method to properly format the modal title
    getModalTitle() {
      if (!this.selectedBooking) return '';
      return `Booking #${this.selectedBooking.booking_id}`;
    },

    viewPaymentProof(payment) {
      if (payment.payment_proof) {
        this.selectedPayment = payment;
        this.selectedProofUrl = `/storage/${payment.payment_proof}`;
        this.showProofModal = true;
        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
      }
    },

    closeProofModal() {
      this.showProofModal = false;
      this.showZoomedImage = false;
      this.selectedProofUrl = null;
      this.selectedPayment = null;
      // Restore body scrolling when modal is closed
      document.body.style.overflow = 'auto';
    },

    handleProofImageError(e) {
      e.target.src = '@/assets/images/error-image.png'; // Replace with your error image
      console.error('Error loading payment proof image');
    },

    zoomImage() {
      this.showZoomedImage = true;
      document.body.style.overflow = 'hidden';
    },

    closeZoomModal() {
      this.showZoomedImage = false;
      document.body.style.overflow = 'auto';
    },
  },
  created() {
    // Add event listener for payment updates
    EventBus.on('payment-updated', this.fetchBookings);
    
    // Set up axios default headers for CSRF
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    
    this.fetchBoardingHouses();
    this.fetchBookings();
  },
  beforeUnmount() {
    // Remove event listener
    EventBus.off('payment-updated', this.fetchBookings);
  }
};
</script>

<style scoped>
@import '@/assets/css/admin_panel.css';

.overall {
  padding: 2rem;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px);
}

.bookings-container {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.section-title {
  color: #794646;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f3f4f6;
}

/* Status Container */
.status-container {
  text-align: center;
  padding: 4rem 2rem;
  background: #f9fafb;
  border-radius: 8px;
  margin: 2rem 0;
}

.status-icon {
  width: 64px;
  height: 64px;
  margin-bottom: 1rem;
  opacity: 0.7;
}

.status-text {
  color: #6b7280;
  font-size: 1.1rem;
}

/* Booking Cards Grid */
.booking-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  padding: 1rem 0;
}

.booking-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Booking Card Header */
.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.booking-id {
  font-size: 1.1rem;
  font-weight: 600;
  color: #794646;
}

/* Status Badges */
.booking-status {
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: capitalize;
}

.booking-status.pending {
  background: #fff7ed;
  color: #c2410c;
  border: 1px solid #fdba74;
}

.booking-status.confirmed {
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #86efac;
}

.booking-status.cancelled {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

.booking-status.paid {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.booking-status.partially_paid {
  background: #fef9c3;
  color: #854d0e;
  border: 1px solid #fde047;
}

.booking-status.unpaid {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

/* Booking Details */
.booking-details {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  align-items: center;
  margin-bottom: 0.75rem;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.detail-row .label {
  width: 120px;
  color: #6b7280;
  font-weight: 500;
  font-size: 0.9rem;
}

.detail-row .value {
  flex: 1;
  color: #374151;
  font-weight: 500;
}

/* Action Buttons */
.booking-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.action-btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .booking-cards {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .bookings-container {
    padding: 1rem;
  }

  .booking-cards {
    grid-template-columns: 1fr;
  }

  .booking-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }

  .detail-row .label {
    width: 100%;
  }
}

/* Profile Section Styles */
.profile-section {
  margin-left: auto;
  margin-right: 2rem;
  display: flex;
  align-items: center;
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

/* Menu Container Styles */
.menu-container {
  display: flex;
  align-items: center;
  margin-left: 1rem;
}

.menuimgg {
  width: 24px;
  height: 24px;
  margin-right: 0.5rem;
}


/* Responsive Design */
@media (max-width: 768px) {
  .navbars {
    padding: 0 1rem;
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

.filters-section {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f8fafc;
  border-radius: 8px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.filter-group label {
  color: #794646;
  font-weight: 600;
  font-size: 0.95rem;
  white-space: nowrap;
}

.filter-group select {
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 0.95rem;
  min-width: 200px;
  cursor: pointer;
}

.filter-group select:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

@media (max-width: 768px) {
  .filters-section {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }

  .filter-group {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .filter-group select {
    width: 100%;
  }
}

/* Add these new styles */
.details-modal {
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

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-modal {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0.5rem;
}

.close-modal:hover {
  color: #333;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
}

@media (max-width: 640px) {
  .modal-content {
    width: 95%;
    margin: 1rem;
  }

  .modal-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }
}

.tenant-profile {
  text-align: center;
  margin-bottom: 1.5rem;
}

.tenant-profile-image {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1rem;
}

.detail-section {
  margin-bottom: 2rem;
}

.detail-section h3 {
  color: #794646;
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.detail-section:last-child {
  margin-bottom: 0;
}

.detail-row {
  display: flex;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-weight: 600;
  color: #374151;
  width: 120px;
  flex-shrink: 0;
}

.detail-value {
  color: #4b5563;
  flex-grow: 1;
}

.detail-value.pending {
  color: #c2410c;
  font-weight: 600;
}

.detail-value.confirmed {
  color: #15803d;
  font-weight: 600;
}

.detail-value.cancelled {
  color: #b91c1c;
  font-weight: 600;
}

@media (max-width: 640px) {
  .modal-content {
    width: 95%;
    margin: 1rem;
  }

  .modal-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }
}

/* Add these new styles */
.tenant-profile-section {
  text-align: center;
  padding: 2rem;
  background: #f8fafc;
  border-radius: 12px;
  margin-bottom: 2rem;
}

.tenant-profile-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.tenant-profile-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.tenant-profile-info {
  text-align: center;
}

.tenant-profile-info h3 {
  color: #374151;
  font-size: 1.5rem;
  margin: 0 0 0.5rem 0;
}

.tenant-contact-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.contact-item i {
  color: #794646;
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
}

.contact-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  text-align: left;
}

.contact-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.contact-value {
  color: #374151;
  font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .tenant-profile-section {
    padding: 1rem;
  }

  .tenant-contact-info {
    grid-template-columns: 1fr;
  }

  .contact-item {
    padding: 0.75rem;
  }
}

/* Update these styles */
.tenant-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.tenant-profile-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
  background-color: #f8fafc; /* Light background for empty state */
}

.tenant-info {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  margin-bottom: 1rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.tenant-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.tenant-name {
  font-weight: 600;
  color: #374151;
  font-size: 1.1rem;
}

.tenant-email,
.tenant-contact,
.tenant-gender {
  font-size: 0.875rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tenant-email i,
.tenant-contact i,
.tenant-gender i {
  color: #794646;
  font-size: 0.875rem;
  width: 16px;
}

/* Responsive adjustments */
@media (max-width: 480px) {
  .tenant-info {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .tenant-details {
    align-items: center;
  }

  .tenant-email,
  .tenant-contact,
  .tenant-gender {
    justify-content: center;
  }
}

/* Add or update these styles */
.location-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.house-info,
.room-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #4b5563;
}

.house-info i,
.room-info i {
  color: #794646;
  width: 16px;
}

.tenant-info {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  margin-bottom: 1rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.tenant-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.tenant-email,
.tenant-contact,
.tenant-gender {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.tenant-email i,
.tenant-contact i,
.tenant-gender i {
  color: #794646;
  width: 16px;
}

@media (max-width: 480px) {
  .tenant-info,
  .location-info {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .tenant-details,
  .house-info,
  .room-info {
    align-items: center;
  }
}

/* Add or update these styles */
.tenant-info-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.tenant-detail {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.tenant-detail i {
  color: #794646;
  width: 16px;
  font-size: 0.875rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.contact-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.contact-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.contact-value {
  color: #374151;
  font-weight: 500;
}

.contact-item i {
  color: #794646;
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
}

@media (max-width: 480px) {
  .tenant-info-grid {
    align-items: center;
  }
  
  .tenant-detail {
    justify-content: center;
  }
  
  .contact-item {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 1.5rem;
  }
  
  .contact-details {
    align-items: center;
  }
}

.action-btn.delete {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.action-btn.delete:hover {
  background-color: #bb2d3b;
}

/* Payment Status Styles */
.payment-status-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.payment-status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.payment-status.paid {
  background: #dcfce7;
  color: #15803d;
}

.payment-status.partially_paid {
  background: #fef9c3;
  color: #854d0e;
}

.payment-status.unpaid {
  background: #fee2e2;
  color: #991b1b;
}

.payment-amount {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

/* Payment History Styles */
.payment-history {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.payment-history h4 {
  color: #374151;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.payment-records {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.payment-record {
  background: white;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.payment-record-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.payment-date {
  font-size: 0.75rem;
  color: #6b7280;
}

.payment-status {
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
}

.payment-status.completed,
.payment-status.confirmed {
  background: #dcfce7;
  color: #15803d;
}

.payment-status.pending {
  background: #fff7ed;
  color: #c2410c;
}

.payment-status.failed {
  background: #fee2e2;
  color: #991b1b;
}

.payment-record-body {
  padding: 0.75rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.payment-detail {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}

.payment-detail i {
  color: #794646;
  width: 16px;
}

.view-proof-btn {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  background: #794646;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.view-proof-btn:hover {
  background: #633939;
}

.view-proof-btn i {
  font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .payment-record-body {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .view-proof-btn {
    margin-left: 0;
    width: 100%;
    justify-content: center;
  }
}

/* Payment Proof Modal Styles */
.proof-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.proof-modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.proof-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.proof-modal-header h3 {
  color: #374151;
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.close-modal {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  padding: 0.5rem;
  line-height: 1;
}

.close-modal:hover {
  color: #374151;
}

.proof-modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8fafc;
}

.proof-image {
  max-width: 100%;
  max-height: 60vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  cursor: zoom-in; /* Add cursor indicator for zoom */
  transition: transform 0.2s ease;
}

.proof-image:hover {
  transform: scale(1.02);
}

.proof-modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: white;
}

.proof-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.proof-details p {
  margin: 0;
  font-size: 0.875rem;
  color: #374151;
}

.proof-details strong {
  color: #6b7280;
  margin-right: 0.5rem;
}

.close-btn {
  width: 100%;
  padding: 0.75rem;
  background: #794646;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.close-btn:hover {
  background: #633939;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .proof-modal {
    padding: 0.5rem;
  }

  .proof-modal-content {
    max-height: 95vh;
  }

  .proof-details {
    grid-template-columns: 1fr;
  }
}

/* Animation for modal */
@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.proof-modal-content {
  animation: modalFadeIn 0.3s ease-out;
}

/* Zoom Modal Styles */
.zoom-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100; /* Higher than proof modal */
  padding: 1rem;
}

.zoom-content {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zoomed-image {
  max-width: 95%;
  max-height: 95vh;
  object-fit: contain;
  border-radius: 8px;
  cursor: zoom-out;
}

.zoom-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 2rem;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s;
}

.zoom-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Animation for zoom modal */
@keyframes zoomIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.zoomed-image {
  animation: zoomIn 0.3s ease-out;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .zoom-modal {
    padding: 0.5rem;
  }

  .zoom-close {
    top: 0.5rem;
    right: 0.5rem;
  }
}

/* Add these new styles */
.payment-overview {
  text-align: center;
  padding: 2rem;
  background: #f8fafc;
  border-radius: 12px;
  margin-bottom: 2rem;
}

.payment-status-large {
  font-size: 1.5rem;
  font-weight: 600;
  padding: 1rem 2rem;
  border-radius: 12px;
  display: inline-flex;
  flex-direction: column;
  gap: 0.5rem;
}

.payment-status-large.paid {
  background: #dcfce7;
  color: #15803d;
}

.payment-status-large.partially_paid {
  background: #fef9c3;
  color: #854d0e;
}

.payment-status-large.unpaid {
  background: #fee2e2;
  color: #991b1b;
}

.payment-amount-large {
  font-size: 1.25rem;
  opacity: 0.9;
}

.modal-payment-history {
  background: #f8fafc;
  border-radius: 8px;
  overflow: hidden;
}

.modal-payment-records {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1rem;
}

.modal-payment-record {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.modal-payment-record-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.modal-payment-record-body {
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.no-payments {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
  font-style: italic;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .payment-status-large {
    width: 100%;
    font-size: 1.25rem;
    padding: 0.75rem 1rem;
  }

  .payment-amount-large {
    font-size: 1rem;
  }

  .modal-payment-record-body {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .view-proof-btn {
    width: 100%;
    justify-content: center;
  }
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