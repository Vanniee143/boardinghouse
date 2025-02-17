<template>
  <div class="payment-settings">
    <div class="back-section">
      <button class="back-btn" @click="goBack">
        <span class="arrow-left"></span>
        Back
      </button>
    </div>

    <h2>Payment QR Code Settings</h2>
    
    <div class="payment-container">
      <!-- Left side - QR Form -->
      <div class="qr-form">
        <form @submit.prevent="saveQRCode">
          <div class="form-group">
            <label>Payment Type</label>
            <select v-model="qrData.payment_type" required>
              <option value="" disabled selected>Select Payment Type</option>
              <option value="gcash">GCash</option>
              <option value="paymaya">PayMaya</option>
            </select>
          </div>

          <div class="form-group">
            <label>Account Name</label>
            <input 
              type="text" 
              v-model="qrData.account_name"
              required
              placeholder="Enter account name"
            >
          </div>

          <div class="form-group">
            <label>Boarding House</label>
            <select v-model="qrData.boarding_house_id" required>
              <option value="" disabled>Select Boarding House</option>
              <option 
                v-for="house in boardingHouses" 
                :key="house.boarding_house_id"
                :value="house.boarding_house_id"
              >
                {{ house.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>QR Code Image</label>
            <input 
              type="file" 
              @change="handleQRImageUpload"
              accept="image/*"
              required
            >
            <div v-if="qrPreview" class="qr-preview">
              <img :src="qrPreview" alt="QR Code preview">
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn">Save QR Code</button>
          </div>
        </form>
      </div>

      <!-- Right side - Existing QR Codes -->
      <div class="qr-list">
        <h3>My QR Codes</h3>
        <div v-if="hasQRCodes" class="qr-grid">
          <div v-for="qr in qrCodes" :key="qr.qr_id" class="qr-item">
            <div class="qr-image-container" @click="openImagePreview(getQRImageUrl(qr.qr_image))">
              <img :src="getQRImageUrl(qr.qr_image)" alt="QR Code">
              <div class="image-overlay">
                <span>Click to view</span>
              </div>
            </div>
            <div class="qr-details">
              <p><strong>Type:</strong> {{ qr.payment_type }}</p>
              <p><strong>Account:</strong> {{ qr.account_name }}</p>
              <p><strong>Boarding House:</strong> {{ qr.boarding_house }}</p>
            </div>
            <button @click="deleteQRCode(qr.qr_id)" class="delete-btn">Delete</button>
          </div>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-qrcode"></i>
          </div>
          <h4>No QR Codes Available</h4>
          <p>Add your first QR code using the form on the left.</p>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div v-if="showFullImage" class="image-modal" @click="closeImagePreview">
      <div class="modal-content" @click.stop>
        <button class="close-button" @click="closeImagePreview">&times;</button>
        <img :src="selectedImage" alt="QR Code" class="full-image">
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'LandlordPaymentSettings',
  data() {
    return {
      qrData: {
        payment_type: '',
        account_name: '',
        boarding_house_id: '',
        qr_image: null,
        created_by: localStorage.getItem('userId')
      },
      qrPreview: null,
      qrCodes: [],
      boardingHouses: [],
      showFullImage: false,
      selectedImage: null
    }
  },
  methods: {
    handleQRImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          alert('File size should not exceed 2MB');
          event.target.value = '';
          return;
        }
        this.qrData.qr_image = file;
        this.qrPreview = URL.createObjectURL(file);
      }
    },

    async saveQRCode() {
      try {
        if (!this.qrData.boarding_house_id) {
          alert('Please select a boarding house');
          return;
        }

        // Verify the boarding house belongs to the landlord
        const boardingHouse = this.boardingHouses.find(
          h => h.boarding_house_id === this.qrData.boarding_house_id
        );
        
        if (!boardingHouse) {
          alert('Invalid boarding house selection');
          return;
        }

        const userId = localStorage.getItem('userId');
        const formData = new FormData();
        formData.append('payment_type', this.qrData.payment_type);
        formData.append('account_name', this.qrData.account_name);
        formData.append('boarding_house_id', this.qrData.boarding_house_id);
        formData.append('qr_image', this.qrData.qr_image);
        formData.append('created_by', userId);

        const response = await axios.post('/landlord/payment-qr-codes', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-User-Id': userId
          }
        });

        if (response.data.status === 'success') {
          alert('QR code saved successfully');
          this.resetForm();
          await this.fetchQRCodes();
        }
      } catch (error) {
        console.error('Error saving QR code:', error);
        if (error.response?.status === 403) {
          alert('Unauthorized access');
          return;
        }
        alert(error.response?.data?.message || 'Failed to save QR code');
      }
    },

    async fetchQRCodes() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get('/landlord/payment-qr-codes', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          this.qrCodes = response.data.data;
        }
      } catch (error) {
        console.error('Error fetching QR codes:', error);
        if (error.response?.status === 403) {
          alert('Unauthorized access');
          this.$router.push('/landlord/dashboard');
        }
      }
    },

    async fetchBoardingHouses() {
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.get('/landlord/get-boarding-houses', {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          this.boardingHouses = response.data.data.map(house => ({
            boarding_house_id: house.id,
            name: house.name
          }));
        }
      } catch (error) {
        console.error('Error fetching boarding houses:', error);
        if (error.response?.status === 403) {
          alert('Unauthorized access');
          this.$router.push('/landlord/dashboard');
        }
      }
    },

    async deleteQRCode(id) {
      if (!confirm('Are you sure you want to delete this QR code?')) return;
      
      try {
        const userId = localStorage.getItem('userId');
        const response = await axios.delete(`/landlord/payment-qr-codes/${id}`, {
          headers: {
            'X-User-Id': userId
          }
        });
        
        if (response.data.status === 'success') {
          alert('QR code deleted successfully');
          await this.fetchQRCodes();
        }
      } catch (error) {
        console.error('Error deleting QR code:', error);
        if (error.response?.status === 403) {
          alert('Unauthorized access');
        } else {
          alert('Failed to delete QR code');
        }
      }
    },

    getQRImageUrl(path) {
      return path ? `/storage/${path}` : '';
    },

    resetForm() {
      this.qrData = {
        payment_type: '',
        account_name: '',
        boarding_house_id: '',
        qr_image: null,
        created_by: localStorage.getItem('userId')
      };
      this.qrPreview = null;
    },

    openImagePreview(imageUrl) {
      this.selectedImage = imageUrl;
      this.showFullImage = true;
      document.body.style.overflow = 'hidden';
    },

    closeImagePreview() {
      this.showFullImage = false;
      this.selectedImage = null;
      document.body.style.overflow = 'auto';
    },

    goBack() {
      this.$router.push('/landlord/dashboard');
    }
  },
  computed: {
    hasQRCodes() {
      return this.qrCodes.length > 0;
    }
  },
  async created() {
    await this.fetchBoardingHouses();
    await this.fetchQRCodes();
  }
}
</script>

