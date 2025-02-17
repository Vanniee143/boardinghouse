<template>
    <div id="app">
        <Navbar />
        
        <div class="overall">
            <div class="content-wrapper">
                <div class="home">
                    <h4 class="helper">Room Reviews</h4>
                    <select 
                        v-model="selectedBoardingHouseId" 
                        @change="onBoardingHouseChange"
                        class="boarding-house-select"
                    >
                        <option value="" disabled>Select a Boarding House</option>
                        <option 
                            v-for="house in boardingHouses" 
                            :key="house.boarding_house_id" 
                            :value="house.boarding_house_id"
                        >
                            {{ house.name }}
                        </option>
                    </select>
                </div>

                <div v-if="boardingHouse" class="containss">
                    <h4 class="head">{{ boardingHouse.name }}</h4>
                    <img :src="getBoardingHouseImage(boardingHouse?.bh_images)" 
                         :alt="boardingHouse.name" 
                         class="img"
                         @click="openModal(getBoardingHouseImage(boardingHouse?.bh_images))"
                         style="cursor: pointer">
                    
                    <div class="rooms-section">
                        <h5 class="section-title">Rooms</h5>
                        <div class="rooms-grid">
                            <div v-for="room in rooms" :key="room.room_id" class="room-card">
                                <img :src="getRoomImage(room.room_images)" 
                                     :alt="room.room_name" 
                                     class="room-image"
                                     @click="openModal(getRoomImage(room.room_images))"
                                     style="cursor: pointer">
                                <div class="room-details">
                                    <h6 class="room-name">{{ room.room_name }}</h6>
                                    <p class="room-price">₱{{ room.price }}/month</p>
                                    <p class="room-status" :class="room.status.toLowerCase()">
                                        Status: {{ room.status }}
                                    </p>
                                    
                                    <div class="room-reviews">
                                        <h6 class="reviews-title">Reviews for this room</h6>
                                        <div v-if="loading" class="loading-state">
                                            Loading reviews...
                                        </div>
                                        <div v-if="error" class="error-state">
                                            {{ error }}
                                        </div>
                                        <div v-else-if="getRoomReviews(room.room_id).length === 0" class="no-reviews">
                                            {{ console.log('Reviews length for room', room.room_id, ':', getRoomReviews(room.room_id).length) }}
                                            No reviews yet for this room
                                        </div>
                                        <div v-else class="review-list">    
                                            <div v-for="review in getRoomReviews(room.room_id)" 
                                                 :key="review.id" 
                                                 class="review-item">
                                                <div class="review-header">
                                                    <div class="user-profile">
                                                        <img 
                                                            :src="getUserProfileImage(review.user_profile)" 
                                                            :alt="review.userName" 
                                                            class="user"
                                                            @error="handleProfileImageError"
                                                        >
                                                        <div class="review-info">
                                                            <p class="users">{{ review.userName }}</p>
                                                            <p class="date">{{ formatDate(review.date) }}</p>
                                                        </div>
                                                    </div>
                                                    <div v-if="isReviewOwner(review)" class="review-actions">
                                                        <button class="action-btn edit" @click="editReview(review)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="action-btn delete" @click="deleteReview(review.id)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="stars">
                                                    <span v-for="n in 5" :key="n" 
                                                          class="star" 
                                                          :class="{ active: n <= review.rating }">★</span>
                                                </div>
                                                <p class="rates">Rating: {{ review.rating }}/5</p>
                                                <p class="comment" v-if="review.comment">{{ review.comment }}</p>
                                                <div v-if="review.images && review.images.length > 0" class="review-images-grid">
                                                    <div v-for="(image, index) in Array.isArray(review.images) ? review.images : JSON.parse(review.images)" 
                                                         :key="index" 
                                                         class="review-image-container"
                                                         @click="openModal(`/storage/${image}`)">
                                                        <img :src="`/storage/${image}`" 
                                                             :alt="`Review image ${index + 1}`"
                                                             class="review-image"
                                                             @error="handleImageError">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div v-if="isLoggedIn" class="add-review-form">
                                            <h6 class="form-title">Add Your Review</h6>
                                            <div class="rating-input">
                                                <span v-for="n in 5" 
                                                      :key="n" 
                                                      class="star" 
                                                      :class="{ active: n <= roomReviews[room.room_id]?.rating }" 
                                                      @click="setRoomRating(room.room_id, n)">★</span>
                                            </div>
                                            <div class="review-input-container">
                                                <div class="message-box">
                                                    <textarea 
                                                        v-model="roomReviews[room.room_id].comment"
                                                        placeholder="Write your review here..."
                                                        class="comment-input"
                                                    ></textarea>
                                                    
                                                    <div v-if="roomReviews[room.room_id]?.imagePreviews?.length" class="image-previews-container">
                                                        <div v-for="(preview, index) in roomReviews[room.room_id].imagePreviews" 
                                                             :key="index" 
                                                             class="preview-item">
                                                            <img :src="preview" 
                                                                 alt="Preview" 
                                                                 class="preview-img">
                                                            <button @click="removeImage(room.room_id, index)" 
                                                                    class="remove-preview-btn">&times;</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="message-actions">
                                                    <div class="left-actions">
                                                        <label :for="'file-input-' + room.room_id" class="upload-btn">
                                                            <img src="@/assets/images/upload.png" alt="Upload" class="upload-icon">
                                                        </label>
                                                        <input 
                                                            :id="'file-input-' + room.room_id"
                                                            type="file"
                                                            accept="image/*"
                                                            class="file"
                                                            @change="(e) => handleFileUpload(e, room.room_id)"
                                                            multiple
                                                        >
                                                    </div>
                                                    <button 
                                                        class="send-btn"
                                                        @click="submitRoomReview(room.room_id)"
                                                        :disabled="!canSubmitReview(room.room_id)"
                                                    >
                                                        Submit Review
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-if="showModal" class="modal" @click="closeModal">
            <div class="modal-content">
                <button class="modal-close" @click="closeModal">&times;</button>
                <img :src="selectedImage" :alt="'Enlarged view'" @click.stop>
            </div>
        </div>

        <div v-if="showEditModal" class="edit-modal">
            <div class="edit-modal-content" @click.stop>
                <button class="modal-close" @click="closeEditModal">&times;</button>
                <h3>Edit Review</h3>
                <div class="rating-input">
                    <span v-for="n in 5" 
                          :key="n" 
                          class="star" 
                          :class="{ active: n <= editingReview.rating }" 
                          @click="editingReview.rating = n">★</span>
                </div>
                <textarea 
                    v-model="editingReview.comment"
                    class="edit-comment-input"
                    placeholder="Write your review here..."
                ></textarea>

                <div v-if="editingReview.imagePreviews?.length" class="edit-image-previews">
                    <div v-for="(preview, index) in editingReview.imagePreviews" 
                         :key="index" 
                         class="preview-item">
                        <img :src="preview" 
                             alt="Preview" 
                             class="preview-img">
                        <button @click="removeEditImage(index)" 
                                class="remove-preview-btn">&times;</button>
                    </div>
                </div>

                <div class="edit-image-upload">
                    <label :for="'edit-file-input'" class="upload-btn">
                        <img src="@/assets/images/upload.png" alt="Upload" class="upload-icon">
                        <span>Add Images</span>
                    </label>
                    <input 
                        id="edit-file-input"
                        type="file"
                        accept="image/*"
                        class="file"
                        @change="handleEditFileUpload"
                        multiple
                    >
                </div>

                <div class="edit-modal-actions">
                    <button class="cancel-btn" @click="closeEditModal">Cancel</button>
                    <button class="save-btn" @click="saveEditedReview">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Navbar from './layouts/Navbar.vue';
