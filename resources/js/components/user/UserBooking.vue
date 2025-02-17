<template>
  <div>
    <Navbare />
    
    <div class="overall">
      <div class="home">
        <div class="header-container">
          <h4 class="helper">Boarding House Booking</h4>
        </div>
      </div>

      <!-- Content Container -->
      <div class="content-container">
        <!-- Booking Forms Container -->
        <div class="forms-container">
          <!-- Move all booking forms here -->
          <div class="booking-form" id="step1">
            <button @click="goBack" class="back-button">
              <i class="fas fa-arrow-left"></i> Back
            </button>
            <h4 class="booking-title">{{ room.room_name }}</h4>
            <div class="room-image">
              <img 
                :src="getRoomImage(room.room_images)" 
                alt="Room Image" 
                @click="openImageModal"
                style="cursor: pointer"
              >
            </div>

            <div class="form-content">
              <!-- User Information Display -->
              <div class="user-info-section">
                <h5>Your Information</h5>
                <div class="user-info-grid">
                  <div class="info-item">
                    <label>Name:</label>
                    <p>{{ userInfo.name || 'Not available' }}</p>
                  </div>
                  <div class="info-item">
                    <label>Email:</label>
                    <p>{{ userInfo.email || 'Not available' }}</p>
                  </div>
                  <div class="info-item">
                    <label>Phone:</label>
                    <p>{{ userInfo.phone_number || 'Not available' }}</p>
                  </div>
                </div>
              </div>

              <div class="date-inputs">
                <div class="form-group">
                  <label>Check-in Date <span class="required">*</span></label>
                  <div class="date-input-container">
                    <input 
                      type="date" 
                      v-model="booking.check_in_date"
                      :min="getCurrentDate()"
                      required
                      @change="handleCheckInChange"
                    >
                  </div>
                </div>

                <div class="form-group">
                  <label>Check-out Date <span class="required">*</span></label>
                  <div class="date-input-container">
                    <input 
                      type="date" 
                      v-model="booking.check_out_date"
                      :min="getMinCheckoutDate()"
                      required
                      @change="handleCheckOutChange"
                    >
                  </div>
                </div>
              </div>

              <div class="price-info">
                <h5>Room & Boarding House Details</h5>
                <div class="details-grid">
                  <div class="detail-section">
                    <h6>Room Information</h6>
                    <p>Price: ₱{{ room.price }} per month</p>
                    <p>Bed Quantity: {{ room.bed_quantity }}</p>
                    <p>Status: {{ room.status }}</p>
                    <p>Payment Status: <span class="payment-status" :class="paymentStatusClass">{{ paymentStatusText }}</span></p>
                  </div>
                  <div class="detail-section">
                    <h6>Boarding House Information</h6>
                    <p>Name: {{ boardingHouse.name }}</p>
                    <p>Address: {{ boardingHouse.address }}</p>
                    <p>Contact: {{ boardingHouse.landlord_phone }}</p>
                  </div>
                </div>
              </div>

              <button @click="submitBooking" class="primary-button">Confirm Booking</button>
            </div>
          </div>
        </div>
        
        <!-- Map Section -->
        <div class="map-section">
          <div class="map-container" v-if="room.map_link">
            <iframe 
              :src="room.map_link" 
              width="100%" 
              height="450" 
              style="border:0;" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
          
          <!-- Add a message when no map is available -->
          <div v-else class="no-map-message">
            <p>No map available for this location</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div class="image-modal" v-if="showImageModal" @click="closeImageModal">
      <div class="modal-content">
        <span class="close-button" @click="closeImageModal">&times;</span>
        <img :src="getRoomImage(room.room_images)" alt="Room Image Full Size">
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Navbare from './layouts/Navbare.vue';
import defaultRoomImage from '@/assets/images/room1.png';

