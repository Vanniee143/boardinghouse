<template>
    <div class="landlord-panel">
        <div class="navbars">
            <router-link to="/landlord/dashboard" class="logo-link">
                <img src="@/assets/images/image.png" alt="logo" class="logos" />
            </router-link>

            <router-link to="/landlord/dashboard" class="nl">
                <h5>SBH BOOKING</h5>
            </router-link>

            <div class="profile-section">
                <div class="landlord-profile">
                    <img
                        :src="
                            getProfilePicture(landlordProfile.profile_picture)
                        "
                        alt="profile"
                        class="profile-icon"
                        @error="handleImageError"
                        @load="handleImageLoad"
                    />

                    <span class="landlord-profile-name">{{
                        landlordName
                    }}</span>
                </div>
            </div>

            <div class="menu-container">
                <img
                    src="@/assets/images/Menu.png"
                    alt="menu"
                    class="menuimgg"
                    id="menuImage"
                />

                <select class="Menu" id="menuSelect" @change="navigateMenu">
                    <option value="#" disabled selected hidden>Menu</option>

                    <option value="/landlord/profile">Profile Settings</option>

                    <option value="/landlord/login">Logout</option>
                </select>
            </div>
        </div>

        <div class="overall">
            <div class="header-section">
                <h4 class="helper">ROOMS</h4>

                <button class="add-room-btn" @click="showAddRoomForm">
                    <img
                        src="@/assets/images/Plus.png"
                        alt="add"
                        class="add-icon white-icon"
                    />

                    Add New Room
                </button>
            </div>

            <div class="rooms-container">
                <div v-if="loading" class="loading">Loading rooms...</div>

                <div v-else-if="error" class="error">
                    {{ error }}
                </div>

                <div v-else-if="!rooms.length" class="no-rooms">
                    <p>No rooms available</p>

                    <p>Click "Add New Room" to add your first room</p>
                </div>

                <div v-else class="rooms-grid">
                    <div
                        v-for="room in rooms"
                        :key="room.room_id"
                        class="room-card"
                    >
                        <div class="room-image" @click="openImageModal(room)">
                            <img
                                :src="getRoomImage(room.room_images)"
                                :alt="room.room_name"
                                class="room-image"
                                @error="handleRoomImageError"
                            />
                        </div>

                        <div class="room-details">
                            <h3 class="room-name">{{ room.room_name }}</h3>

                            <div class="room-info">
                                <div class="info-row">
                                    <span class="info-label">Beds:</span>

                                    <span class="info-value">{{
                                        room.bed_quantity
                                    }}</span>
                                </div>

                                <div class="info-row">
                                    <span class="info-label">Status:</span>

                                    <span
                                        class="info-value"
                                        :class="room.status"
                                        >{{ room.status }}</span
                                    >
                                </div>

                                <div class="info-row">
                                    <span class="info-label">Price:</span>

                                    <span class="info-value">₱{{
                                        formatPrice(room.price)
                                    }}</span>
                                </div>
                            </div>

                            <div class="room-actions">
                                <button
                                    class="action-btn edit"
                                    @click="editRoom(room)"
                                >
                                    <img
                                        src="@/assets/images/Edit.png"
                                        alt="edit"
                                        class="action-icon"
                                    />

                                    Edit
                                </button>

                                <button
                                    class="action-btn delete"
                                    @click="confirmDelete(room)"
                                >
                                    <img
                                        src="@/assets/images/trash.png"
                                        alt="delete"
                                        class="action-icon"
                                    />

                                    Delete
                                </button>

                                <button
                                    class="action-btn view"
                                    @click="viewRoomDetails(room.room_id)"
                                >
                                    <img
                                        src="@/assets/images/eye.png"
                                        alt="view"
                                        class="action-icon"
                                    />

                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->

        <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
            <div class="modal-content" @click.stop>
                <button class="close-button" @click="closeImageModal">&times;</button>
                <div class="image-container">
                    <img 
                        :src="selectedImage"
                        :alt="selectedRoom?.room_name"
                        class="modal-image"
                    />
                </div>
            </div>
        </div>

        <!-- Room Details Modal -->
        <div v-if="showRoomDetailsModal && selectedRoom" class="modal-overlay" @click="closeRoomDetails">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3>Room Details</h3>
                    <button class="close-btn" @click="closeRoomDetails">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="room-info">
                        <h4>{{ selectedRoom.room_name }}</h4>
                        
                        <div class="info-row">
                            <span class="label">Status:</span>
                            <span :class="['status-badge', selectedRoom.status]">
                                {{ selectedRoom.status }}
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <span class="label">Bed Quantity:</span>
                            <span>{{ selectedRoom.bed_quantity }}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="label">Price:</span>
                            <span>₱{{ selectedRoom.price }}</span>
                        </div>
                        
                        <div v-if="selectedRoom.room_images" class="room-image">
                            <img :src="selectedRoom.room_images" alt="Room Image">
                        </div>
                        
                        <div v-if="selectedRoom.map_link" class="map-container">
                            <h4>Location</h4>
                            <div v-html="sanitizedMapLink"></div>
                        </div>

                        <!-- Creation Information -->
                        <div class="detail-section">
                            <h4>Creation Information</h4>
                            <div class="info-row">
                                <span class="label">Created By:</span>
                                <span class="value">
                                    {{ selectedRoom.created_by_name || 'System' }}
                                    <span v-if="selectedRoom.created_by_type" class="user-type">
                                        ({{ selectedRoom.created_by_type }})
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Created At:</span>
                                <span class="value">{{ formatDate(selectedRoom.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Update Information -->
                        <div class="detail-section">
                            <h4>Last Update Information</h4>
                            <div class="info-row">
                                <span class="label">Updated By:</span>
                                <span class="value">
                                    {{ selectedRoom.updated_by_name || 'System' }}
                                    <span v-if="selectedRoom.updated_by_type" class="user-type">
                                        ({{ selectedRoom.updated_by_type }})
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Updated At:</span>
                                <span class="value">{{ formatDate(selectedRoom.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

import defaultProfilePic from "@/assets/images/Profile.png";
import defaultRoomImage from "@/assets/images/default-room.png";
import { emitter } from '@/eventBus'

export default {
    name: "LandlordRooms",

    data() {
        return {
            landlordName: localStorage.getItem("userName") || "Landlord",

            landlordProfile: {
                profile_picture: localStorage.getItem("profilePicture"),

                user_id: localStorage.getItem("userId"),

                email: localStorage.getItem("userEmail"),

                phone_number: localStorage.getItem("userPhone"),

                gender: localStorage.getItem("userGender"),

                birthdate: localStorage.getItem("userBirthdate"),
            },

            defaultProfilePic,

            rooms: [],

            loading: true,

            error: null,

            showImageModal: false,

            selectedImage: null,

            selectedRoom: null,

            defaultRoomImage,

            showRoomDetailsModal: false,
        };
    },

    computed: {
        sanitizedMapLink() {
            if (!this.selectedRoom?.map_link) return '';
            
            try {
                if (this.selectedRoom.map_link.includes('<iframe')) {
                    const srcMatch = this.selectedRoom.map_link.match(/src="([^"]+)"/);
                    if (srcMatch && srcMatch[1]) {
                        return `<iframe 
                            src="${srcMatch[1]}" 
                            width="100%" 
                            height="300" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>`;
                    }
                }
                return `<iframe 
                    src="${this.selectedRoom.map_link}" 
                    width="100%" 
                    height="300" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>`;
            } catch (error) {
                console.error('Error processing map URL:', error);
                return '';
            }
        }
    },

    methods: {
        getProfilePicture(profilePicture) {
            if (profilePicture && profilePicture.trim() !== "") {
                return profilePicture;
            }

            return this.defaultProfilePic;
        },

        handleImageError(e) {
            e.target.src = this.defaultProfilePic;
        },

        handleImageLoad() {
            console.log("Image loaded successfully");
        },

        async fetchLandlordProfile() {
            try {
                const userId = this.landlordProfile.user_id;

                const response = await axios.get(
                    `/landlord/fetch-profile/${userId}`
                );

                if (response.data.status === "success") {
                    const userData = response.data.data;

                    this.landlordName = userData.name;

                    this.landlordProfile.profile_picture =
                        userData.profile_picture;

                    localStorage.setItem("userName", userData.name);

                    if (userData.profile_picture) {
                        localStorage.setItem(
                            "profilePicture",
                            userData.profile_picture
                        );
                    }
                }
            } catch (error) {
                console.error("Error fetching landlord profile:", error);
            }
        },

        async fetchRooms() {
            try {
                this.loading = true;
                this.error = null;
                
                const userId = localStorage.getItem('userId');
                const response = await axios.get('/landlord/get-rooms', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-User-Id': userId
                    }
                });

                console.log('Raw room response:', response.data);
                
                if (response.data.status === 'success') {
                    this.rooms = response.data.data.map(room => ({
                        ...room,
                        room_images: room.room_images || null,
                        creator: room.creator || null // Handle potentially missing creator
                    }));
                    console.log('Processed rooms:', this.rooms);
                } else {
                    throw new Error(response.data.message || 'Failed to fetch rooms');
                }
            } catch (error) {
                console.error('Error fetching rooms:', error);
                if (error.response) {
                    console.error('Error response:', error.response.data);
                    console.error('Error status:', error.response.status);
                }
                
                if (error.response?.status === 403) {
                    alert('You are not authorized to view these rooms');
                    this.$router.push('/landlord/dashboard');
                } else {
                    this.error = error.response?.data?.message || 'Failed to load rooms';
                }
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return 'N/A';
            try {
                return new Date(date).toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'N/A';
            }
        },

        getRoomImage(imagePath) {
            if (imagePath) {
                return `/storage/${imagePath}`;
            }
            return this.defaultRoomImage;
        },

        handleRoomImageError(e) {
            e.target.src = this.defaultRoomImage;
        },

        showAddRoomForm() {
            this.$router.push("/landlord/add-room");
        },

        editRoom(room) {
            this.$router.push(`/landlord/rooms/${room.room_id}/edit`);
        },

        async viewRoomDetails(roomId) {
            try {
                const userId = localStorage.getItem('userId');
                if (!userId) {
                    alert('Please log in to view room details');
                    this.$router.push('/landlord/login');
                    return;
                }

                const response = await axios.get(`/landlord/get-room/${roomId}`, {
                    headers: {
                        'X-User-Id': userId,
                        'Accept': 'application/json'
                    }
                });

                console.log('Full API Response:', response.data);

                if (response.data.status === 'success') {
                    console.log('Room details before setting:', response.data.data);
                    this.selectedRoom = response.data.data;
                    console.log('Room details after setting:', this.selectedRoom);
                    this.showRoomDetailsModal = true;
                    document.body.style.overflow = 'hidden';
                } else {
                    throw new Error(response.data.message || 'Failed to fetch room details');
                }
            } catch (error) {
                console.error('Error fetching room details:', error);
                
                if (error.response?.status === 403) {
                    alert('You are not authorized to view this room');
                    return;
                }
                if (error.response?.status === 401) {
                    alert('Please log in again');
                    this.$router.push('/landlord/login');
                    return;
                }
                
                alert('Failed to load room details: ' + (error.response?.data?.message || error.message));
            }
        },

        openImageModal(room) {
            this.selectedRoom = room;
            this.selectedImage = this.getRoomImage(room.room_images);
            this.showImageModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeImageModal() {
            this.showImageModal = false;
            this.selectedImage = null;
            this.selectedRoom = null;
            document.body.style.overflow = 'auto';
        },

        navigateMenu(event) {
            const route = event.target.value;

            if (route === "/landlord/login") {
                this.logout();
            } else if (route !== "#") {
                this.$router.push(route);
            }
        },

        async logout() {
            try {
                await axios.post("/landlord/logout");

                localStorage.removeItem("token");

                localStorage.removeItem("userName");

                localStorage.removeItem("userId");

                localStorage.removeItem("userType");

                localStorage.removeItem("profilePicture");

                localStorage.removeItem("userEmail");

                localStorage.removeItem("userPhone");

                localStorage.removeItem("userGender");

                localStorage.removeItem("userBirthdate");

                delete axios.defaults.headers.common["Authorization"];

                this.$router.push("/landlord/login");
            } catch (error) {
                console.error("Logout error:", error);

                this.$router.push("/landlord/login");
            }
        },

        closeRoomDetails() {
            this.showRoomDetailsModal = false;
            this.selectedRoom = null;
            document.body.style.overflow = 'auto';
        },

        formatPrice(price) {
            if (!price || isNaN(price)) {
                return '0.00';
            }
            return Number(price).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        async confirmDelete(room) {
            if (confirm(`Are you sure you want to delete "${room.room_name}"? This action cannot be undone.`)) {
                try {
                    const userId = localStorage.getItem('userId');
                    const response = await axios.delete(`/landlord/delete-room/${room.room_id}`, {
                        headers: {
                            'X-User-Id': userId
                        }
                    });
                    
                    if (response.data.status === 'success') {
                        alert('Room deleted successfully');
                        // Remove from local array
                        this.rooms = this.rooms.filter(r => r.room_id !== room.room_id);
                    }
                } catch (error) {
                    console.error('Error deleting room:', error);
                    if (error.response?.status === 403) {
                        alert('You are not authorized to delete this room');
                    } else {
                        alert(error.response?.data?.message || 'Failed to delete room');
                    }
                }
            }
        },

        handleRoomStatusUpdate(updatedRoomIds) {
            // Update status of affected rooms
            this.rooms = this.rooms.map(room => {
                if (updatedRoomIds.includes(room.room_id)) {
                    return {
                        ...room,
                        status: 'available'
                    };
                }
                return room;
            });
        }
    },

    created() {
        console.log("Component created");
        this.fetchLandlordProfile();
        this.fetchRooms();
        
        // Listen for room status updates using emitter
        emitter.on('rooms-status-updated', this.handleRoomStatusUpdate);
    },

    beforeUnmount() {
        // Clean up event listener
        emitter.off('rooms-status-updated', this.handleRoomStatusUpdate);
    },
};
</script>

<style scoped>
@import "@/assets/css/landlord_panel.css";

/* Fix viewport scrollbars */
.landlord-panel {
  width: 100%;
  height: 100vh;
  overflow: hidden;
  position: fixed;
  top: 0;
  left: 0;
}

.overall {
  padding: 2rem;
  width: 100%;
  height: calc(100vh - 70px); /* Subtract navbar height */
  margin: 0 auto;
  transform: translate(0, 70px);
  overflow-y: auto; /* Allow vertical scroll only in content area */
  overflow-x: hidden;
}

.rooms-container {
  width: 100%;
  padding: 0;
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
  gap: 2rem;
  padding: 2rem;
}

.room-card {
  width: 100%;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
  display: flex;
  flex-direction: column;
  min-height: 600px;
  position: relative;
  margin: 0; /* Remove margin to prevent grid issues */
}

.room-card:hover {
  transform: translateY(-5px);
}

.room-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.room-image:hover {
  transform: scale(1.05);
}

.room-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px 12px 0 0;
}

.room-details {
  padding: 1.5rem;
  padding-bottom: calc(1.5rem + 72px); /* Add space for fixed buttons */
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.room-name {
  color: #794646;

  font-size: 1.25rem;

  font-weight: 600;

  margin-bottom: 1rem;
}

.room-info {
  flex-grow: 1;
  margin-bottom: 1rem;
  overflow-y: auto;
}

.info-row {
  display: flex;

  justify-content: space-between;

  margin-bottom: 0.5rem;

  padding: 0.5rem;

  background: #f8f9fa;

  border-radius: 4px;
}

.info-label {
  color: #666;

  font-weight: 500;
}

.info-value {
  font-weight: 600;
}

.info-value.available {
  color: #10b981;
}

.info-value.occupied {
  color: #ef4444;
}

.info-value.maintenance {
  color: #f59e0b;
}

.room-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  padding: 0.75rem;
  border-top: 1px solid #eee;
  background: white;
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  height: 36px;
}

.action-btn .action-icon {
  width: 14px;
  height: 14px;
  object-fit: contain;
}

.action-btn.edit {
  background: #f3f4f6;
  color: #374151;
}

.action-btn.view {
  background: #794646;
  color: white;
}

.action-btn.delete {
  background: #fee2e2;
  color: #dc2626;
}

.action-btn:hover {
  transform: translateY(-2px);
}

.action-btn.edit:hover {
  background: #e5e7eb;
}

.action-btn.view:hover {
  background: #693c3c;
}

.action-btn.delete:hover {
  background: #fecaca;
}

.loading,
.error,
.no-rooms {
  text-align: center;

  padding: 3rem;

  background: white;

  border-radius: 12px;

  margin: 2rem;

  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.loading {
  color: #666;
}

.error {
  color: #ef4444;
}

.no-rooms {
  color: #666;
}

.no-rooms p:first-child {
  font-size: 1.25rem;

  color: #794646;

  font-weight: 600;

  margin-bottom: 0.5rem;
}

.image-modal,
.map-modal {
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

  max-height: 90%;

  background: white;

  padding: 1.5rem;

  border-radius: 12px;
}

.zoomed-image {
  max-width: 100%;

  max-height: 90vh;

  object-fit: contain;
}

.close-button {
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

.close-button:hover {
  color: #ddd;
}

@media (max-width: 768px) {
  .rooms-grid {
    grid-template-columns: 1fr;
  }

  .modal-content {
    width: 95%;

    padding: 1rem;
  }

  .close-button {
    top: -30px;

    right: -10px;
  }

  .header-section {
    padding: 0 1rem;
  }

  .landlord-profile-name {
    display: none;
  }
}

/* Profile section styles */

.profile-section {
  margin-left: auto !important; /* Force margin-left: auto */

  margin-right: 1rem !important; /* Reduced margin to accommodate menu */

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
  margin-left: 0 !important; /* Remove auto margin */

  margin-right: 2rem; /* Add right margin */
}

@media (max-width: 768px) {
  .landlord-profile-name {
    display: none;
  }
}

.details-modal {
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

.details-modal-content {
  background: white;

  padding: 2rem;

  border-radius: 12px;

  width: 90%;

  max-width: 800px;

  max-height: 90vh;

  position: relative;

  overflow-y: auto;
}

.room-details-container h2 {
  color: #794646;

  margin-bottom: 1.5rem;
}

.details-image-container {
  margin-bottom: 1.5rem;

  border-radius: 8px;

  overflow: hidden;
}

.details-image {
  width: 100%;

  height: 300px;

  object-fit: cover;
}

.details-info {
  display: flex;

  flex-direction: column;

  gap: 1rem;
}

.info-group {
  display: flex;

  flex-direction: column;

  gap: 0.5rem;
}

.info-group label {
  color: #666;

  font-weight: 600;
}

.info-group span {
  color: #333;

  font-size: 1.1rem;
}

.info-group span.available {
  color: #10b981;
}

.info-group span.occupied {
  color: #ef4444;
}

.info-group span.maintenance {
  color: #f59e0b;
}

.map-container {
  width: 100%;

  height: 300px;

  border-radius: 8px;

  overflow: hidden;
}

.close-details-btn {
  position: absolute;

  top: 1rem;

  right: 1rem;

  background: none;

  border: none;

  font-size: 24px;

  color: #666;

  cursor: pointer;

  padding: 0.5rem;
}

.close-details-btn:hover {
  color: #333;
}

@media (max-width: 768px) {
  .details-modal-content {
    padding: 1rem;

    width: 95%;
  }

  .details-image {
    height: 200px;
  }

  .map-container {
    height: 200px;
  }
}

.user-type {
  font-size: 0.9em;
  color: #794646;
  font-style: italic;
  margin-left: 0.5rem;
}

.info-row {
  display: flex;

  justify-content: space-between;

  padding: 0.8rem;

  background: white;

  border-radius: 4px;

  margin-bottom: 0.5rem;
}

.info-label {
  font-weight: 600;

  color: #794646;

  width: 120px;

  flex-shrink: 0;
}

.info-value {
  color: #666;

  flex-grow: 1;
}

.rooms-grid {
  display: flex;

  flex-wrap: wrap;

  gap: 2rem;

  padding: 1rem;
}

.room-card {
  flex: 0 0 300px;

  background: white;

  border-radius: 12px;

  overflow: hidden;

  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

  transition: transform 0.2s ease;

  margin-bottom: 1rem;
}

.room-card:hover {
  transform: translateY(-5px);
}

.rooms-container {
  width: 100%;

  max-width: 100%;

  overflow-x: auto;

  padding: 1rem;
}

.overall {
  width: 100%;

  max-width: 100%;

  padding: 2rem;
}

@media (max-width: 1024px) {
  .room-card {
    flex: 0 0 calc(50% - 1rem);
  }
}

@media (max-width: 768px) {
  .room-card {
    flex: 0 0 100%;
    margin: 1rem 0;
  }
}

.header-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 0 2rem;
}

.helper {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.add-room-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem; /* Reduced padding */
  background: black;
  color: white;
  border: none;
  border-radius: 6px; /* Slightly reduced border radius */
  font-weight: 600;
  font-size: 14px; /* Reduced font size */
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-room-btn:hover {
  background: #333;
  transform: translateY(-2px);
}

.white-icon {
  filter: brightness(0) invert(1);
  width: 16px; /* Reduced icon size */
  height: 16px; /* Reduced icon size */
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
}

.image-container {
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-image {
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
}

.close-button {
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

@media (max-width: 1400px) {
  .room-card {
    flex: 0 0 380px;
  }
}

@media (max-width: 768px) {
  .room-card {
    flex: 0 0 100%;
    min-height: 550px;
  }

  .room-actions {
    padding: 0.75rem;
  }

  .action-btn {
    height: 36px;
    font-size: 0.8rem;
    padding: 0.5rem;
  }
}

.detail-section {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.detail-section h4 {
  color: #794646;
  margin-bottom: 1rem;
  font-size: 1.1rem;
  font-weight: 600;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  background: white;
  border-radius: 4px;
}

.info-row .label {
  font-weight: 600;
  color: #666;
  min-width: 120px;
}

.info-row .value {
  flex: 1;
  text-align: right;
}

.user-type {
  font-size: 0.9em;
  color: #794646;
  font-style: italic;
  margin-left: 0.5rem;
}

.modal-body {
  max-height: 70vh;
  overflow-y: auto;
  padding-right: 1rem;
}

.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Add these modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 1.5rem;
  position: relative;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.room-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge.available {
  background: #dcfce7;
  color: #166534;
}

.status-badge.occupied {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.maintenance {
  background: #fef3c7;
  color: #92400e;
}

.room-image {
  width: 100%;
  max-height: 300px;
  overflow: hidden;
  border-radius: 8px;
}

.room-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.map-container {
  margin-top: 1rem;
}

.map-container iframe {
  width: 100%;
  height: 300px;
  border: none;
  border-radius: 8px;
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