import userImage from '@/assets/images/User.png';
import defaultRoomImage from '@/assets/images/room2.png';

export default {
    name: 'UserReviews',
    components: {
      Navbar
    },
    data() {
        return {
            isLoggedIn: false,
            userName: '',
            boardingHouseId: null,
            boardingHouse: null,
            reviews: {},
            roomReviews: {},
            loading: false,
            error: null,
            userImage,
            defaultRoomImage,
            boardingHouses: [],
            selectedBoardingHouseId: '',
            rooms: [],
            showModal: false,
            selectedImage: null,
            showEditModal: false,
            editingReview: null,
            currentUserId: localStorage.getItem('userId')
        }
    },
    computed: {
        sortedReviews() {
            return [...this.reviews].sort((a, b) => new Date(b.date) - new Date(a.date));
        }
    },
    mounted() {
        const userName = localStorage.getItem('userName');
        const token = localStorage.getItem('token');
        if (userName && token) {
            this.isLoggedIn = true;
            this.userName = userName;
        }
        
        // Get the ID from route params
        this.boardingHouseId = this.$route.params.id;
        
        if (!this.boardingHouseId) {
            console.error('No boarding house ID provided');
            return;
        }
        
        console.log('Mounted with boarding house ID:', this.boardingHouseId);
        
        this.fetchBoardingHouseDetails();
        this.fetchRooms().then(() => {
            this.fetchReviews();
        });
        
        // Always fetch the list of boarding houses
        this.fetchBoardingHouses();
    },
    methods: {
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        async fetchBoardingHouseDetails() {
            try {
                console.log('Fetching details for boarding house:', this.boardingHouseId);
                const response = await axios.get(`/user/boarding-houses/${this.boardingHouseId}/details`);
                
                if (response.data.status === 'success' && response.data.data) {
                    this.boardingHouse = response.data.data;
                    console.log('Boarding house details:', this.boardingHouse);
                } else {
                    throw new Error(response.data.message || 'Failed to fetch boarding house');
                }
            } catch (error) {
                console.error('Error fetching boarding house details:', error);
                this.error = error.message || 'Failed to fetch boarding house details';
                this.boardingHouse = null;
            }
        },
        async fetchReviews() {
            this.loading = true;
            this.error = null;
            
            try {
                if (!this.rooms || !this.rooms.length) {
                    console.log('No rooms available to fetch reviews for');
                    return;
                }

                // Initialize reviews object
                this.reviews = {};

                // Fetch reviews for each room
                for (const room of this.rooms) {
                    try {
                        console.log(`Fetching reviews for room ${room.room_id}`);
                        const response = await axios.get(`/user/rooms/${room.room_id}/reviews`);
                        
                        console.log('Raw response:', response);
                        
                        if (response.data.status === 'success') {
                            if (Array.isArray(response.data.data)) {
                                console.log(`Reviews found for room ${room.room_id}:`, response.data.data);
                                this.reviews[room.room_id] = response.data.data;
                            } else {
                                console.warn(`Invalid reviews data format for room ${room.room_id}`);
                                this.reviews[room.room_id] = [];
                            }
                        } else {
                            console.warn(`No reviews found for room ${room.room_id}`);
                            this.reviews[room.room_id] = [];
                        }
                    } catch (error) {
                        console.error(`Error fetching reviews for room ${room.room_id}:`, error);
                        if (error.response) {
                            console.error('Error response:', error.response.data);
                        }
                        this.reviews[room.room_id] = [];
                    }
                }

                console.log('Final reviews object:', this.reviews);

            } catch (error) {
                console.error('Error fetching reviews:', error);
                this.error = 'Failed to fetch reviews. Please try again later.';
            } finally {
                this.loading = false;
            }
        },
        async submitReview() {
            if (!this.isLoggedIn) {
                alert('Please log in to submit a review');
                return;
            }

            if (!this.newReview.rating || !this.newReview.comment) {
                alert('Please provide both rating and comment');
                return;
            }

            if (this.isSubmitting) return;
            this.isSubmitting = true;

            try {
                const formData = new FormData();
                formData.append('boarding_house_id', this.boardingHouseId);
                formData.append('rating', this.newReview.rating);
                formData.append('comment', this.newReview.comment);
                if (this.newReview.image) {
                    formData.append('image', this.newReview.image);
                }

                const response = await axios.post('/boarding-houses/reviews', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    }
                });

                if (response.data.status === 'success') {
                    // Add the new review to the list
                    const newReview = {
                        id: response.data.data.id,
                        userName: this.userName,
                        date: new Date().toISOString(),
                        rating: this.newReview.rating,
                        comment: this.newReview.comment,
                        image: response.data.data.image_url
                    };

                    this.reviews.unshift(newReview);

                    // Reset the form
                    this.newReview = {
                        rating: 0,
                        comment: '',
                        image: null
                    };

                    // Reset file input
                    const fileInput = document.getElementById('fileInput');
                    if (fileInput) fileInput.value = '';

                    alert('Review submitted successfully!');
                } else {
                    throw new Error(response.data.message || 'Failed to submit review');
                }
            } catch (error) {
                console.error('Error submitting review:', error);
                alert('Failed to submit review. Please try again.');
            } finally {
                this.isSubmitting = false;
            }
        },
        handleFileUpload(event, roomId) {
            const files = event.target.files;
            if (files) {
                // Initialize if not exists
                if (!this.roomReviews[roomId]) {
                    this.roomReviews[roomId] = {
                        rating: 0,
                        comment: '',
                        images: [], // Array to store multiple images
                        imagePreviews: [] // Array to store multiple previews
                    };
                }

                // Process each file
                Array.from(files).forEach(file => {
                    if (file.size > 5000000) { // 5MB limit
                        alert('File size should not exceed 5MB');
                        return;
                    }
                    if (!file.type.startsWith('image/')) {
                        alert('Please upload image files only');
                        return;
                    }

                    // Create preview URL
                    const previewUrl = URL.createObjectURL(file);
                    
                    // Add to arrays
                    this.roomReviews[roomId].images.push(file);
                    this.roomReviews[roomId].imagePreviews.push(previewUrl);
                });
            }
        },
        navigate(event) {
            const route = event.target.value;
            if (route === '/login') {
                this.handleLogout();
            } else {
                this.$router.push(route);
            }
        },
        handleLogout() {
            localStorage.removeItem('userName');
            localStorage.removeItem('userId');
            localStorage.removeItem('token');
            this.$router.push('/login');
        },
        setRating(rating) {
            this.newReview.rating = rating;
        },
        getBoardingHouseImage(image) {
            if (image) {
                // If it's already a full URL, return it as is
                if (image.startsWith('http')) {
                    return image;
                }
                // Otherwise, prepend the storage path
                return `/storage/${image}`;
            }
            return this.defaultRoomImage;
        },
        async fetchBoardingHouses() {
            try {
                const response = await axios.get('/boarding-houses/available');
                if (response.data.status === 'success') {
                    this.boardingHouses = response.data.data;
                    
                    // If we have a boardingHouseId from route params, select it
                    if (this.boardingHouseId) {
                        this.selectedBoardingHouseId = this.boardingHouseId;
                    }
                } else {
                    throw new Error(response.data.message || 'Failed to fetch boarding houses');
                }
            } catch (error) {
                console.error('Error fetching boarding houses:', error);
            }
        },
        async onBoardingHouseChange() {
            if (!this.selectedBoardingHouseId) return;
            
            // Update URL without refreshing
            this.$router.push({
                name: 'user.reviews',
                params: { id: this.selectedBoardingHouseId }
            }, () => {}, // Empty callback to handle navigation errors
            { replace: true }); // Replace current history entry instead of adding new one
            
            this.boardingHouseId = this.selectedBoardingHouseId;
            
            // Fetch data directly without relying on route change
            await Promise.all([
                this.fetchBoardingHouseDetails(),
                this.fetchRooms().then(() => this.fetchReviews())
            ]);
        },
        async fetchRooms() {
            try {
                if (!this.boardingHouseId) {
                    console.warn('No boarding house ID provided');
                    return;
                }

                const response = await axios.get(`/user/rooms/${this.boardingHouseId}/list`);
                if (response.data.status === 'success') {
                    this.rooms = response.data.data;
                    // Initialize review form for each room
                    this.rooms.forEach(room => {
                        if (!this.roomReviews[room.room_id]) {
                            this.roomReviews[room.room_id] = {
                                rating: 0,
                                comment: '',
                                images: [], // Array to store multiple images
                                imagePreviews: [] // Array to store multiple previews
                            };
                        }
                    });
                } else {
                    throw new Error(response.data.message || 'Failed to fetch rooms');
                }
            } catch (error) {
                console.error('Error fetching rooms:', error);
                this.rooms = [];
            }
        },
        getRoomImage(images) {
            if (images) {
                // If it's already a full URL, return it as is
                if (images.startsWith('http')) {
                    return images;
                }
                // Otherwise, prepend the storage path
                return `/storage/${images}`;
            }
            return this.defaultRoomImage;
        },
        getRoomReviews(roomId) {
            const reviews = this.reviews[roomId] || [];
            console.log(`Getting reviews for room ${roomId}:`, reviews);
            return reviews;
        },
        setRoomRating(roomId, rating) {
            if (!this.roomReviews[roomId]) {
                this.roomReviews[roomId] = { 
                    rating: 0, 
                    comment: '', 
                    images: [], // Array to store multiple images
                    imagePreviews: [] // Array to store multiple previews
                };
            }
            this.roomReviews[roomId].rating = rating;
        },
        canSubmitReview(roomId) {
            const review = this.roomReviews[roomId];
            return review && review.rating > 0 && review.comment.trim() !== '';
        },
        async submitRoomReview(roomId) {
            try {
                // Verify we have the boarding house ID
                if (!this.boardingHouseId) {
                    throw new Error('Boarding house ID is missing');
                }

                const formData = new FormData();
                formData.append('room_id', roomId);
                formData.append('boarding_house_id', this.boardingHouseId); // Make sure this is set
                formData.append('rating', this.roomReviews[roomId].rating);
                formData.append('comment', this.roomReviews[roomId].comment);
                
                // Debug log
                console.log('Submitting review for:', {
                    roomId,
                    boardingHouseId: this.boardingHouseId,
                    rating: this.roomReviews[roomId].rating,
                    comment: this.roomReviews[roomId].comment
                });

                // Handle multiple images
                if (this.roomReviews[roomId].images?.length > 0) {
                    this.roomReviews[roomId].images.forEach((image, index) => {
                        formData.append(`images[${index}]`, image);
                    });
                }

                const response = await axios.post('/user/reviews', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.data.status === 'success') {
                    this.resetReviewForm(roomId);
                    await this.fetchRoomReviews(roomId);
                    alert('Review submitted successfully!');
                }
            } catch (error) {
                console.error('Error submitting review:', error);
                if (error.response?.data?.message) {
                    alert(error.response.data.message);
                } else {
                    alert(error.message || 'Failed to submit review. Please try again.');
                }
            }
        },
        openModal(imageUrl) {
            this.selectedImage = imageUrl;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.showModal = false;
            this.selectedImage = null;
            document.body.style.overflow = 'auto';
        },
        getImagePreview(image) {
            if (image) {
                // If it's already a full URL, return it as is
                if (image.startsWith('http')) {
                    return image;
                }
                // Otherwise, prepend the storage path
                return `/storage/${image}`;
            }
            return this.defaultRoomImage;
        },
        removeImage(roomId, index) {
            if (this.roomReviews[roomId]) {
                // Remove specific image and preview
                this.roomReviews[roomId].images.splice(index, 1);
                this.roomReviews[roomId].imagePreviews.splice(index, 1);
            }
        },
        handleImageError(e) {
            console.error('Image failed to load:', e.target.src);
            e.target.src = this.defaultRoomImage; // Fallback to default image
        },
        isReviewOwner(review) {
            return parseInt(review.user_id) === parseInt(this.currentUserId);
        },
        editReview(review) {
            console.log('Editing review with images:', review.images);
            this.editingReview = {
                id: review.id,
                rating: review.rating,
                comment: review.comment,
                images: [], // For new file uploads
                existingImages: Array.isArray(review.images) ? review.images : JSON.parse(review.images || '[]'),
                imagePreviews: []
            };
            
            // Set up previews for existing images
            this.editingReview.imagePreviews = this.editingReview.existingImages.map(img => `/storage/${img}`);
            
            this.showEditModal = true;
        },
        handleEditFileUpload(event) {
            const files = event.target.files;
            if (files) {
                Array.from(files).forEach(file => {
                    if (file.size > 5000000) { // 5MB limit
                        alert('File size should not exceed 5MB');
                        return;
                    }
                    if (!file.type.startsWith('image/')) {
                        alert('Please upload image files only');
                        return;
                    }

                    this.editingReview.images.push(file);
                    this.editingReview.imagePreviews.push(URL.createObjectURL(file));
                });
            }
        },
        removeEditImage(index) {
            // If index is within existing images range
            if (index < this.editingReview.existingImages.length) {
                this.editingReview.existingImages.splice(index, 1);
            } else {
                // Adjust index for new images array
                const newIndex = index - this.editingReview.existingImages.length;
                this.editingReview.images.splice(newIndex, 1);
            }
            this.editingReview.imagePreviews.splice(index, 1);
        },
        async saveEditedReview() {
            try {
                const formData = new FormData();
                formData.append('rating', this.editingReview.rating);
                formData.append('comment', this.editingReview.comment);
                formData.append('_method', 'PUT');
                
                // Append existing images that weren't removed
                formData.append('existing_images', JSON.stringify(this.editingReview.existingImages));
                
                // Append new images
                this.editingReview.images.forEach((image, index) => {
                    formData.append(`images[${index}]`, image);
                });

                const response = await axios.post(
                    `/user/reviews/${this.editingReview.id}`, 
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }
                );

                if (response.data.status === 'success') {
                    await this.fetchReviews(); // Refresh reviews to get updated data
                    this.closeEditModal();
                    alert('Review updated successfully!');
                }
            } catch (error) {
                console.error('Error updating review:', error);
                alert(error.response?.data?.message || 'Failed to update review');
            }
        },
        async deleteReview(reviewId) {
            if (!confirm('Are you sure you want to delete this review?')) return;
            
            try {
                const response = await axios.delete(`/user/reviews/${reviewId}`);
                if (response.data.status === 'success') {
                    await this.refreshReviews();
                    alert('Review deleted successfully!');
                }
            } catch (error) {
                console.error('Error deleting review:', error);
                alert(error.response?.data?.message || 'Failed to delete review');
            }
        },
        async fetchRoomReviews(roomId) {
            try {
                console.log(`Fetching reviews for room ${roomId}`);
                const response = await axios.get(`/user/rooms/${roomId}/reviews`);
                console.log('Review response:', response.data);
                
                if (response.data.status === 'success') {
                    // Ensure images are properly parsed
                    const reviews = response.data.data.map(review => ({
                        ...review,
                        images: Array.isArray(review.images) ? review.images : 
                                (typeof review.images === 'string' ? JSON.parse(review.images) : [])
                    }));
                    
                    // Update reviews using standard object assignment
                    this.reviews = {
                        ...this.reviews,
                        [roomId]: reviews
                    };
                    console.log(`Updated reviews for room ${roomId}:`, this.reviews[roomId]);
                }
            } catch (error) {
                console.error(`Error fetching reviews for room ${roomId}:`, error);
                this.reviews[roomId] = [];
            }
        },
        async refreshReviews() {
            try {
                if (!this.rooms || !this.rooms.length) return;
                
                // Fetch reviews for all rooms
                const reviewPromises = this.rooms.map(room => this.fetchRoomReviews(room.room_id));
                await Promise.all(reviewPromises);
                
            } catch (error) {
                console.error('Error refreshing reviews:', error);
                this.error = 'Failed to refresh reviews';
            }
        },
        resetReviewForm(roomId) {
            this.roomReviews[roomId] = {
                rating: 0,
                comment: '',
                images: [],
                imagePreviews: []
            };
        },
        closeEditModal() {
            this.showEditModal = false;
            this.editingReview = null;
        },
        getUserProfileImage(profilePath) {
            if (profilePath) {
                // If it's already a full URL, return it as is
                if (profilePath.startsWith('http')) {
                    return profilePath;
                }
                // If it's a storage path, prepend storage
                return `/storage/${profilePath}`;
            }
            return this.userImage; // Fall back to default user image
        },
        handleProfileImageError(e) {
            console.error('Profile image failed to load:', e.target.src);
            e.target.src = this.userImage; // Fall back to default user image
        },
    },
    watch: {
        '$route.params.id': {
            handler(newId) {
                if (newId && newId !== this.boardingHouseId && !this.selectedBoardingHouseId) {
                    // Only handle route changes that weren't triggered by the select
                    this.boardingHouseId = newId;
                    this.selectedBoardingHouseId = newId;
                    this.fetchBoardingHouseDetails();
                    this.fetchRooms();
                    this.fetchReviews();
                }
            },
            immediate: true
        }
    },
    created() {
        const token = localStorage.getItem('token');
        if (!token) {
            console.error('No authentication token found');
            // Redirect to login or handle unauthorized state
            return;
        }
        
        if (this.boardingHouseId) {
            this.fetchBoardingHouseDetails();
            this.fetchRooms();
            this.fetchReviews();
        }
        
        this.fetchBoardingHouses();
    }
}
</script>