<style scoped>
.payment-settings {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  margin-top: 50px;
  min-height: 100vh;
  background: #f8f9fa;
  position: relative;
}

.payment-settings::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  z-index: -1;
}

h2 {
  color: #794646;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 2rem;
}

h3 {
  color: #794646;
  font-size: 22px;
  font-weight: 600;
  margin: 2rem 0 1rem;
}

.payment-container {
  display: flex;
  gap: 2rem;
  align-items: flex-start;
}

.qr-form {
  flex: 0 0 400px;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  position: sticky;
  top: 2rem;
}

.qr-list {
  flex: 1;
  min-width: 0;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  max-height: 800px;
  overflow-y: auto;
}

.qr-list::-webkit-scrollbar {
  width: 8px;
}

.qr-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.qr-list::-webkit-scrollbar-thumb {
  background: #794646;
  border-radius: 4px;
}

.qr-list::-webkit-scrollbar-thumb:hover {
  background: #693c3c;
}

.qr-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  min-height: min-content;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 16px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  color: #333;
  background-color: white;
}

.form-group select option[value=""] {
  color: #666;
}

.form-group select option {
  color: #333;
  padding: 0.5rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.form-group input[type="file"] {
  padding: 0.5rem;
  border: 2px dashed #ddd;
  background: #f8f8f8;
  cursor: pointer;
}

.form-group input[type="file"]:hover {
  border-color: #794646;
}

.qr-preview {
  margin-top: 1rem;
  max-width: 200px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qr-preview img {
  width: 100%;
  height: auto;
  display: block;
}

.form-actions {
  margin-top: 2rem;
  display: flex;
  justify-content: flex-end;
}

.save-btn {
  background: #794646;
  color: white;
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s ease;
}

.save-btn:hover {
  background: #693c3c;
  transform: translateY(-1px);
}

.qr-item {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  margin: 0;
}

.qr-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.qr-image-container {
  position: relative;
  padding: 1.5rem;
  background: #f8f9fa;
  border-bottom: 1px solid #eee;
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.qr-item img {
  width: 80%;
  height: 80%;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.qr-details {
  padding: 1.5rem;
  flex-grow: 1;
  background: white;
}

.qr-details p {
  margin: 0.75rem 0;
  color: #666;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
}

.qr-details p strong {
  color: #794646;
  font-weight: 600;
  min-width: 100px;
  margin-right: 0.5rem;
}

.delete-btn {
  width: 100%;
  padding: 1rem;
  background: #dc3545;
  color: white;
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transform: translate(-10px, 0%);
}

.delete-btn:hover {
  background: #c82333;
}

.delete-btn::before {
  content: '×';
  font-size: 1.2em;
  line-height: 1;
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
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
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

.full-image {
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
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
  background: rgba(0, 0, 0, 0.7);
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  transform: translateY(10px);
  transition: transform 0.3s ease;
}

.qr-image-container:hover .image-overlay {
  opacity: 1;
}

.qr-image-container:hover .image-overlay span {
  transform: translateY(0);
}

.empty-state {
  background: white;
  border-radius: 12px;
  padding: 3rem 2rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-icon {
  font-size: 48px;
  color: #794646;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state h4 {
  color: #794646;
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #666;
  font-size: 16px;
  max-width: 300px;
  margin: 0 auto;
}

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

@media (max-width: 1024px) {
  .payment-container {
    flex-direction: column;
  }

  .qr-form {
    position: static;
    width: 100%;
    max-width: none;
    margin-bottom: 2rem;
  }

  .qr-list {
    width: 100%;
    padding: 1.5rem;
    max-height: 600px;
  }

  .qr-grid {
    padding: 0.75rem;
    gap: 1.5rem;
  }
}

@media (max-width: 768px) {
  .payment-settings {
    padding: 1rem;
  }

  .qr-list {
    padding: 1rem;
    max-height: 500px;
  }

  .qr-grid {
    padding: 0.5rem;
    gap: 1rem;
  }

  .close-button {
    top: 10px;
    right: 10px;
  }

  .modal-content {
    width: 95vw;
  }

  .save-btn {
    width: 100%;
  }
}
</style> 