export default {
  name: 'UserBooking',
  components: {
    Navbare
  },
  props: {
    id: {
      type: [String, Number],
      required: true
    },
    boardingHouseId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      roomId: this.id,
      defaultRoomImage,
      room: {
        room_name: '',
        price: 0,
        bed_quantity: 0,
        status: '',
        room_images: '',
        description: '',
        map_link: ''
      },
      booking: {
        check_in_date: '',
        check_out_date: '',
        status: 'pending',
        user_id: null,
        room_id: this.id
      },
      showImageModal: false,
      userInfo: {
        name: '',
        email: '',
        phone_number: ''
      },
      boardingHouse: {
        name: '',
        address: '',
        landlord_phone: '',
        description: ''
      }
    }
  },
  methods: {
    goBack() {
      this.$router.go(-1);
    },

    async validateBookingDates() {
      try {
        const response = await axios.post('/user/validate-booking-dates', {
          room_id: this.booking.room_id,
          check_in_date: this.booking.check_in_date,
          check_out_date: this.booking.check_out_date
        });
        return response.data.available;
      } catch (error) {
        console.error('Error validating dates:', error);
        return false;
      }
    },

    async submitBooking() {
      try {
        if (!this.validateStep1()) {
          return;
        }

        // Create booking
        const response = await axios.post('/user/bookings/create', {
          user_id: this.booking.user_id,
          room_id: this.roomId,
          check_in_date: this.booking.check_in_date,
          check_out_date: this.booking.check_out_date,
          status: 'pending',
          payment_status: 'unpaid'
        }, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (response.data.status === 'success') {
          const bookingId = parseInt(response.data.booking_id);
          if (!bookingId || isNaN(bookingId)) {
            throw new Error('Invalid booking ID received');
          }

          // Create notification for the user
          await this.createNotification({
            user_id: this.booking.user_id,
            type: 'booking',
            message: `Your booking request for ${this.room.room_name} has been submitted and is pending approval.`,
            link: `/user/view-booking/${bookingId}`
          });

          // Store booking ID
          this.booking.id = bookingId;

          // Redirect to view booking page
          this.$router.push({
            name: 'user.view-booking',
            params: { 
              id: bookingId.toString()
            }
          });
        } else {
          throw new Error(response.data.message || 'Failed to create booking');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit booking: ' + (error.response?.data?.message || error.message));
      }
    },

    validateStep1() {
      if (!this.booking.check_in_date) {
        alert('Please select a check-in date');
        return false;
      }
      if (!this.booking.check_out_date) {
        alert('Please select a check-out date');
        return false;
      }
      
      // Validate check-out date is after check-in date
      const checkIn = new Date(this.booking.check_in_date);
      const checkOut = new Date(this.booking.check_out_date);
      if (checkOut <= checkIn) {
        alert('Check-out date must be after check-in date');
        return false;
      }
      return true;
    },

    async fetchRoomDetails() {
      try {
        console.log('Fetching room with ID:', this.roomId);
        const response = await axios.get(`/user/rooms/${this.roomId}/details`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        console.log('Room details response:', response.data); // Debug log
        
        if (response.data.status === 'success' && response.data.data) {
          const roomData = response.data.data;
          console.log('Room data:', roomData);
          
          // Make sure boarding_house_id is set from the correct property
          const boardingHouseId = roomData.boarding_house_id || 
                                 (roomData.boarding_house && roomData.boarding_house.boarding_house_id);
          
          if (!boardingHouseId) {
            throw new Error('No boarding house ID found in room data');
          }
          
          this.room = {
            room_name: roomData.room_name || 'Unnamed Room',
            price: roomData.price || 0,
            bed_quantity: roomData.bed_quantity || 0,
            status: roomData.status || 'unknown',
            room_images: roomData.room_images || '',
            description: roomData.description || 'No description available',
            map_link: roomData.map_link || '',
            boarding_house_id: boardingHouseId // Set the boarding house ID
          };

          if (roomData.boarding_house) {
            this.boardingHouse = {
              id: boardingHouseId,
              name: roomData.boarding_house.name || 'Not Available',
              address: roomData.boarding_house.address || 'Not Available',
              landlord_phone: roomData.boarding_house.landlord_phone || 'Not Available',
              description: roomData.boarding_house.description || 'No description available'
            };
          }
        } else {
          throw new Error(response.data.message || 'Failed to fetch room details');
        }
      } catch (error) {
        console.error('Error fetching room details:', error);
        alert('Failed to load room details. Please try again later.');
      }
    },

    getRoomImage(image) {
      if (image) {
        // If it's already a full URL (including our asset URL), return as is
        if (image.startsWith('http')) {
          return image;
        }
        // If it's a storage path, prepend storage
        if (image.startsWith('/storage/')) {
          return image;
        }
        // If it's just a path, prepend storage
        return `/storage/${image}`;
      }
      return this.defaultRoomImage;
    },

    goToBookingList() {
      this.$router.push('/user/bookings');
    },

    openImageModal() {
      this.showImageModal = true;
      document.body.style.overflow = 'hidden';
    },

    closeImageModal() {
      this.showImageModal = false;
      document.body.style.overflow = 'auto';
    },

    async fetchUserInfo() {
      try {
        const response = await axios.get('/user/profile', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (response.data.status === 'success' && response.data.data) {
          this.userInfo = {
            name: response.data.data.name || 'Not available',
            email: response.data.data.email || 'Not available',
            phone_number: response.data.data.phone_number || 'Not available'
          };
          this.booking.user_id = response.data.data.user_id;
        } else {
          console.warn('Invalid user data format:', response.data);
          // Set default values if data is not in expected format
          this.userInfo = {
            name: 'Not available',
            email: 'Not available',
            phone_number: 'Not available'
          };
        }
      } catch (error) {
        console.error('Error fetching user info:', error);
        if (error.response?.status === 401) {
          // Handle unauthorized - maybe redirect to login
          this.$router.push('/login');
        } else {
          // Set default values on error
          this.userInfo = {
            name: 'Not available',
            email: 'Not available',
            phone_number: 'Not available'
          };
        }
      }
    },

    getCurrentDate() {
      return new Date().toISOString().split('T')[0];
    },

    getMinCheckoutDate() {
      if (!this.booking.check_in_date) {
        return this.getCurrentDate();
      }
      const nextDay = new Date(this.booking.check_in_date);
      nextDay.setDate(nextDay.getDate() + 1);
      return nextDay.toISOString().split('T')[0];
    },

    handleCheckInChange() {
      if (this.booking.check_out_date) {
        const checkIn = new Date(this.booking.check_in_date);
        const checkOut = new Date(this.booking.check_out_date);
        if (checkOut <= checkIn) {
          this.booking.check_out_date = '';
          alert('Check-out date must be after check-in date');
        }
      }
    },

    handleCheckOutChange() {
      if (this.booking.check_in_date) {
        const checkIn = new Date(this.booking.check_in_date);
        const checkOut = new Date(this.booking.check_out_date);
        if (checkOut <= checkIn) {
          this.booking.check_out_date = '';
          alert('Check-out date must be after check-in date');
        }
      }
    },

    formatPrice(price) {
      return Number(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },

    // Add this method to create notifications
    async createNotification(notificationData) {
      try {
        const response = await axios.post('/notifications/create', notificationData);
        if (response.data.status !== 'success') {
          throw new Error(response.data.message || 'Failed to create notification');
        }
      } catch (error) {
        console.error('Error creating notification:', error);
      }
    },

    async fetchBookingDetails() {
      try {
        const response = await axios.get(`/user/bookings/${this.booking.id}`);
        if (response.data.status === 'success') {
          this.booking = {
            ...this.booking,
            ...response.data.data,
            payments: response.data.data.payments || []
          };
        }
      } catch (error) {
        console.error('Error fetching booking details:', error);
      }
    }
  },
  computed: {
    getQRCodeImage() {
      return this.qrCodes[this.booking.payment_method];
    },
    bookingStatusText() {
      if (this.booking.status === 'confirmed') {
        if (this.booking.payment_status === 'paid') {
          return 'Confirmed (Paid)';
        } else if (this.booking.payment_status === 'partially_paid') {
          return 'Confirmed (Partially Paid)';
        }
      }
      return this.booking.status.charAt(0).toUpperCase() + this.booking.status.slice(1);
    },
    paymentStatusClass() {
      if (!this.booking.payments || this.booking.payments.length === 0) return 'unpaid';
      
      const totalAmount = this.room.price;
      const paidAmount = this.booking.payments
        .filter(payment => payment.status === 'completed')
        .reduce((sum, payment) => sum + parseFloat(payment.amount), 0);
      
      if (paidAmount >= totalAmount) return 'paid';
      if (paidAmount > 0) return 'partially_paid';
      return 'unpaid';
    },
    
    paymentStatusText() {
      if (!this.booking.payments || this.booking.payments.length === 0) return 'Unpaid';
      
      const totalAmount = this.room.price;
      const paidAmount = this.booking.payments
        .filter(payment => payment.status === 'completed')
        .reduce((sum, payment) => sum + parseFloat(payment.amount), 0);
      
      if (paidAmount >= totalAmount) return 'Paid';
      if (paidAmount > 0) return 'Partially Paid';
      return 'Unpaid';
    }
  },
  async created() {
    try {
      await this.fetchRoomDetails(); // Get room details first
      await this.fetchUserInfo();    // Then try to get user info if available
    } catch (error) {
      console.error('Error in created hook:', error);
      // Don't redirect or show error, just log it
    }
  }
}
</script>

<style scoped>
.overall {
  padding: 2rem;
  max-width: 1200px;
  margin-top: 40px;
  margin-left: 40px;
}

.home {
  margin-bottom: 2rem;
}

.helper {
  color: #794646;
  font-size: 24px;
  font-weight: 700;
}

.booking-container {
  display: block;
  margin-bottom: 2rem;
}

.booking-form {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  margin-bottom: 2rem;
}

.booking-form + .booking-form {
  margin-top: 2rem;
}

.booking-title {
  color: #794646;
  font-size: 20px;
  margin-bottom: 1.5rem;
}

.room-image {
  width: 100%;
  height: 300px;
  overflow: hidden;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.room-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.form-content {
  padding: 1rem 0;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #794646;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

input, select {
  width: 97%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
}

.date-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 5rem;
  width: 30%;
}

.date-input-container {
  position: relative;
}

.date-input-container img {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
}

.price-info {
  text-align: left;
  background: #CFFFD7;
  padding: 1.5rem;
  border-radius: 8px;
  margin: 1.5rem 0;
}

.price-info h5 {
  color: #794646;
  margin-bottom: 1rem;
  text-align: center;
  font-size: 1.2rem;
}

.primary-button {
  background: black;
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 6px;
  width: 100%;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s;
}

.primary-button:hover {
  background: #3b3b3b;
}

.receipt {
  background: #fff;
}

.receipt-content {
  padding: 1.5rem;
}

.receipt-header {
  text-align: center;
  margin-bottom: 2rem;
}

.booking-id {
  color: #666;
  font-size: 14px;
}

.receipt-details {
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
  padding: 1.5rem 0;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.detail-item span {
  color: #666;
}

.booking-dates {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 1.5rem;
}

.date-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-item img {
  width: 24px;
  height: 24px;
}

.secondary-button {
  background: #f8f9fa;
  color: #794646;
  border: 1px solid #794646;
  padding: 1rem 2rem;
  border-radius: 6px;
  width: 100%;
  font-size: 16px;
  cursor: pointer;
  margin-top: 1.5rem;
  transition: all 0.3s;
}

.secondary-button:hover {
  background: #794646;
  color: white;
}

.map-container {
  margin-top: 2rem;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: none;
  border: none;
  color: #794646;
  font-size: 16px;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.back-button:hover {
  background-color: rgba(121, 70, 70, 0.1);
}

.required {
  color: red;
  margin-left: 4px;
}

input:required {
  border-left: 3px solid #794646;
}

.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
}

.modal-content img {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.close-button {
  position: absolute;
  top: -40px;
  right: 0;
  color: white;
  font-size: 30px;
  cursor: pointer;
  padding: 5px;
}

.close-button:hover {
  color: #ddd;
}

.no-map-message {
  text-align: center;
  padding: 2rem;
  background: #f8f9fa;
  border-radius: 12px;
  margin-top: 2rem;
  color: #666;
}

.content-container {
  display: flex;
  gap: 2rem;
  align-items: flex-start;
  position: relative;
}

.forms-container {
  flex: 1;
  max-width: 60%;
  margin-right: 40%;
}

.map-section {
  position: fixed;
  right: 20px;
  top: 2.5rem;
  width: 41%;
}

.map-container {
  margin-top: 40px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  height: calc(100vh - 100px);
  position: relative;
}

.map-container iframe {
  height: 100%;
}

.booking-form {
  max-width: 100%;
  /* ... rest of booking-form styles ... */
}

/* Add media query for responsiveness */
@media (max-width: 1200px) {
  .forms-container {
    max-width: 100%;
    margin-right: 0;
  }
  
  .map-section {
    display: none;
  }
}

.user-info-section {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.user-info-section h5 {
  color: #794646;
  margin-bottom: 1rem;
}

.user-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.info-item {
  font-size: 1.1rem;
  padding: 0.5rem;
}

.info-item label {
  color: #666;
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.info-item p {
  color: #333;
  font-weight: 500;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  text-align: left;
  margin-top: 1rem;
}

.detail-section {
  padding: 1rem;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 6px;
}

.detail-section h6 {
  color: #794646;
  font-size: 1rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.detail-section p {
  margin: 0.5rem 0;
  color: #333;
}

.qr-code-section {
  background: #fff;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  margin: 1.5rem 0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qr-code-section h6 {
  color: #794646;
  font-size: 1.1rem;
  margin-bottom: 1rem;
}

.qr-container {
  width: 200px;
  height: 200px;
  margin: 0 auto;
  padding: 1rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  cursor: pointer;
  position: relative;
  transition: transform 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-container:hover {
  transform: scale(1.02);
}

.qr-container:hover .click-hint {
  opacity: 1;
}

.click-hint {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.5rem;
  font-size: 0.8rem;
  opacity: 0;
  transition: opacity 0.2s ease;
  border-bottom-left-radius: 8px;
  border-bottom-right-radius: 8px;
}

.payment-instructions {
  margin: 1rem 0;
  color: #666;
  font-size: 0.9rem;
}

.payment-details {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 6px;
  margin-top: 1rem;
}

.payment-details p {
  margin: 0.5rem 0;
  font-weight: 500;
}

.cash-payment-info {
  margin: 1.5rem 0;
  padding: 1rem;
  background: #e8f4ff;
  border-radius: 8px;
  border-left: 4px solid #3b82f6;
}

.cash-payment-info h6 {
  color: #1e40af;
  margin-bottom: 0.5rem;
}

.cash-payment-info p {
  color: #1e3a8a;
  margin: 0.25rem 0;
}

.payment-proof-upload {
  margin-top: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.payment-proof-input {
  margin: 1rem 0;
  width: 100%;
  padding: 0.5rem;
}

.upload-note {
  color: #666;
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.payment-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  background-color: white;
  cursor: pointer;
}

.payment-select:focus {
  outline: none;
  border-color: #794646;
}

.payment-select option {
  padding: 0.5rem;
}

.required {
  color: red;
  margin-left: 4px;
}

.payment-proof-preview {
  margin-top: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.proof-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
  border-radius: 4px;
}

.qr-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.qr-modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  position: relative;
  max-width: 90%;
  max-height: 90vh;
}

.qr-modal-image {
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
}

.close-qr-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 2rem;
  color: #666;
  cursor: pointer;
  padding: 0.5rem;
  line-height: 1;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s ease;
}

.close-qr-modal:hover {
  background-color: rgba(0, 0, 0, 0.1);
}

.qr-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.status-badge.paid {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.status-badge.partially_paid {
  background: #fef9c3;
  color: #854d0e;
  border: 1px solid #fde047;
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
</style>