<style scoped>

.star {
    cursor: pointer;
    color: #ddd;
    font-size: 28px;
    margin-right: 5px;
}

.star.active {
    color: #ffd700;
}

.social-icons {
    display: flex;
    gap: 15px;
    align-items: center;
}

.icons {
    cursor: pointer;    
    margin-bottom: 15px;
}

.icons:hover {
    transform: scale(1.1);
}

.no-reviews {
    text-align: center;
    color: #666;
    padding: 20px;
    font-style: italic;
    font-size: 16px;
}

.buttonn:disabled {
    background: #cccccc;
    cursor: not-allowed;
}

.overall {
    min-height: calc(100vh - 350px);
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    margin-top: 50px;
    margin-bottom: 100px;
}

#app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.content-wrapper {
    flex: 1;
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

.containss {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.head {
    color: #794646;
    font-size: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.insides {
    padding: 1rem 0;
}

.read {
    color: #794646;
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.review-items-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.review-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.user {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #794646;
    flex-shrink: 0;
}

.review-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.users {
    font-weight: 600;
    color: #333;
    margin: 0;
    font-size: 18px;
}

.date {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

.stars {
    margin-bottom: 0.5rem;
}

.rates {
    color: #666;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.comment {
    color: #333;
    line-height: 1.5;
}

.new-review-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.commentss {
    color: #794646;
    font-size: 1.2rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.rating-input {
    margin: 15px 0;
}

.comment-input-container {
    position: relative;
    margin-bottom: 1rem;
}

.comment-input {
    width: 80%;
    min-height: 120px;
    max-height: 200px;
    padding: 10px;
    padding-right: 120px; /* Make space for preview */
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 10px 0;
    resize: vertical;
    font-size: 16px;
}

.custom-file-upload {
    display: inline-flex;
    align-items: center;
    padding: 8px 15px;
    background: #794646;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.custom-file-upload:hover {
    background: #633939;
}

.upload-icon {
    width: 20px;
    height: 20px;
    margin-right: 8px;
    filter: brightness(0) invert(1); /* Makes the icon white */
}

.file {
    display: none;
}

.buttonn {
    background: #794646;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    margin-top: 15px;
    font-size: 16px;
    font-weight: 600;
}

.buttonn:hover:not(:disabled) {
    background: #633939;
}

.footer {
    background: #F5F2F2;
    height: 23vh;
    margin-top: auto;
    width: 100%;
}

.social {
    transform: translate(-5%, 35%);
    width: 30%;
    position: static;
}

.footer-title {
    text-align: center;
    color: #794646;
    text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    font-family: arial;
    font-size: 16px;
    font-weight: 700;
    line-height: normal;
}

.social-icons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.icons {
    width: 40px;
    color: #5F5F5F;
}

.infos {
    color: #794646;
    font-family: arial;
    font-size: 14px;
    font-weight: 700;
    width: 150px;
    transform: translate(300%, -70%);
    position: static;
}

.infos2 {
    color: #794646;
    font-family: arial;
    font-size: 14px;
    font-weight: 700;
    width: 250px;
    transform: translate(280%, -183%);
    position: static;
}

.copywright {
    color: #794646;
    text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    font-family: arial;
    font-size: 12px;
    font-weight: 700;
    transform: translate(270%, -900%);
    width: 400px;
    position: static;
}

.boarding-house-select {
    width: 100%;
    max-width: 400px;
    padding: 0.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #333;
    font-size: 1rem;
    background-color: white;
}

.boarding-house-select:focus {
    outline: none;
    border-color: #794646;
}

@media (max-width: 768px) {
    .footer {
        height: auto;
        padding: 20px;
    }

    .social, .infos, .infos2 {
        transform: none;
        text-align: center;
        margin-bottom: 30px;
        width: 100%;
    }

    .copywright {
        transform: none;
        width: 100%;
        margin-top: 30px;
    }

    .social-icons {
        justify-content: center;
    }
}

.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.room-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 20px;
}

.room-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.room-details {
    padding: 20px;
}

.room-name {
    font-size: 24px;
    color: #794646;
    margin-bottom: 10px;
    font-weight: 600;
}

.room-price {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
}

.review-image {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin-top: 0.5rem;
    cursor: pointer;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    max-width: 90%;
    max-height: 90%;
}

.modal-content img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
}

.submit-review {
    background: #794646;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    margin-top: 1rem;
}

.submit-review:disabled {
    background: #cccccc;
    cursor: not-allowed;
}

.image-upload {
    margin: 1rem 0;
}

.custom-file-upload {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    color: #794646;
}

.upload-icon {
    width: 20px;
    height: 20px;
}

.file {
    display: none;
}

.reviews-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}

.form-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}

.room-status {
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 16px;
    display: inline-block;
}

.room-status.available {
    background-color: #e6ffe6;
    color: #006600;
}

.room-status.occupied {
    background-color: #ffe6e6;
    color: #cc0000;
}

.room-status.maintenance {
    background-color: #fff3e6;
    color: #cc6600;
}

.img, .room-image {
    transition: transform 0.2s ease-in-out;
}

.img:hover, .room-image:hover {
    transform: scale(1.02);
    opacity: 0.9;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    cursor: pointer;
}

.modal-content {
    position: relative;
    max-width: 90%;
    max-height: 90vh;
    margin: auto;
}

.modal-content img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.modal-close {
    position: absolute;
    top: -40px;
    right: -40px;
    color: white;
    font-size: 30px;
    cursor: pointer;
    background: none;
    border: none;
    padding: 10px;
}

.modal-close:hover {
    color: #ddd;
}

.image-preview-container {
    position: relative;
    display: inline-block;
    margin: 10px 0;
    max-width: 200px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preview-image {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 8px;
}

.remove-preview {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(255, 0, 0, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    line-height: 1;
}

.remove-preview:hover {
    background: rgba(255, 0, 0, 1);
}

.review-input-container {
    position: relative;
    margin-bottom: 1rem;
}

.textarea-image-preview {
    position: absolute;
    bottom: 10px;
    right: 10px;
    max-width: 100px;
    max-height: 100px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(121, 70, 70, 0.2);
    background: white;
    padding: 2px;
    border: 1px solid #794646;
}

.remove-preview-button {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #794646;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
    line-height: 1;
    z-index: 1;
}

.remove-preview-button:hover {
    background: #633939;
}

.review-input-container {
    background: #fff;
    border: 1px solid #794646;
    border-radius: 12px;
    overflow: hidden;
    margin: 10px 0;
}

.message-box {
    position: relative;
    padding: 10px;
    background: #fff;
}

.comment-input {
    width: 90%;
    min-height: 60px;
    max-height: 150px;
    padding: 12px;
    border: 1px solid #794646;
    border-radius: 8px;
    background: #fff;
    resize: none;
    font-size: 14px;
    line-height: 1.5;
    outline: none;
}

.comment-input:focus {
    border-color: #633939;
    box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.message-image-preview {
    margin-top: 8px;
    position: relative;
    display: inline-block;
    max-width: 150px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(121, 70, 70, 0.2);
}

.preview-img {
    width: 100%;
    height: auto;
    display: block;
}

.remove-preview-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(121, 70, 70, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    padding: 0;
}

.message-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: #fff;
    border-top: 1px solid #794646;
}

.left-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.upload-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #794646;
    cursor: pointer;
    transition: background 0.2s;
    padding: 0;
}

.upload-btn:hover {
    background: #633939;
}

.upload-icon {
    width: 18px;
    height: 18px;
    filter: brightness(0) invert(1);
    margin: 0;
    display: block;
    object-fit: contain;
}

.send-btn {
    background: #794646;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.send-btn:hover:not(:disabled) {
    background: #633939;
}

.send-btn:disabled {
    background: #cccccc;
    cursor: not-allowed;
}

.file {
    display: none;
}

.image-previews-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
    padding: 8px;
    border-radius: 8px;
    background: #f8f9fa;
}

.preview-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(121, 70, 70, 0.2);
}

