<template>
    <div class="admin-accounts">
        <!-- Navbar -->
        <div class="navbars">
            <router-link to="/admin">
                <img src="@/assets/images/image.png" alt="logo" class="logos" />
            </router-link>

            <router-link to="/admin" class="nl">
                <h5>SBH BOOKING</h5>
            </router-link>

            <div class="profile-sections">
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
                <img
                    src="@/assets/images/Menu.png"
                    alt="menu"
                    class="menuimgg"
                    id="menuImage"
                />
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
                <h4 class="helper">EDIT ACCOUNT</h4>
            </div>

            <div v-if="loading" class="loading">Loading account details...</div>

            <div v-else-if="error" class="error">{{ error }}</div>

            <div v-else class="edit-form-container">
                <form @submit.prevent="handleSubmit" class="edit-form">
                    <div class="profile-section">
                        <img 
                            :src="getDisplayImage()"
                            alt="Profile" 
                            class="profile-preview"
                            @error="handleImageError"
                        />
                        <div class="profile-upload">
                            <label for="profile" class="upload-label">Change Profile Picture</label>
                            <input 
                                type="file" 
                                id="profile" 
                                accept="image/*"
                                @change="handleImageChange"
                                class="file-input"
                            />
                        </div>
                    </div>

                    <div class="user-type-display">
                        <span class="type-label">Account Type:</span>
                        <span class="type-value">{{ formData.userType }}</span>
                    </div>

                    <div class="form-group">
                        <label>User ID:</label>
                        <input type="text" :value="formData.user_id" disabled class="disabled-input" />
                    </div>

                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" v-model="formData.name" required />
                    </div>

                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" v-model="formData.email" required />
                    </div>

                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input 
                            type="tel" 
                            v-model="formData.phone_number" 
                            placeholder="Enter phone number"
                        />
                    </div>

                    <div class="form-group">
                        <label>Gender:</label>
                        <select v-model="formData.gender">
                            <option value="" disabled selected>Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Birthdate:</label>
                        <input 
                            type="date" 
                            v-model="formData.birthdate"
                            :max="getCurrentDate()"
                        />
                    </div>

                    <div class="form-group">
                        <label>User Type:</label>
                        <select v-model="formData.userType" required>
                            <option value="user">User</option>
                            <option value="landlord">Landlord</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="info-group">
                        <div class="info-row">
                            <span class="info-label">Created By:</span>
                            <span class="info-value">{{ formData.created_by_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Updated By:</span>
                            <span class="info-value">{{ formData.updated_by_name }}</span>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <button type="button" class="cancel-btn" @click="goBack">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import defaultProfilePic from '@/assets/images/Profile.png'

export default {
    name: 'EditAccount',
    data() {
        return {
            adminName: localStorage.getItem('userName') || 'Admin',
            adminProfile: {
                profile_picture: localStorage.getItem('profilePicture'),
                user_id: localStorage.getItem('userId')
            },
            loading: true,
            error: null,
            formData: {
                name: '',
                email: '',
                phone_number: '',
                gender: null,
                birthdate: '',
                profile_picture: null
            },
            previewImage: null,
            originalData: null,
            defaultProfilePic,
        };
    },
    methods: {
        navigateMenu(event) {
            const route = event.target.value;
            if (route === '/admin/admin_login') {
                this.logout();
            } else if (route !== '#') {
                this.$router.push(route);
            }
        },

        async logout() {
            try {
                // Call the logout endpoint
                await axios.post('/admin/logout');
                
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
                this.$router.push('/admin/admin_login');
            } catch (error) {
                console.error('Logout error:', error);
                // Still redirect even if the server request fails
                this.$router.push('/admin/admin_login');
            }
        },

        async fetchAccountDetails() {
            try {
                const response = await axios.get(`/admin/fetch-accounts`);
                if (response.data.status === 'success') {
                    const accounts = response.data.data;
                    let account = null;
                    
                    for (const userType in accounts) {
                        account = accounts[userType].find(
                            acc => acc.user_id.toString() === this.$route.params.id
                        );
                        if (account) break;
                    }

                    if (account) {
                        this.formData = { 
                            ...account,
                            gender: account.gender || '',
                            profile_picture: account.profile_picture || this.defaultProfilePic
                        };
                        this.originalData = { ...account };
                        
                        // Format birthdate if exists
                        if (this.formData.birthdate) {
                            this.formData.birthdate = this.formData.birthdate.split('T')[0];
                        }

                        // Set creator/updater info
                        this.formData.created_by_name = account.created_by_name || 'System';
                        this.formData.created_by_type = account.created_by_type;
                        this.formData.updated_by_name = account.updated_by_name || 'System';
                        this.formData.updated_by_type = account.updated_by_type;
                    } else {
                        throw new Error('Account not found');
                    }
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                this.error = 'Failed to load account details';
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        handleImageChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewImage = URL.createObjectURL(file);
                this.formData.profile_picture = file;
                console.log('Profile picture selected:', file.name);
            }
        },

        async handleSubmit() {
            try {
                const formData = new FormData();
                
                for (const key in this.formData) {
                    if (key !== 'profile_picture' && this.formData[key] !== null) {
                        formData.append(key, this.formData[key]);
                    }
                }

                if (this.formData.profile_picture instanceof File) {
                    formData.append('profile_picture', this.formData.profile_picture);
                }

                const adminId = localStorage.getItem('userId');
                formData.append('updated_by', adminId);

                const adminType = localStorage.getItem('userType');
                formData.append('updater_type', adminType);

                formData.append('_method', 'PUT');

                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                const response = await axios.post(
                    `/admin/update-account/${this.$route.params.id}`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        }
                    }
                );

                if (response.data.status === 'success') {
                    this.formData.updated_by_name = localStorage.getItem('userName');
                    this.formData.updated_by_type = adminType;
                    this.formData.updated_at = new Date().toISOString();

                    alert('Account updated successfully');
                    this.$router.push('/admin/accounts');
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                console.error('Update error:', error);
                alert('Failed to update account: ' + (error.response?.data?.message || error.message));
            }
        },

        goBack() {
            this.$router.push('/admin/accounts');
        },

        getProfilePicture(profilePicture) {
            if (profilePicture && profilePicture.trim() !== '') {
                return profilePicture;
            }
            return this.defaultProfilePic;
        },

        handleImageError(e) {
            console.log('Image load error occurred');
            e.target.src = this.defaultProfilePic;
        },

        handleImageLoad() {
            console.log('Image loaded successfully');
        },

        async fetchAdminProfile() {
            try {
                const userId = this.adminProfile.user_id;
                const response = await axios.get(`/admin/fetch-account/${userId}`);
                
                if (response.data.status === 'success') {
                    const userData = response.data.data;
                    this.adminName = userData.name;
                    this.adminProfile.profile_picture = userData.profile_picture;
                    
                    // Update localStorage
                    localStorage.setItem('userName', userData.name);
                    if (userData.profile_picture) {
                        localStorage.setItem('profilePicture', userData.profile_picture);
                    }
                }
            } catch (error) {
                console.error('Error fetching admin profile:', error);
            }
        },

        getDisplayImage() {
            if (this.previewImage) {
                return this.previewImage;
            }
            if (this.formData.profile_picture && this.formData.profile_picture.trim() !== '') {
                return this.formData.profile_picture;
            }
            return this.defaultProfilePic;
        },

        getCurrentDate() {
            return new Date().toISOString().split('T')[0];
        },
    },
    async created() {
        await this.fetchAdminProfile();
        this.fetchAccountDetails();
    }
};
</script>

<style scoped>
@import '@/assets/css/admin_panel.css';

.overall {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  transform: translate(0, 70px);
}

.home {
  margin-bottom: 2rem;
}

.helper {
  color: #794646;
  font-size: 24px;
  font-weight: 700;
  margin: 0;
}

.edit-form-container {
  background: #fff;
  border-radius: 8px;
  padding: 2.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  margin: 0 auto;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Update navbar profile styles */
.profile-sections {
    margin-left: auto;  /* Push to the right */
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
}

.admin-profile-name {
    color: #794646;
    font-weight: 600;
    font-size: 0.9rem;
}  

/* Profile Section */
.profile-section {
  width: 90%;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
  padding: 2rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.profile-preview {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 1.5rem;
  border: 4px solid #794646;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  background-color: #f8f9fa;
}

.profile-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.upload-label {
  background: #794646;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 600;
}

.upload-label:hover {
  background: #693c3c;
  transform: translateY(-1px);
}

.file-input {
  display: none;
}

/* Form Groups */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  color: #794646;
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.form-group input,
.form-group select {
  width: 96%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #794646;
  box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.form-group select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1em;
  padding-right: 2.5rem;
}

.disabled-input {
  background-color: #f8f9fa;
  cursor: not-allowed;
  opacity: 0.7;
}

/* User Type Display */
.user-type-display {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 95.5%;
}

.type-label {
  color: #794646;
  font-weight: 600;
  font-size: 1rem;
}

.type-value {
  font-size: 1rem;
  color: #794646;
  text-transform: capitalize;
  font-weight: 500;
}

/* Button Group */
.button-group {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
}

.save-btn,
.cancel-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.save-btn {
  background: #794646;
  color: white;
  border: none;
}

.cancel-btn {
  background: transparent;
  color: #794646;
  border: 1px solid #794646;
}

.save-btn:hover {
  background: #693c3c;
  transform: translateY(-1px);
}

.cancel-btn:hover {
  background: #f8f9fa;
  transform: translateY(-1px);
}

/* Loading and Error States */
.loading,
.error {
  text-align: center;
  padding: 2rem;
  margin: 2rem auto;
  max-width: 800px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.error {
  color: #dc3545;
  background: #fff5f5;
}

/* Navbar Styles */
.navbars {
  display: flex;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: white;
  padding: 0 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 100;
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
  border: 1px solid #ddd;
}

.admin-profile-name {
  color: #794646;
  font-weight: 600;
  font-size: 0.9rem;
}

.menu-container {
  margin-left: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .overall {
    padding: 1rem;
  }

  .edit-form-container {
    padding: 1.5rem;
  }

  .profile-preview {
    width: 150px;
    height: 150px;
  }

  .button-group {
    flex-direction: column-reverse;
    gap: 0.5rem;
  }

  .save-btn,
  .cancel-btn {
    width: 100%;
  }

  .profile-section {
    margin-right: 1rem;
  }

  .admin-profile-name {
    display: none;
  }
}

.info-group {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.info-row {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-weight: 600;
    color: #794646;
    width: 120px;
    flex-shrink: 0;
}

.info-value {
    color: #666;
}

.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    background-color: white;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    padding-right: 2.5rem;
}

.form-group select:focus {
    outline: none;
    border-color: #794646;
    box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.form-group select option[value=""] {
    color: #999;
}

.form-group select option:not([value=""]) {
    color: #333;
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