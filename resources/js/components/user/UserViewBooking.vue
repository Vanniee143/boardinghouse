<template>
    <div id="app">
        <Navbaro />

        <div class="overall">
            <div class="home">
                <h4 class="helper">View Booking</h4>
            </div>

            <div v-if="loading" class="loading-state">Loading bookings...</div>

            <div v-else-if="error" class="error-state">
                {{ error }}
            </div>

            <div v-else-if="bookings.length === 0" class="empty-state">
                No bookings found
            </div>

            <div v-else v-for="(booking, index) in bookings" :key="booking.booking_id" class="contains">
                <div class="booking-header">
                    <h3 class="booking-title">Booking #{{ booking.booking_id }}</h3>
                    <span class="booking-status" :class="booking.status">{{ booking.status }}</span>
                </div>

                <div class="booking-grid">
                    <!-- Left side - Image with header and footer -->
                    <div class="image-section">
                        <div class="image-container" @click="showImageModal(booking.room?.room_images)">
                            <img
                                :src="getRoomImage(booking.room?.room_images)"
                                :alt="booking.room?.room_name"
                                class="img"
                                @error="handleImageError"
                            />
                            <div class="image-overlay">
                                <span>Click to view larger</span>
                            </div>
                        </div>

                        <div class="location-info">
                            <p class="text">Location:</p>
                            <div class="location-details">
                                <i class="fas fa-home"></i>
                                <p class="texts">{{ booking.room?.boarding_house?.name }}</p>
                                <i class="fas fa-map-marker-alt"></i>
                                <p class="texts">{{ booking.room?.boarding_house?.address }}</p>
                                <button class="view-map-btn" @click="showMapModal(booking.room?.map_link)">
                                    <i class="fas fa-map-marked-alt fa-lg"></i> View Map
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right side - Details -->
                    <div class="details-section">
                        <div class="inside">
                            <div class="itemss">
                                <p class="title">Booking Details</p>
                                <p class="memo">
                                    Room: {{ booking.room?.room_name }}<br>
                                    Price: ₱{{ booking.room?.price }} Per Month<br>
                                    Status: {{ booking.status }}
                                </p>
                                <div class="date-info">
                                    <p><span>Check-in:</span> {{ formatDate(booking.check_in_date) }}</p>
                                    <p><span>Check-out:</span> {{ formatDate(booking.check_out_date) }}</p>
                                </div>
                                <div class="button-group">
                                    <button 
                                        class="pay-now" 
                                        @click="showDetails(index)"
                                        :disabled="booking.status === 'cancelled' || hasFullPayment(booking)"
                                    >
                                        {{ hasFullPayment(booking) ? 'Fully Paid' : 'Pay Now' }}
                                    </button>
                                    <button 
                                        class="cancel-inline" 
                                        @click="cancelBooking(booking.booking_id)"
                                        :disabled="booking.status === 'cancelled'"
                                    >
                                        {{ booking.status === 'cancelled' ? 'Cancelled' : 'Cancel Booking' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete button outside grid for cancelled bookings -->
                <div v-if="booking.status === 'cancelled'" class="delete-container">
                    <button 
                        class="delete-button"
                        @click="deleteBooking(booking.booking_id)"
                    >
                        Delete Booking
                    </button>
                </div>

                <!-- Payment Modal -->
                <div class="payment-modal" :id="'details' + index" v-show="activeDetail === index">
                    <div class="modal-content">
                        <img src="@/assets/images/X.png" alt="Close" @click="hideDetails" class="x-icon"/>
                        
                        <div class="payment-details">
                            <h3 class="payment-title">Payment Details</h3>
                            
                            <!-- Add Booking ID Display -->
                            <div class="booking-info">
                                <h4>Booking Information</h4>
                                <p>Booking #{{ bookings[activeDetail]?.booking_id }}</p>
                                <p>Room: {{ bookings[activeDetail]?.room?.room_name }}</p>
                            </div>
                            
                            <!-- Amount Selection -->
                            <div class="amount-section">
                                <h4>Select Payment Amount</h4>
                                <div class="amount-options">
                                    <!-- Full Payment Option -->
                                    <label class="amount-option">
                                        <input 
                                            type="radio" 
                                            v-model="paymentAmount" 
                                            :value="booking.room.price" 
                                            name="paymentAmount"
                                            :disabled="hasPartialPayment(booking)"
                                        >
                                        <span>Full Payment (₱{{ booking.room.price }})</span>
                                    </label>

                                    <!-- Half Payment Option -->
                                    <label class="amount-option" v-if="!hasPartialPayment(booking)">
                                        <input 
                                            type="radio" 
                                            v-model="paymentAmount" 
                                            :value="booking.room.price / 2" 
                                            name="paymentAmount"
                                        >
                                        <span>50% Down Payment (₱{{ booking.room.price / 2 }})</span>
                                    </label>

                                    <!-- Remaining Balance Option -->
                                    <label class="amount-option" v-if="hasPartialPayment(booking)">
                                        <input 
                                            type="radio" 
                                            v-model="paymentAmount" 
                                            :value="getRemainingAmount(booking)" 
                                            name="paymentAmount"
                                        >
                                        <span>Remaining Balance (₱{{ getRemainingAmount(booking) }})</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="payment-method-section">
                                <h4>Select Payment Method</h4>
                                <div class="payment-methods">
                                    <label class="payment-method">
                                        <input 
                                            type="radio" 
                                            v-model="paymentMethod" 
                                            value="cash" 
                                            name="paymentMethod"
                                            @change="handlePaymentMethodChange"
                                        >
                                        <span>Cash Payment</span>
                                    </label>
                                    <label class="payment-method">
                                        <input 
                                            type="radio" 
                                            v-model="paymentMethod" 
                                            value="gcash_qr" 
                                            name="paymentMethod"
                                            @change="handlePaymentMethodChange"
                                        >
                                        <span>GCash</span>
                                    </label>
                                    <label class="payment-method">
                                        <input 
                                            type="radio" 
                                            v-model="paymentMethod" 
                                            value="paymaya_qr" 
                                            name="paymentMethod"
                                            @change="handlePaymentMethodChange"
                                        >
                                        <span>PayMaya</span>
                                    </label>
                                </div>
                            </div>

                            <!-- QR Code Display (for GCash/PayMaya) -->
                            <div v-if="paymentMethod !== 'cash'" class="qr-container">
                                <div v-if="qrCodeLoading" class="loading-indicator">
                                    Loading QR code...
                                </div>
                                <div v-else-if="!qrCodeUrls[`${getBoardingHouseId(bookings[activeDetail])}-${paymentMethod}`]" class="null-qr">
                                    <img 
                                        :src="nullQRImage" 
                                        alt="No QR Code Available"
                                        class="qr-image"
                                    >
                                    <p class="null-qr-text">No QR code available for this payment method</p>
                                </div>
                                <img 
                                    v-else
                                    :src="qrCodeUrls[`${getBoardingHouseId(bookings[activeDetail])}-${paymentMethod}`]"
                                    :alt="paymentMethod + ' QR Code'"
                                    class="qr-image"
                                    @error="handleQRImageError"
                                    @load="handleQRImageLoad"
                                    @click="openQRModal(qrCodeUrls[`${getBoardingHouseId(bookings[activeDetail])}-${paymentMethod}`])"
                                    style="cursor: pointer;"
                                >
                            </div>

                            <!-- Payment Proof Upload -->
                            <div v-if="paymentMethod !== 'cash'" class="proof-section">
                                <h4>Upload Payment Proof</h4>
                                <div class="upload-container">
                                    <input 
                                        type="file" 
                                        @change="handleProofUpload" 
                                        accept="image/*"
                                        class="proof-input"
                                    >
                                    <img 
                                        v-if="paymentProof" 
                                        :src="paymentProof" 
                                        class="proof-preview"
                                    >
                                </div>
                            </div>

                            <!-- Reference Number -->
                            <div v-if="paymentMethod !== 'cash'" class="reference-section">
                                <h4>Reference Number</h4>
                                <input 
                                    type="text" 
                                    v-model="referenceNumber" 
                                    placeholder="Enter reference number"
                                    class="reference-input"
                                >
                            </div>

                            <!-- Submit Payment Button -->
                            <button 
                                @click="submitPayment(bookings[activeDetail]?.booking_id)" 
                                class="submit-payment"
                                :disabled="!isPaymentValid || isSubmitting"
                            >
                                {{ isSubmitting ? 'Submitting...' : 'Submit Payment' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add this inside your booking details section -->
                <div class="payment-status-section">
                    <div class="payment-status" :class="getPaymentStatusClass(booking)">
                        {{ getPaymentStatusText(booking) }}
                        <span class="payment-amount">₱{{ formatAmount(getTotalPaid(booking)) }} / ₱{{ formatAmount(booking.room?.price || 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Image Modal -->
        <div v-if="selectedImage" class="image-modal" @click="closeImageModal">
            <div class="modal-content" @click.stop>
                <button class="close-modal" @click="closeImageModal">&times;</button>
                <img :src="getRoomImage(selectedImage)" alt="Room Image" class="modal-image">
            </div>
        </div>

        <!-- QR Code Modal -->
        <div v-if="selectedQRCode" class="qr-modal" @click="closeQRModal">
            <div class="modal-content" @click.stop>
                <button class="close-modal" @click="closeQRModal">&times;</button>
                <img :src="selectedQRCode" alt="QR Code" class="qr-modal-image">
            </div>
        </div>

        <!-- Map Modal -->
        <div v-if="showingMap" class="map-modal" @click="closeMapModal">
            <div class="map-modal-content" @click.stop>
                <button class="close-modal" @click="closeMapModal">&times;</button>
                <div class="map-container">
                    <iframe
                        :src="currentMapUrl"
                        width="100%"
                        height="450"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Navbaro from "@/components/user/layouts/Navbaro.vue";
import axios from "axios";

export default {
    name: "UserViewBooking",
    components: {
        Navbaro,
    },
    data() {
        return {
            isLoggedIn: false,

            userName: "",

            bookings: [],

            activeDetail: null,

            loading: true,

            error: null,

            defaultImage: '/default-image.jpg',

            selectedImage: null,

            paymentAmount: null,
            paymentMethod: null,
            paymentProof: null,
            referenceNumber: '',
            uploadedFile: null,
            qrCodeUrls: {},
            qrCodeLoading: false,
            defaultQRImage: '/images/select-qr.png',
            nullQRImage: '/images/null-qr.png',
            selectedQRCode: null,
            isSubmitting: false,
            showingMap: false,
            currentMapUrl: '',
        };
    },
    computed: {
        isPaymentValid() {
            if (!this.paymentAmount || !this.paymentMethod) return false;
            
            if (this.paymentMethod === 'cash') return true;
            
            return this.paymentMethod !== 'cash' && 
                   this.uploadedFile && 
                   this.referenceNumber.trim() !== '';
        },
        qrCodeKey() {
            const boardingHouseId = this.getBoardingHouseId(this.bookings[this.activeDetail]);
            return `${boardingHouseId}-${this.paymentMethod}`;
        }
    },
    watch: {
        async paymentMethod(newMethod) {
            if (newMethod && newMethod !== 'cash' && this.activeDetail !== null) {
                const booking = this.bookings[this.activeDetail];
                console.log('Payment method changed:', {
                    method: newMethod,
                    booking: booking
                });

                const boardingHouseId = this.getBoardingHouseId(booking);
                if (boardingHouseId) {
                    this.qrCodeLoading = true;
                    try {
                        const paymentType = newMethod.replace('_qr', '');
                        const url = `/user/boarding-houses/${boardingHouseId}/payment-qr-codes/${paymentType}`;
                        
                        console.log('Fetching QR code:', {
                            url,
                            boardingHouseId,
                            paymentType
                        });

                        const response = await axios.get(url);
                        console.log('QR code response:', response.data);

                        if (response.data.status === 'success' && response.data.data?.qr_image) {
                            this.qrCodeUrls[`${boardingHouseId}-${newMethod}`] = response.data.data.qr_image;
                        } else {
                            console.warn('Invalid QR code response:', response.data);
                            this.qrCodeUrls[`${boardingHouseId}-${newMethod}`] = null;
                        }
                    } catch (error) {
                        console.error('Error fetching QR code:', error);
                        console.error('Error details:', error.response?.data);
                        this.qrCodeUrls[`${boardingHouseId}-${newMethod}`] = null;
                    } finally {
                        this.qrCodeLoading = false;
                    }
                }
            }
        }
    },
    methods: {
        getBoardingHouseId(booking) {
            if (!booking) {
                console.warn('No booking provided to getBoardingHouseId');
                return null;
            }

            // Debug log the entire booking object
            console.log('Full booking object:', booking);
            
            const boardingHouseId = booking?.room?.boarding_house?.boarding_house_id || 
                                   booking?.room?.boarding_house?.id ||
                                   booking?.room?.boarding_house_id ||
                                   booking?.boarding_house_id;
                                   
            console.log('Selected boarding house ID:', boardingHouseId);
            
            return boardingHouseId;
        },

        async showDetails(index) {
            this.activeDetail = index;
            this.paymentMethod = null; // Reset payment method when opening modal
            this.paymentAmount = null; // Reset payment amount when opening modal
            const booking = this.bookings[index];
            
            // Debug logs
            console.log('Opening payment modal for booking:', booking);
            console.log('Booking ID:', booking.booking_id);
            
            // Add delay to ensure reactive data is updated
            this.$nextTick(() => {
                if (this.paymentMethod && this.paymentMethod !== 'cash') {
                    const boardingHouseId = this.getBoardingHouseId(booking);
                    console.log('Fetching QR code with boarding house ID:', boardingHouseId);
                    if (boardingHouseId) {
                        this.fetchQRCode(boardingHouseId, this.paymentMethod);
                    } else {
                        console.error('No boarding house ID found for booking:', booking.booking_id);
                    }
                }
            });
        },

        hideDetails() {
            this.activeDetail = null;
            this.paymentMethod = null; // Reset payment method when closing modal
            this.paymentAmount = null; // Reset payment amount when closing modal
            this.paymentProof = null; // Reset payment proof when closing modal
            this.referenceNumber = ''; // Reset reference number when closing modal
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString("en-US", {
                year: "numeric",

                month: "2-digit",

                day: "2-digit",
            });
        },

        navigate(event) {
            const route = event.target.value;

            if (route === "/login") {
                this.handleLogout();
            } else {
                this.$router.push(route);
            }
        },

        handleLogout() {
            localStorage.removeItem("userName");

            localStorage.removeItem("userId");

            localStorage.removeItem("token");

            this.$router.push("/login");
        },

        async fetchBookings() {
            this.loading = true;
            this.error = null;
            try {
                const userId = localStorage.getItem("userId");
                if (!userId) {
                    this.$router.push("/login");
                    return;
                }

                const response = await axios.get("/user/bookings", {
                    headers: {
                        "X-User-Id": userId,
                    },
                });

                if (response.data.status === "success") {
                    console.log('Raw bookings response:', response.data);
                    
                    this.bookings = response.data.data;
                    
                    // Debug log each booking's structure
                    this.bookings.forEach((booking, index) => {
                        console.log(`Booking ${index} structure:`, {
                            booking_id: booking.booking_id,
                            room: booking.room,
                            boarding_house: booking?.room?.boarding_house,
                            boarding_house_id: this.getBoardingHouseId(booking)
                        });
                    });
                } else {
                    throw new Error(response.data.message || "Failed to fetch bookings");
                }
            } catch (error) {
                console.error("Error fetching bookings:", error);
                this.error = error.response?.data?.message || "Failed to fetch bookings";
            } finally {
                this.loading = false;
            }
        },

        async cancelBooking(bookingId) {
            if (!confirm('Are you sure you want to cancel this booking?')) {
                return;
            }

            try {
                const response = await axios.post(`/user/bookings/${bookingId}/cancel`);
                
                if (response.data.status === 'success') {
                    // Update the local booking status
                    const bookingIndex = this.bookings.findIndex(b => b.booking_id === bookingId);
                    if (bookingIndex !== -1) {
                        this.bookings[bookingIndex].status = 'cancelled';
                    }

                    await this.createNotification({
                        user_id: localStorage.getItem('userId'),
                        type: 'booking',
                        message: `Your booking #${bookingId} has been cancelled.`,
                        link: `/user/view-booking/${bookingId}`
                    });

                    alert('Booking cancelled successfully');
                    await this.fetchBookings();
                }
            } catch (error) {
                console.error('Error cancelling booking:', error);
                alert(error.response?.data?.message || 'Failed to cancel booking');
            }
        },

        // Add method to create notifications
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

        getRoomImage(imagePath) {
            if (!imagePath) {
                return this.defaultImage;
            }
            
            // If it's already a full URL
            if (imagePath.startsWith('http')) {
                return imagePath;
            }
            
            // If it's a storage path
            if (imagePath.startsWith('/storage/')) {
                return imagePath;
            }
            
            // Otherwise, assume it's a relative path and prepend storage
            return `/storage/${imagePath}`;
        },

        handleImageError(e) {
            e.target.src = this.defaultImage;
        },

        showImageModal(image) {
            this.selectedImage = image;
            document.body.style.overflow = 'hidden';
        },

        closeImageModal() {
            this.selectedImage = null;
            document.body.style.overflow = 'auto';
        },

        async deleteBooking(bookingId) {
            if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await axios.delete(`/user/bookings/${bookingId}`);
                
                if (response.data.status === 'success') {
                    // Remove the booking from local state
                    this.bookings = this.bookings.filter(b => b.booking_id !== bookingId);
                    alert('Booking deleted successfully');
                }
            } catch (error) {
                console.error('Error deleting booking:', error);
                alert(error.response?.data?.message || 'Failed to delete booking');
            }
        },

        async fetchQRCode(boardingHouseId, paymentType) {
            if (!boardingHouseId || !paymentType) {
                console.warn('Missing required parameters for QR code fetch:', { 
                    boardingHouseId, 
                    paymentType
                });
                return;
            }

            this.qrCodeLoading = true;
            try {
                console.log('Fetching QR code for:', {
                    boardingHouseId,
                    paymentType,
                    url: `/user/boarding-houses/${boardingHouseId}/payment-qr-codes/${paymentType}`
                });

                const response = await axios.get(
                    `/user/boarding-houses/${boardingHouseId}/payment-qr-codes/${paymentType}`
                );
                
                console.log('QR code response:', response.data);

                if (response.data.status === 'success' && response.data.data?.qr_image) {
                    const qrUrl = response.data.data.qr_image.startsWith('/storage/') 
                        ? response.data.data.qr_image 
                        : `/storage/${response.data.data.qr_image}`;
                        
                    console.log('Setting QR code URL:', qrUrl);
                    this.qrCodeUrls[`${boardingHouseId}-${paymentType}`] = qrUrl;
                } else {
                    console.warn('No QR code found in response:', response.data);
                    this.qrCodeUrls[`${boardingHouseId}-${paymentType}`] = null;
                }
            } catch (error) {
                console.error('Error fetching QR code:', error);
                this.qrCodeUrls[`${boardingHouseId}-${paymentType}`] = null;
            } finally {
                this.qrCodeLoading = false;
            }
        },

        async handleProofUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.uploadedFile = file;

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.paymentProof = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        async submitPayment(bookingId) {
            try {
                // Get the active booking from the bookings array
                const activeBooking = this.bookings[this.activeDetail];
                if (!activeBooking) {
                    throw new Error('No active booking found');
                }

                // Enhanced validation
                if (!bookingId) {
                    throw new Error('Invalid booking ID');
                }

                if (!this.paymentMethod) {
                    alert('Please select a payment method');
                    return;
                }

                if (!this.paymentAmount) {
                    alert('Please select a payment amount');
                    return;
                }

                // Create FormData object
                const formData = new FormData();
                formData.append('booking_id', bookingId);
                formData.append('amount', this.paymentAmount);
                
                // Map the payment method correctly - keep the _qr suffix
                formData.append('payment_method', this.paymentMethod);

                // Add reference number and proof for non-cash payments
                if (this.paymentMethod !== 'cash') {
                    if (!this.referenceNumber?.trim()) {
                        alert('Please enter a reference number');
                        return;
                    }
                    if (!this.uploadedFile) {
                        alert('Please upload payment proof');
                        return;
                    }
                    formData.append('reference_number', this.referenceNumber.trim());
                    formData.append('payment_proof', this.uploadedFile);
                }

                // Debug log the FormData contents
                console.log('Submitting payment with data:', {
                    booking_id: bookingId,
                    amount: this.paymentAmount,
                    payment_method: this.paymentMethod,
                    reference_number: this.paymentMethod !== 'cash' ? this.referenceNumber.trim() : null,
                    has_payment_proof: !!this.uploadedFile
                });

                // Add loading state
                this.isSubmitting = true;

                const response = await axios.post('/user/payments/create', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.status === 'success') {
                    // Create notification for successful payment
                    await this.createNotification({
                        user_id: localStorage.getItem('userId'),
                        type: 'payment',
                        message: `Payment of ₱${this.paymentAmount} for booking #${bookingId} has been submitted successfully.`,
                        link: `/user/view-booking/${bookingId}`
                    });

                    alert('Payment submitted successfully');
                    this.resetPaymentForm();
                    this.hideDetails();
                    await this.fetchBookings();
                } else {
                    throw new Error(response.data.message || 'Failed to submit payment');
                }
            } catch (error) {
                console.error('Error submitting payment:', error);
                console.error('Error response:', error.response?.data);
                console.error('Error status:', error.response?.status);
                
                // Enhanced error handling
                let errorMessage = 'Failed to submit payment. ';
                if (error.response?.data?.errors) {
                    // Handle validation errors
                    const errorMessages = Object.values(error.response.data.errors).flat();
                    errorMessage += errorMessages.join('\n');
                } else if (error.response?.data?.message) {
                    // Handle specific error message from server
                    errorMessage += error.response.data.message;
                } else if (error.message) {
                    // Handle client-side error message
                    errorMessage += error.message;
                }
                
                alert(errorMessage);
            } finally {
                this.isSubmitting = false;
            }
        },

        // Add helper method to reset form
        resetPaymentForm() {
            this.paymentAmount = null;
            this.paymentMethod = null;
            this.paymentProof = null;
            this.referenceNumber = '';
            this.uploadedFile = null;
        },

        handleQRImageError(e) {
            console.error('QR Image failed to load:', e.target.src);
            // Show null QR state when image fails to load
            const booking = this.bookings[this.activeDetail];
            if (booking) {
                this.qrCodeUrls[`${booking.room.boardingHouse.id}-${this.paymentMethod}`] = null;
            }
        },

        handleQRImageLoad(e) {
            console.log('QR Image loaded successfully:', e.target.src);
        },

        openQRModal(qrUrl) {
            this.selectedQRCode = qrUrl;
            document.body.style.overflow = 'hidden';
        },

        closeQRModal() {
            this.selectedQRCode = null;
            document.body.style.overflow = 'auto';
        },

        formatAmount(amount) {
            return parseFloat(amount).toFixed(2);
        },

        getTotalPaid(booking) {
            if (!booking?.payments) {
                console.log('No payments array for booking:', booking);
                return 0;
            }
            
            const totalPaid = booking.payments
                .filter(payment => payment.status === 'completed')
                .reduce((sum, payment) => {
                    const amount = parseFloat(payment.amount) || 0;
                    return sum + amount;
                }, 0);

            console.log('Total paid calculation:', {
                booking_id: booking.booking_id,
                payments: booking.payments,
                totalPaid: totalPaid
            });
            
            return totalPaid;
        },

        getPaymentStatusClass(booking) {
            if (!booking?.payments) {
                console.log('No payments array for booking:', booking);
                return 'unpaid';
            }
            
            const totalPrice = parseFloat(booking.room?.price) || 0;
            const totalPaid = this.getTotalPaid(booking);
            
            console.log('Payment status calculation:', {
                booking_id: booking.booking_id,
                totalPrice,
                totalPaid,
                payments: booking.payments
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

        hasPartialPayment(booking) {
            return this.getPaymentStatusClass(booking) === 'partially_paid';
        },

        hasFullPayment(booking) {
            return this.getPaymentStatusClass(booking) === 'completed';
        },

        getRemainingAmount(booking) {
            const totalPrice = booking.room.price;
            const totalPaid = this.getTotalPaid(booking);
            return totalPrice - totalPaid;
        },

        getBoardingHouseId(booking) {
            if (!booking) return null;
            return booking?.room?.boarding_house?.boarding_house_id || 
                   booking?.room?.boarding_house_id || 
                   null;
        },

        async fetchBoardingHouseDetails(boardingHouseId) {
            try {
                if (!boardingHouseId) {
                    throw new Error('Invalid boarding house ID');
                }

                const response = await axios.get(`/user/boarding-houses/${boardingHouseId}/details`);
                
                // Check if response exists and has data
                if (response?.data?.status === 'success' && response.data.data) {
                    return response.data.data;
                } else {
                    console.error('Invalid response format:', response?.data);
                    throw new Error(response?.data?.message || 'Failed to fetch boarding house details');
                }
            } catch (error) {
                console.error('Error fetching boarding house details:', error);
                // Return a default object instead of throwing to prevent cascading errors
                return {
                    name: 'Unknown Boarding House',
                    address: 'Address not available',
                    // Add other default properties as needed
                };
            }
        },

        getBoardingHouseAddress(booking) {
            if (!booking?.room?.boarding_house) {
                return 'Address not available';
            }
            const boardingHouse = booking.room.boarding_house;
            return `${boardingHouse.name} - ${boardingHouse.address}`;
        },

        getMapUrl(mapLink) {
            if (!mapLink) {
                // If no map link is provided, try to use the boarding house map link as fallback
                const bhMapLink = booking.room?.boarding_house?.map_link;
                if (bhMapLink) {
                    return bhMapLink;
                }
                // If no map links available, return default Philippines map
                return "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3931.712556440649!2d125.49050407450618!3d9.790371276607797!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwNDcnMjUuMyJOIDEyNcKwMjknMzUuMSJF!5e0!3m2!1sen!2sph!4v1730087382440!5m2!1sen!2sph";
            }
            return mapLink;
        },

        showMapModal(mapLink) {
            this.currentMapUrl = this.getMapUrl(mapLink);
            this.showingMap = true;
            document.body.style.overflow = 'hidden';
        },

        closeMapModal() {
            this.showingMap = false;
            this.currentMapUrl = '';
            document.body.style.overflow = 'auto';
        },

        // Add this method to properly format the payment method for display
        formatPaymentMethod(method) {
            const methods = {
                'cash': 'Cash',
                'gcash_qr': 'GCash',
                'paymaya_qr': 'PayMaya'
            };
            return methods[method] || method;
        },

        handlePaymentMethodChange(event) {
            console.log('Payment method changed:', event.target.value);
            console.log('Current paymentMethod:', this.paymentMethod);
        },
    },

    async mounted() {
        const userName = localStorage.getItem("userName");

        const userId = localStorage.getItem("userId");

        if (!userId) {
            this.$router.push("/login");

            return;
        }

        if (userName) {
            this.isLoggedIn = true;

            this.userName = userName;
        }

        await this.fetchBookings();
    },
};
</script>

<style scoped>
.overall {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    margin-top: 2rem;
}

.home {
    margin-bottom: 2rem;
}

.helper {
    color: #794646;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.contains {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.heads {
    color: #794646;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    font-weight: 500;
}

.img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 8px;
    margin: 0;
    transition: transform 0.3s ease;
}

.img:hover {
    transform: none;
}

.img[src=""] {
    animation: loading 1.5s infinite;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.text {
    color: #666;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.texts {
    color: #333;
    margin-bottom: 1rem;
}

.inside {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
}

.itemss {
    text-align: left;
}

.title {
    color: #794646;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.memo {
    color: #333;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.pay-now {
    background: #28a745; /* Green color for payment button */
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
    font-weight: 500;
    flex: 1;
}

.pay-now:hover:not(:disabled) {
    background: #218838;
}

.pay-now:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.details {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 500px;
    z-index: 1000;
}

.x-icon {
    position: absolute;
    top: 1rem;
    right: -2rem;
    cursor: pointer;
    width: 24px;
    height: 24px;
    padding: 4px;
    border-radius: 50%;
    transition: background 0.3s;
}

.x-icon:hover {
    background: #f0f0f0;
}

.information {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.inseee {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.insss {
    color: #333;
    font-weight: 500;
    margin-bottom: 1rem;
}

.cancel {
    width: 100%;
    padding: 1rem;
    margin-top: 1.5rem;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.cancel:hover:not(:disabled) {
    background: #bb2d3b;
}

.cancel:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.loading-state,
.error-state,
.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.loading-state {
    color: #794646;
}

.error-state {
    color: #dc3545;
}

.empty-state {
    color: #666;
}

/* Add overlay when modal is open */
.details::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: -1;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .overall {
        padding: 1rem;
    }

    .details {
        width: 95%;
        padding: 1.5rem;
    }

    .img {
        height: 200px;
    }
}

.booking-grid {
    display: grid;
    grid-template-columns: 40% 1fr;
    gap: 2rem;
    align-items: start;
}

.image-section {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.image-section .heads {
    margin: 0;
    padding: 0;
}

.image-container {
    position: relative;
    cursor: pointer;
    overflow: hidden;
    border-radius: 8px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-overlay span {
    color: white;
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.5);
}

.image-container:hover .image-overlay {
    opacity: 1;
}

.image-modal {
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

.modal-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    margin: 20px;
}

.modal-image {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
}

.close-modal {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
    padding: 10px;
    line-height: 1;
}

.close-modal:hover {
    color: #ddd;
}

.location-info {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.location-details {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.location-details i {
    color: #794646;
    font-size: 1rem;
}

.text {
    color: #666;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.texts {
    color: #333;
    font-size: 0.95rem;
    margin: 0;
}

.details-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.date-info {
    background: #fff;
    padding: 1rem;
    border-radius: 6px;
    margin: 1rem 0;
}

.date-info p {
    margin: 0.5rem 0;
    color: #333;
}

.date-info span {
    color: #794646;
    font-weight: 500;
    margin-right: 0.5rem;
}

.button-group {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.button-group button {
    flex: 1; /* Make buttons equal width */
}

.cancel-inline {
    background: #fff;
    color: #dc3545;
    border: 1px solid #dc3545;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.cancel-inline:hover:not(:disabled) {
    background: #dc3545;
    color: white;
}

.cancel-inline:disabled {
    background: #f8f9fa;
    border-color: #ccc;
    color: #666;
    cursor: not-allowed;
}

/* Update responsive design */
@media (max-width: 768px) {
    .booking-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .image-section {
        gap: 0.75rem;
    }

    .img {
        height: 250px;
    }

    .button-group {
        flex-direction: column;
    }
    
    .button-group button {
        width: 100%;
    }
}

.button-container {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.cancel, .delete {
    flex: 1;
    padding: 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.cancel {
    background: #dc3545;
    color: white;
}

.cancel:hover:not(:disabled) {
    background: #bb2d3b;
}

.cancel:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.delete {
    background: #6c757d;
    color: white;
}

.delete:hover {
    background: #5a6268;
}

/* Make buttons stack on mobile */
@media (max-width: 768px) {
    .button-container {
        flex-direction: column;
    }
}

.delete-container {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
    text-align: right;
}
.delete-button {
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.delete-button:hover {
    background: #5a6268;
}

/* Update responsive styles */
@media (max-width: 768px) {
    .delete-container {
        text-align: center;
    }
    
    .delete-button {
        width: 100%;
    }
}

.payment-modal {
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

.payment-details {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.payment-title {
    color: #794646;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.amount-section,
.payment-method-section,
.qr-section,
.proof-section,
.reference-section {
    margin-bottom: 1.5rem;
}

.amount-options,
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.amount-option,
.payment-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.amount-option:hover,
.payment-method:hover {
    background: #f8f9fa;
}

.qr-container {
    display: flex;
    justify-content: center;
    margin: 1rem 0;
}

.qr-image {
    max-width: 200px;
    border-radius: 8px;
}

.proof-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px dashed #ddd;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.proof-preview {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
    border-radius: 6px;
}

.reference-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.submit-payment {
    width: 100%;
    padding: 1rem;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
    font-weight: 500;
    margin-top: 1rem;
}

.submit-payment:hover:not(:disabled) {
    background: #218838;
}

.submit-payment:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.null-qr {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.null-qr img {
    opacity: 0.6;
    max-width: 150px;
    margin-bottom: 1rem;
}

.null-qr-text {
    color: #666;
    font-size: 0.9rem;
}

.qr-container {
    min-height: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px dashed #ddd;
    border-radius: 8px;
    margin: 1rem 0;
}

.qr-image {
    max-width: 200px;
    max-height: 200px;
    object-fit: contain;
}

.loading-indicator {
    color: #666;
    font-size: 0.9rem;
    padding: 2rem;
    text-align: center;
}

.qr-modal {
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

.qr-modal .modal-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    margin: 20px;
}

.qr-modal-image {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
}

.qr-modal .close-modal {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
    padding: 10px;
    line-height: 1;
}

.qr-modal .close-modal:hover {
    color: #ddd;
}

.qr-image {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.qr-image:hover {
    transform: scale(1.05);
}

.booking-info {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.booking-info h4 {
    color: #794646;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.booking-info p {
    margin: 0.25rem 0;
    color: #333;
    font-size: 0.95rem;
}

.booking-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.booking-title {
    color: #794646;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.booking-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: capitalize;
}

.booking-status.pending {
    background: #fff3cd;
    color: #856404;
}

.booking-status.confirmed {
    background: #d4edda;
    color: #155724;
}

.booking-status.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.payment-complete {
    color: #28a745;
    font-weight: 500;
    text-align: center;
    padding: 0.75rem;
    background: #d4edda;
    border-radius: 6px;
    margin: 0.5rem 0;
}

.amount-option {
    position: relative;
    display: block;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.amount-option.disabled {
    opacity: 0.6;
    background: #f8f9fa;
}

.amount-option input {
    margin-right: 0.5rem;
}

.option-note {
    display: block;
    color: #666;
    font-size: 0.8rem;
    margin-top: 0.25rem;
    font-style: italic;
}

.amount-option input:disabled + span {
    color: #666;
}

.payment-status-section {
    margin-top: 1rem;
}

.payment-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
}

.payment-amount {
    font-size: 0.9rem;
    margin-left: 1rem;
}

.payment-status.completed {
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

.view-map-btn {
    background: #794646;
    color: white;
    border: none;
    padding: 0.75rem 1.25rem;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
    transition: background-color 0.3s;
    margin-left: 1rem;
}

.view-map-btn i {
    color: #fff;
    font-size: 1.2rem;
    opacity: 0.9;
}

.view-map-btn:hover {
    background: #623939;
    transform: translateY(-1px);
}

.view-map-btn:hover i {
    opacity: 1;
}

.map-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
}

.map-modal-content {
    position: relative;
    width: 90%;
    max-width: 900px;
    background: white;
    border-radius: 12px;
    padding: 1rem;
}

.map-container {
    border-radius: 8px;
    overflow: hidden;
    height: 70vh;
}

.map-container iframe {
    width: 100%;
    height: 100%;
    border: none;
}

@media (max-width: 768px) {
    .map-modal-content {
        width: 95%;
        margin: 1rem;
    }

    .map-container {
        height: 60vh;
    }
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 1rem 0;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-method:hover {
    background-color: #f8f9fa;
    border-color: #794646;
}

.payment-method input[type="radio"] {
    width: 18px;
    height: 18px;
    margin: 0;
    cursor: pointer;
}

.payment-method span {
    font-size: 1rem;
    color: #333;
}

/* Add a selected state style */
.payment-method input[type="radio"]:checked + span {
    color: #794646;
    font-weight: 500;
}

.payment-method input[type="radio"]:checked {
    accent-color: #794646;
}
</style>