.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-preview-btn {
    position: absolute;
    top: 2px;
    right: 2px;
    background: rgba(121, 70, 70, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    padding: 0;
}

.remove-preview-btn:hover {
    background: #794646;
}

.message-box {
    position: relative;
    padding: 10px;
    background: #fff;
}

.comment-input {
    width: 91%;
    min-height: 60px;
    padding: 12px;
    border: 1px solid #794646;
    border-radius: 8px;
    resize: none;
    font-size: 14px;
    line-height: 1.5;
}

.review-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
    margin-top: 10px;
    max-width: 100%;
}

.review-image-container {
    position: relative;
    width: 100%;
    padding-bottom: 100%; /* Creates a square aspect ratio */
}

.review-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.review-image:hover {
    transform: scale(1.05);
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
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

.modal-close {
    position: absolute;
    top: -40px;
    right: -40px;
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
    padding: 10px;
}

@media (max-width: 768px) {
    .review-images-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
    
    .modal-content {
        width: 95%;
    }
    
    .modal-close {
        top: -30px;
        right: 0;
    }
}

.review-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.action-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    transition: color 0.2s;
}

.action-btn:hover {
    color: #794646;
}

.edit {
    color: #333;
}

.delete {
    color: #cc0000;
}

.edit-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.edit-modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 90%;
    max-height: 90vh;
    overflow: auto;
}

