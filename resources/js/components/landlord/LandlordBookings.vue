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

        <h4 class="helper">BOOKINGS</h4>

      </div>



      <div class="bookings-wrapper">

        <div class="filters">

          <select v-model="selectedStatus" class="filter-select">

            <option value="all">All Bookings</option>

            <option value="pending">Pending</option>

            <option value="confirmed">Confirmed</option>

            <option value="cancelled">Cancelled</option>

          </select>



          <select v-model="selectedHouse" class="filter-select">

            <option value="all">All Boarding Houses</option>

            <option v-for="house in boardingHouses" :key="house.id" :value="house.id">

              {{ house.name }}

            </option>

          </select>

        </div>



        <div class="bookings-container">

          <div v-if="loading" class="status-container">

            <img src="@/assets/images/loading.gif" alt="Loading" class="status-icon">

            <p class="status-text">Loading bookings...</p>

          </div>



          <div v-else-if="filteredBookings.length === 0" class="status-container">

            <img src="@/assets/images/no-bookings.png" alt="No Bookings" class="status-icon">

            <p class="status-text">No bookings found</p>

            <p class="status-subtext">{{ getEmptyStateMessage() }}</p>

          </div>



          <div v-else class="bookings-grid">

            <div v-for="booking in filteredBookings" :key="booking.id" class="booking-card">

              <div class="booking-header">

                <span class="booking-reference">Booking #{{ booking.booking_id }}</span>

                <span :class="['booking-status', booking.status]">{{ booking.status }}</span>

              </div>



              <div class="booking-details">

                <div class="tenant-profile-section">

                  <div class="tenant-avatar-container">

                    <img 

                      :src="getTenantProfilePicture(booking.tenant_profile)"

                      :alt="booking.tenant_name" 

                      class="tenant-profile-image"

                      @error="handleImageError"

                    />

                  </div>

                  <div class="tenant-info-container">

                    <h3 class="tenant-name">{{ booking.tenant_name }}</h3>

                    <p class="tenant-contact" v-if="booking.tenant_contact">

                      <i class="fas fa-phone-alt"></i> {{ booking.tenant_contact }}

                    </p>

                    <p class="tenant-email" v-if="booking.tenant_email">

                      <i class="fas fa-envelope"></i> {{ booking.tenant_email }}

                    </p>

                  </div>

                </div>



                <h3 class="room-name">{{ booking.room_name }}</h3>

                <p class="house-name">{{ booking.house_name }}</p>

                

                <div class="payment-info">

                  <div class="price-info">

                    <span class="detail-label">Room Price:</span>

                    <span class="detail-value">₱{{ formatAmount(getRoomPrice(booking)) }}</span>

                  </div>

                  <div class="payment-progress">

                    <div class="amount-info">

                      <span class="paid-amount">₱{{ formatAmount(getTotalPaid(booking)) }}</span>

                      <span class="total-amount">/ ₱{{ formatAmount(getRoomPrice(booking)) }}</span>

                    </div>

                    <div class="payment-status-badge" :class="getPaymentStatusClass(booking)">

                      {{ getPaymentStatusText(booking) }}

                    </div>

                  </div>

                </div>



                <div class="booking-reference-info">

                  <span class="detail-label">Reference ID:</span>

                  <span class="detail-value">#{{ booking.booking_id }}</span>

                </div>

                <div class="booking-dates">

                  <div>Check-in: {{ formatDate(booking.check_in_date) }}</div>

                  <div>Check-out: {{ formatDate(booking.check_out_date) }}</div>

                </div>

              </div>



              <div class="booking-actions">

                <button 

                  v-if="booking.status === 'pending'"

                  @click="confirmBooking(booking.booking_id)" 

                  class="action-btn confirm"

                >

                  Confirm

                </button>

                <button 

                  v-if="booking.status === 'pending'"

                  @click="cancelBooking(booking.booking_id)" 

                  class="action-btn cancel"

                >

                  Cancel

                </button>

                <button 

                  @click="viewDetails(booking.booking_id)" 

                  class="action-btn view"

                >

                  View Details

                </button>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>



    <div v-if="selectedBooking" class="details-modal" @click="closeDetailsModal">

      <div class="modal-content details-modal-content" @click.stop>

        <div class="modal-header">

          <h2>Booking Details #{{ selectedBooking.booking_id }}</h2>

          <button class="close-modal" @click="closeDetailsModal">&times;</button>

        </div>



        <div class="modal-body">

          <div class="details-info">

            <!-- Room Information -->

            <div class="detail-section">

              <h3>Room Information</h3>

              <div class="detail-row">

                <span class="detail-label">Room:</span>

                <span class="detail-value">{{ selectedBooking.room_name }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Boarding House:</span>

                <span class="detail-value">{{ selectedBooking.house_name }}</span>

              </div>

            </div>



            <!-- Tenant Information -->

            <div class="detail-section">

              <h3>Tenant Information</h3>

              <div class="tenant-profile">

                <img 

                  :src="getTenantProfilePicture(selectedBooking.tenant_profile)"

                  :alt="selectedBooking.tenant_name" 

                  class="tenant-profile-image"

                  @error="handleImageError"

                >

              </div>

              <div class="detail-row">

                <span class="detail-label">Name:</span>

                <span class="detail-value">{{ selectedBooking.tenant_name }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Email:</span>

                <span class="detail-value">{{ selectedBooking.tenant_email || 'Not provided' }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Contact:</span>

                <span class="detail-value">{{ selectedBooking.tenant_contact || 'Not provided' }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Gender:</span>

                <span class="detail-value">{{ formatGender(selectedBooking.tenant_gender) || 'Not provided' }}</span>

              </div>

            </div>



            <!-- Booking Details -->

            <div class="detail-section">

              <h3>Booking Details</h3>

              <div class="detail-row">

                <span class="detail-label">Status:</span>

                <span class="detail-value" :class="selectedBooking.status">

                  {{ selectedBooking.status }}

                </span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Check-in:</span>

                <span class="detail-value">{{ formatDate(selectedBooking.check_in_date) }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Check-out:</span>

                <span class="detail-value">{{ formatDate(selectedBooking.check_out_date) }}</span>

              </div>

            </div>



            <!-- Payment Information -->

            <div class="detail-section">

              <h3>Payment Information</h3>

              <div class="detail-row">

                <span class="detail-label">Room Price:</span>

                <span class="detail-value">₱{{ formatAmount(selectedBooking.room?.price || 0) }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Amount Paid:</span>

                <span class="detail-value">₱{{ formatAmount(getTotalPaid(selectedBooking)) }}</span>

              </div>

              <div class="detail-row">

                <span class="detail-label">Payment Status:</span>

                <span class="detail-value" :class="getPaymentStatusClass(selectedBooking)">

                  {{ getPaymentStatusText(selectedBooking) }}

                </span>

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

          <button @click="closeDetailsModal" class="action-btn close">Close</button>

        </div>

      </div>

    </div>

  </div>

</template>



<script>

import axios from 'axios';

import defaultProfilePic from '@/assets/images/Profile.png';



export default {

  name: 'LandlordBookings',

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

      defaultProfilePic: '/images/default-profile.png',

      bookings: [],

      boardingHouses: [],

      selectedStatus: 'all',

      selectedHouse: 'all',

      loading: true,

      selectedBooking: null,

    };

  },

  computed: {

    filteredBookings() {

      return this.bookings.filter(booking => {

        const statusMatch = this.selectedStatus === 'all' || booking.status === this.selectedStatus;

        const houseMatch = this.selectedHouse === 'all' || booking.house_id === this.selectedHouse;

        return statusMatch && houseMatch;

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

        // Clear all localStorage items

        localStorage.removeItem('token');

        localStorage.removeItem('userName');

        localStorage.removeItem('userId');

        localStorage.removeItem('userType');

        localStorage.removeItem('profilePicture');

        localStorage.removeItem('userEmail');

        localStorage.removeItem('userPhone');

        localStorage.removeItem('userGender');

        localStorage.removeItem('userBirthdate');

        // Remove Authorization header

        delete axios.defaults.headers.common['Authorization'];

        // Redirect to login page

        this.$router.push('/landlord/login');

      } catch (error) {

        console.error('Logout error:', error);

      }

    },

    formatDate(date) {

      return new Date(date).toLocaleDateString('en-US', {

        year: 'numeric',

        month: 'long',

        day: 'numeric'

      });

    },

    async fetchBookings() {

      try {

        const userId = localStorage.getItem('userId');

        if (!userId) {

          throw new Error('User ID not found in localStorage');

        }



        const response = await axios.get('/landlord/get-bookings', {

          headers: {

            'Accept': 'application/json',

            'X-Requested-With': 'XMLHttpRequest',

            'X-User-Id': userId

          }

        });

        

        if (response.data.status === 'success') {

          // Debug: Log the raw response

          console.log('Raw API Response:', response.data);



          // Map the response data to include room relationship

          this.bookings = (response.data.data || []).map(booking => ({

            ...booking,

            room: booking.room || {

              price: booking.room_price || 0

            }

          }));

          

          // Debug: Log a sample booking

          if (this.bookings.length > 0) {

            console.log('Sample Booking Structure:', {

              room: this.bookings[0].room,

              room_price: this.bookings[0].room_price,

              full_booking: this.bookings[0]

            });

          }

        } else {

          throw new Error(response.data.message || 'Failed to fetch bookings');

        }

      } catch (error) {

        console.error('Error fetching bookings:', error);

        

        if (error.response?.status === 401) {

          alert('Please log in again');

          this.$router.push('/landlord/login');

          return;

        }

        

        this.error = error.response?.data?.message || 'Failed to load bookings';

        this.bookings = [];

      } finally {

        this.loading = false;

      }

    },

    async fetchBoardingHouses() {

      try {

        const response = await axios.get('/api/landlord/boarding-houses');

        this.boardingHouses = response.data;

      } catch (error) {

        console.error('Error fetching boarding houses:', error);

      }

    },

    async confirmBooking(bookingId) {
      try {
        const userId = localStorage.getItem('userId');
        // First check if the booking exists and is in pending status
        const booking = this.bookings.find(b => b.booking_id === bookingId);
        if (!booking) {
          throw new Error('Booking not found');
        }
        
        if (booking.status !== 'pending') {
          throw new Error('Only pending bookings can be confirmed');
        }

        if (!confirm('Are you sure you want to confirm this booking?')) {
          return;
        }

        const response = await axios.post(`/landlord/bookings/${bookingId}/confirm`, {
          landlord_id: userId
        }, {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          // Create notification for the tenant
          await this.createNotification({
            user_id: booking.user_id, // The tenant's user ID
            type: 'booking',
            message: `Your booking for ${booking.room_name} has been confirmed.`,
            link: '/user/bookings',
            booking_id: bookingId,
            status: 'confirmed'
          });

          await this.fetchBookings();
          this.closeDetailsModal();
          alert('Booking confirmed successfully');
        }
      } catch (error) {
        console.error('Error confirming booking:', error);
        alert(error.response?.data?.message || error.message || 'Failed to confirm booking');
      }
    },

    async cancelBooking(bookingId) {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.post(`/landlord/bookings/${bookingId}/cancel`, null, {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          // Get the booking details for the notification
          const booking = this.bookings.find(b => b.booking_id === bookingId);
          
          // Create notification for the tenant
          await this.createNotification({
            user_id: booking.user_id, // The tenant's user ID
            type: 'booking',
            message: `Your booking for ${booking.room_name} has been cancelled by the landlord.`,
            link: '/user/bookings',
            booking_id: bookingId,
            status: 'cancelled'
          });

          await this.fetchBookings();
          this.closeDetailsModal();
          alert('Booking cancelled successfully');
        }
      } catch (error) {
        console.error('Error cancelling booking:', error);
        alert(error.response?.data?.message || 'Failed to cancel booking');
      }
    },

    async viewDetails(bookingId) {

      this.selectedBooking = this.filteredBookings.find(booking => booking.booking_id === bookingId);

    },

    closeDetailsModal() {

      this.selectedBooking = null;

    },

    getEmptyStateMessage() {

      if (this.selectedStatus !== 'all' || this.selectedHouse !== 'all') {

        return 'Try adjusting your filters to see more bookings';

      }

      return 'When you receive bookings, they will appear here';

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

          

          // Update localStorage

          localStorage.setItem('userName', userData.name);

          if (userData.profile_picture) {

            localStorage.setItem('profilePicture', userData.profile_picture);

          }

        }

      } catch (error) {

        console.error('Error fetching landlord profile:', error);

      }

    },

    getTenantProfilePicture(profilePicture) {
      if (profilePicture && profilePicture.trim() !== '') {
        return profilePicture;
      }
      return this.defaultProfilePic;
    },

    async createNotification(notificationData) {
      try {
        const response = await axios.post('/notifications/create', {
          ...notificationData,
          created_at: new Date().toISOString(),
          read: false
        });

        if (response.data.status !== 'success') {
          throw new Error(response.data.message || 'Failed to create notification');
        }
      } catch (error) {
        console.error('Error creating notification:', error);
      }
    },

    formatGender(gender) {
      if (!gender) return null;
      return gender.charAt(0).toUpperCase() + gender.slice(1);
    },

    formatAmount(amount) {
      return parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },

    getTotalPaid(booking) {
      if (!booking?.payments) return 0;
      
      return booking.payments
        .filter(payment => payment.status === 'completed')
        .reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0);
    },

    getPaymentStatusText(booking) {
      if (!booking?.payments) return 'Unpaid';
      
      const totalPrice = this.getRoomPrice(booking);
      const totalPaid = this.getTotalPaid(booking);
      
      if (totalPaid >= totalPrice) return 'Fully Paid';
      if (totalPaid > 0) return 'Partially Paid';
      return 'Unpaid';
    },

    getPaymentStatusClass(booking) {
      if (!booking?.payments) return 'unpaid';
      
      const totalPrice = this.getRoomPrice(booking);
      const totalPaid = this.getTotalPaid(booking);
      
      if (totalPaid >= totalPrice) return 'completed';
      if (totalPaid > 0) return 'partially_paid';
      return 'unpaid';
    },

    getRoomPrice(booking) {
      // Debug log to see the full booking object
      console.log('Full booking object:', booking);

      if (!booking) {
        console.warn('Booking is null or undefined');
        return 0;
      }

      // Get price from room relationship
      const price = booking.room?.price;

      console.log('Found price:', {
        bookingId: booking.booking_id,
        roomPrice: price,
        finalPrice: price
      });

      const numPrice = parseFloat(price);
      return isNaN(numPrice) ? 0 : numPrice;
    },

  },

  async created() {

    await this.fetchLandlordProfile();

    await Promise.all([

      this.fetchBookings(),

      this.fetchBoardingHouses()

    ]);

  }

};

</script>



<style scoped>

@import '@/assets/css/landlord_panel.css';



.bookings-wrapper {

  background: white;

  border-radius: 12px;

  padding: 2rem;

  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

  margin-top: 2rem;

}



.status-container {

  display: flex;

  flex-direction: column;

  align-items: center;

  justify-content: center;

  padding: 4rem 2rem;

  text-align: center;

  background: #f9fafb;

  border-radius: 8px;

  margin-top: 2rem;

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



.filter-select:hover {

  border-color: #d1d5db;

}



.filter-select:focus {

  outline: none;

  border-color: #794646;

}



.bookings-grid {

  display: grid;

  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));

  gap: 2rem;

}



.booking-card {

  background: white;

  border-radius: 12px;

  padding: 1.5rem;

  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

}



.booking-header {

  display: flex;

  justify-content: space-between;

  align-items: center;

  margin-bottom: 1rem;

}



.booking-id {

  color: #666;

  font-size: 0.9rem;

}



.booking-status {

  padding: 0.25rem 0.75rem;

  border-radius: 999px;

  font-size: 0.875rem;

  font-weight: 600;

}



.booking-status.pending {

  background: #fef3c7;

  color: #92400e;

}



.booking-status.confirmed {

  background: #d1fae5;

  color: #065f46;

}



.booking-status.cancelled {

  background: #fee2e2;

  color: #991b1b;

}



.room-name {

  color: #794646;

  font-size: 1.25rem;

  font-weight: 600;

  margin-bottom: 0.25rem;

}



.house-name {

  color: #666;

  font-size: 0.9rem;

  margin-bottom: 1rem;

}



.tenant-info {

  display: flex;

  align-items: center;

  gap: 0.5rem;

  margin-bottom: 1rem;

}



.tenant-icon {

  width: 24px;

  height: 24px;

  border-radius: 50%;

}



.booking-dates {

  color: #666;

  font-size: 0.9rem;

  margin-bottom: 1rem;

}



.booking-actions {

  display: flex;

  gap: 0.5rem;

  flex-wrap: wrap;

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



.action-btn.confirm {

  background: #794646;

  color: white;

}



.action-btn.confirm:hover {

  background: #5d3535;

}



.action-btn.cancel {

  background: #fee2e2;

  color: #991b1b;

}



.action-btn.cancel:hover {

  background: #fecaca;

}



.action-btn.view {

  background: #f3f4f6;

  color: #374151;

}



.action-btn.view:hover {

  background: #e5e7eb;

}



@media (max-width: 640px) {

  .filters {

    flex-direction: column;

  }



  .bookings-wrapper {

    padding: 1rem;

  }



  .status-container {

    padding: 2rem 1rem;

  }



  .status-icon {

    width: 80px;

    height: 80px;

  }

}



.profile-section {

  margin-left: auto !important;

  margin-right: 1rem !important;

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



.menu-container {

  margin-left: 0 !important;

  margin-right: 2rem;

}



@media (max-width: 768px) {

  .landlord-profile-name {

    display: none;

  }

}



/* Modal Styles */

.details-modal {

  position: fixed;

  top: 0;

  left: 0;

  right: 0;

  bottom: 0;

  background: rgba(0, 0, 0, 0.7);

  display: flex;

  align-items: center;

  justify-content: center;

  z-index: 1000;

}



.details-modal-content {

  background: white;

  border-radius: 12px;

  width: 90%;

  max-width: 800px;

  max-height: 90vh;

  overflow-y: auto;

}



.modal-header {

  padding: 1.5rem;

  border-bottom: 1px solid #eee;

  display: flex;

  justify-content: space-between;

  align-items: center;

}



.modal-header h2 {

  color: #794646;

  margin: 0;

}



.close-modal {

  background: none;

  border: none;

  font-size: 24px;

  color: #666;

  cursor: pointer;

}



.modal-body {

  padding: 1.5rem;

}



.detail-section {

  margin-bottom: 2rem;

}



.detail-section h3 {

  color: #794646;

  font-size: 1.2rem;

  margin-bottom: 1rem;

}



.detail-row {

  display: flex;

  margin-bottom: 0.75rem;

  padding: 0.75rem;

  background: #f8f9fa;

  border-radius: 6px;

}



.detail-label {

  width: 120px;

  color: #666;

  font-weight: 600;

}



.detail-value {

  flex: 1;

  color: #333;

}



.detail-value.pending {

  color: #b45309;

  font-weight: 600;

}



.detail-value.confirmed {

  color: #059669;

  font-weight: 600;

}



.detail-value.cancelled {

  color: #dc2626;

  font-weight: 600;

}



.modal-footer {

  padding: 1.5rem;

  border-top: 1px solid #eee;

  display: flex;

  justify-content: flex-end;

  gap: 1rem;

}



.modal-actions {

  display: flex;

  gap: 1rem;

}



@media (max-width: 640px) {

  .details-modal-content {

    width: 95%;

    margin: 1rem;

  }



  .modal-actions {

    flex-direction: column;

  }

}



.booking-reference {

  color: #666;

  font-size: 0.9rem;

  font-weight: 600;

}



.booking-reference-info {

  display: flex;

  align-items: center;

  gap: 0.5rem;

  margin-bottom: 1rem;

  color: #666;

  font-size: 0.9rem;

}



.booking-reference-info .detail-label {

  font-weight: 600;

  min-width: auto;

}



.tenant-profile {

  display: flex;

  justify-content: center;

  margin-bottom: 1rem;

}



.tenant-profile-image {

  width: 100px;

  height: 100px;

  border-radius: 50%;

  object-fit: cover;

  border: 3px solid #794646;

}



.tenant-icon {

  width: 32px;

  height: 32px;

  border-radius: 50%;

  object-fit: cover;

  border: 2px solid #794646;

}



.detail-row .detail-value {

  word-break: break-word;

}



.detail-row .detail-label {

  min-width: 120px;

  flex-shrink: 0;

}



.tenant-profile {

  display: flex;

  justify-content: center;

  margin-bottom: 1.5rem;

  padding: 1rem 0;

}



.tenant-profile-image {

  width: 120px;

  height: 120px;

  border-radius: 50%;

  object-fit: cover;

  border: 4px solid #794646;

  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

}



.payment-info {

  margin: 1rem 0;

  padding: 0.75rem;

  background: #f8f9fa;

  border-radius: 6px;

}



.price-info {

  display: flex;

  justify-content: space-between;

  margin-bottom: 0.5rem;

}



.payment-progress {

  display: flex;

  align-items: center;

  gap: 0.5rem;

}



.payment-status-badge {

  display: inline-block;

  padding: 0.25rem 0.75rem;

  border-radius: 999px;

  font-size: 0.75rem;

  font-weight: 500;

}



.payment-status-badge.completed {

  background: #d4edda;

  color: #155724;

}



.payment-status-badge.partially_paid {

  background: #fff3cd;

  color: #856404;

}



.payment-status-badge.unpaid {

  background: #f8d7da;

  color: #721c24;

}



.amount-info {

  display: flex;

  align-items: center;

  gap: 0.25rem;

  font-size: 0.875rem;

}



.paid-amount {

  font-weight: 600;

  color: #374151;

}



.total-amount {

  color: #6b7280;

}



.tenant-profile-section {

  display: flex;

  align-items: center;

  gap: 1.5rem;

  padding: 1rem;

  background: #f8f9fa;

  border-radius: 8px;

  margin-bottom: 1rem;

}



.tenant-avatar-container {

  flex-shrink: 0;

}



.tenant-profile-image {

  width: 80px;

  height: 80px;

  border-radius: 50%;

  object-fit: cover;

  border: 3px solid #794646;

  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

}



.tenant-info-container {

  flex-grow: 1;

}



.tenant-name {

  color: #794646;

  font-size: 1.1rem;

  font-weight: 600;

  margin: 0 0 0.5rem 0;

}



.tenant-contact,

.tenant-email {

  color: #666;

  font-size: 0.9rem;

  margin: 0.25rem 0;

  display: flex;

  align-items: center;

  gap: 0.5rem;

}



.tenant-contact i,

.tenant-email i {

  color: #794646;

  font-size: 0.8rem;

}



@media (max-width: 768px) {

  .tenant-profile-section {

    flex-direction: column;

    text-align: center;

    gap: 1rem;

  }



  .tenant-info-container {

    display: flex;

    flex-direction: column;

    align-items: center;

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