.edit-comment-input {
    width: 100%;
    min-height: 120px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 10px 0;
    resize: vertical;
    font-size: 16px;
}

.edit-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 1rem;
}

.cancel-btn {
    background: #794646;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.cancel-btn:hover {
    background: #633939;
}

.save-btn {
    background: #794646;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.save-btn:hover {
    background: #633939;
}

.review-actions {
    margin-left: auto;
    display: flex;
    gap: 10px;
}

.action-btn {
    background: none;
    border: none;
    padding: 5px;
    cursor: pointer;
    transition: transform 0.2s ease;
    color: #794646;
}

.action-btn:hover {
    transform: scale(1.1);
}

.action-btn.edit {
    color: #4a90e2;
}

.action-btn.delete {
    color: #e25c5c;
}

.edit-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1100;
}

.edit-modal-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.edit-comment-input {
    width: 100%;
    min-height: 120px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 15px 0;
    resize: vertical;
    font-size: 14px;
}

.edit-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 1rem;
}

.cancel-btn, .save-btn {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.cancel-btn {
    background: #f5f5f5;
    border: 1px solid #ddd;
}

.save-btn {
    background: #794646;
    color: white;
    border: none;
}

.save-btn:hover {
    background: #633939;
}

.edit-image-previews {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 15px 0;
}

.edit-image-upload {
    margin: 15px 0;
}

.edit-image-upload .upload-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #794646;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.edit-image-upload .upload-btn:hover {
    background: #633939;
}

.edit-image-upload .upload-icon {
    width: 20px;
    height: 20px;
    filter: brightness(0) invert(1);
}
</style>