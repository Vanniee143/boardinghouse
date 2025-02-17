<template>
    <div class="admin-accounts">
        <div class="navbars">
            <router-link to="/admin">
                <img src="@/assets/images/image.png" alt="logo" class="logos" />
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

                    <option value="/admin/profile">Profile Settings</option>

                    <option value="/admin/admin_login">Logout</option>
                </select>
            </div>
        </div>

        <div class="overall">
            <div class="home">
                <h4 class="helper">ACCOUNTS</h4>
                <router-link to="/admin/add-user" class="add-btn">
                    + Add User
                </router-link>
            </div>

            <div v-if="loading" class="loading">Loading accounts...</div>

            <div v-else-if="error" class="error">
                {{ error }}
            </div>

            <div v-else class="accounts-container">
                <!-- Regular Users Section -->

                <div
                    class="account-section"
                    v-if="accounts.user && accounts.user.length"
                >
                    <h3 class="section-title">User Accounts</h3>

                    <div
                        class="containss"
                        v-for="account in accounts.user"
                        :key="account.user_id"
                    >
                        <div class="account-header">
                            <img
                                :src="getProfilePicture(account.profile_picture)"
                                :alt="account.name"
                                class="account-profile-pic"
                                @error="handleImageError"
                            />

                            <div class="sets">{{ account.name }}</div>
                        </div>

                        <div class="user-info">
                            <div class="info-row">
                                <span class="info-label">User ID:</span>
                                <span class="info-value">{{ account.user_id }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ account.email }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Phone:</span>

                                <span class="info-value">{{
                                    account.phone_number || "Not provided"
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Gender:</span>

                                <span class="info-value">{{
                                    account.gender || "Not specified"
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Birthdate:</span>

                                <span class="info-value">{{
                                    formatDate(account.birthdate)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">User Type:</span>
                                <span class="info-value">{{ account.userType }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Created:</span>

                                <span class="info-value">{{
                                    formatDate(account.created_at)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Created By:</span>
                                <span class="info-value">
                                    {{ account.created_by_name || 'System' }}
                                    <span v-if="account.created_by_type" class="user-type">({{ account.created_by_type }})</span>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated:</span>

                                <span class="info-value">{{
                                    formatDate(account.updated_at)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated By:</span>
                                <span class="info-value">
                                    {{ account.updated_by_name || 'System' }}
                                    <span v-if="account.updated_by_type" class="user-type">({{ account.updated_by_type }})</span>
                                </span>
                            </div>
                        </div>

                        <div class="action-icons">
                            <img
                                src="@/assets/images/Edit.png"
                                alt="Edit"
                                class="Edit"
                                @click="editAccount(account)"
                            />

                            <img
                                src="@/assets/images/Trash.png"
                                alt="Trash"
                                class="Trash"
                                @click="deleteAccount(account.user_id)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Admin Section -->

                <div
                    class="account-section"
                    v-if="accounts.admin && accounts.admin.length"
                >
                    <h3 class="section-title">Admin Accounts</h3>

                    <div
                        class="containss admin-container"
                        v-for="account in accounts.admin"
                        :key="account.user_id"
                    >
                        <div class="account-header">
                            <img
                                :src="getProfilePicture(account.profile_picture)"
                                :alt="account.name"
                                class="account-profile-pic"
                                @error="handleImageError"
                            />

                            <div class="sets">{{ account.name }}</div>
                        </div>

                        <div class="user-info">
                            <div class="info-row">
                                <span class="info-label">User ID:</span>
                                <span class="info-value">{{ account.user_id }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ account.email }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Phone:</span>

                                <span class="info-value">{{
                                    account.phone_number || "Not provided"
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Gender:</span>

                                <span class="info-value">{{
                                    account.gender || "Not specified"
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Birthdate:</span>

                                <span class="info-value">{{
                                    formatDate(account.birthdate)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">User Type:</span>
                                <span class="info-value">{{ account.userType }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Created:</span>

                                <span class="info-value">{{
                                    formatDate(account.created_at)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Created By:</span>
                                <span class="info-value">
                                    {{ account.created_by_name || 'System' }}
                                    <span v-if="account.created_by_type" class="user-type">({{ account.created_by_type }})</span>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated:</span>

                                <span class="info-value">{{
                                    formatDate(account.updated_at)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated By:</span>
                                <span class="info-value">
                                    {{ account.updated_by_name || 'System' }}
                                    <span v-if="account.updated_by_type" class="user-type">({{ account.updated_by_type }})</span>
                                </span>
                            </div>
                        </div>

                        <div class="action-icons">
                            <img
                                src="@/assets/images/Edit.png"
                                alt="Edit"
                                class="Edit"
                                @click="editAccount(account)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Landlord Section -->

                <div
                    class="account-section"
                    v-if="accounts.landlord && accounts.landlord.length"
                >
                    <h3 class="section-title">Landlord Accounts</h3>

                    <div
                        class="containss"
                        v-for="account in accounts.landlord"
                        :key="account.user_id"
                    >
                        <div class="account-header">
                            <img
                                :src="getProfilePicture(account.profile_picture)"
                                :alt="account.name"
                                class="account-profile-pic"
                                @error="handleImageError"
                            />

                            <div class="sets">{{ account.name }}</div>
                        </div>

                        <div class="user-info">
                            <div class="info-row">
                                <span class="info-label">User ID:</span>
                                <span class="info-value">{{ account.user_id }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ account.email }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Phone:</span>

                                <span class="info-value">{{
                                    account.phone_number || "Not provided"
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Gender:</span>

                                <span class="info-value">{{
                                    account.gender || "Not specified"
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Birthdate:</span>

                                <span class="info-value">{{
                                    formatDate(account.birthdate)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">User Type:</span>
                                <span class="info-value">{{ account.userType }}</span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Created:</span>

                                <span class="info-value">{{
                                    formatDate(account.created_at)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Created By:</span>
                                <span class="info-value">
                                    {{ account.created_by_name || 'System' }}
                                    <span v-if="account.created_by_type" class="user-type">({{ account.created_by_type }})</span>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated:</span>

                                <span class="info-value">{{
                                    formatDate(account.updated_at)
                                }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Updated By:</span>
                                <span class="info-value">
                                    {{ account.updated_by_name || 'System' }}
                                    <span v-if="account.updated_by_type" class="user-type">({{ account.updated_by_type }})</span>
                                </span>
                            </div>
                        </div>

                        <div class="action-icons">
                            <img
                                src="@/assets/images/Edit.png"
                                alt="Edit"
                                class="Edit"
                                @click="editAccount(account)"
                            />

                            <img
                                src="@/assets/images/Trash.png"
                                alt="Trash"
                                class="Trash"
                                @click="deleteAccount(account.user_id)"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="
                        (!accounts.user || !accounts.user.length) &&
                        (!accounts.landlord || !accounts.landlord.length) &&
                        (!accounts.admin || !accounts.admin.length)
                    "
                    class="no-accounts"
                >
                    <p>No accounts available</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import defaultProfilePic from '@/assets/images/Profile.png';

export default {
    name: "AdminAccounts",

    data() {
        return {
            adminName: localStorage.getItem("userName") || "Admin",
            adminProfile: {
                profile_picture: localStorage.getItem('profilePicture'),
                user_id: localStorage.getItem('userId'),
                email: localStorage.getItem('userEmail'),
                phone_number: localStorage.getItem('userPhone'),
                gender: localStorage.getItem('userGender'),
                birthdate: localStorage.getItem('userBirthdate')
            },
            accounts: {},
            loading: false,
            error: null,
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

        async deleteAccount(id) {
            try {
                // Get the account to be deleted

                const accountToDelete = Object.values(this.accounts)

                    .flat()

                    .find((account) => account.user_id === id);

                if (!accountToDelete) {
                    throw new Error("Account not found");
                }

                // Check permissions based on userType

                if (accountToDelete.userType === "admin") {
                    alert("Admin accounts cannot be deleted");

                    return;
                }

                if (
                    !confirm(
                        `Are you sure you want to delete this ${accountToDelete.userType} account?`
                    )
                ) {
                    return;
                }

                const response = await axios.delete(
                    `/admin/delete-account/${id}`
                );

                if (response.data.status === "success") {
                    await this.fetchAccounts();

                    alert("Account deleted successfully");
                } else {
                    throw new Error("Failed to delete account");
                }
            } catch (error) {
                console.error("Error deleting account:", error);

                alert(
                    error.message ||
                        "Failed to delete account. Please try again."
                );
            }
        },

        formatDate(date) {
            return new Date(date).toLocaleString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                hour12: true
            });
        },

        async fetchAccounts() {
            this.loading = true;
            this.error = null;
            try {
                console.log("Fetching accounts...");
                const response = await axios.get("/admin/fetch-accounts");
                console.log("Response data:", response.data); // Debug log

                if (response.data.status === "success") {
                    this.accounts = response.data.data;
                    // Debug log for profile pictures
                    Object.values(this.accounts).flat().forEach(account => {
                        console.log(`${account.name}'s profile:`, account.profile_picture);
                    });
                } else {
                    throw new Error(response.data.message || "Failed to fetch accounts");
                }
            } catch (error) {
                console.error("Error fetching accounts:", error);
                this.error = error.response?.data?.message || "Failed to load accounts";
                this.accounts = {};
            } finally {
                this.loading = false;
            }
        },

        editAccount(account) {
            // Store the account data in localStorage for editing

            localStorage.setItem("editingAccount", JSON.stringify(account));

            // Navigate to edit page

            this.$router.push({
                path: `/admin/edit-account/${account.user_id}`,

                query: { type: account.userType },
            });
        },

        getProfilePicture(profilePicture) {
            console.log('Getting profile picture:', profilePicture);
            console.log('Profile from localStorage:', localStorage.getItem('profilePicture'));
            
            if (profilePicture && profilePicture.trim() !== '') {
                console.log('Using provided profile picture:', profilePicture);
                return profilePicture;
            }
            
            console.log('Using default picture:', this.defaultProfilePic);
            return this.defaultProfilePic;
        },

        handleImageError(e) {
            e.target.src = this.defaultProfilePic;
        },

        handleImageLoad() {
            console.log('Image loaded successfully');
        },
    },

    async created() {
        // Check authentication
        const isAdmin = localStorage.getItem('userType') === 'admin';
        const hasToken = localStorage.getItem('token') === 'true';
        const userId = localStorage.getItem('userId');
        
        if (!isAdmin || !hasToken || !userId) {
            console.log('Not authenticated, redirecting to login');
            this.$router.push('/admin/admin_login');
            return;
        }

        // Set the user ID in adminProfile
        this.adminProfile = {
            ...this.adminProfile,
            user_id: userId
        };

        console.log("Component created, fetching accounts...");
        this.fetchAccounts();
    },
};
</script>

<style scoped>
@import "@/assets/css/admin_panel.css";

.section {
    background: #fff;

    border-radius: 8px;

    padding: 1.5rem;

    margin: 1.5rem;

    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.contains {
    background: #f8f9fa;

    border-radius: 8px;

    padding: 1rem;

    margin-bottom: 1rem;

    position: relative;
}

.Edit,
.Trash {
    position: absolute;

    cursor: pointer;

    opacity: 0.7;

    transition: opacity 0.2s;
}

.overall {
    transform: translate(0, 30px);
}

.Edit {
    top: 1rem;

    right: 3rem;
}

.Trash {
    top: 1rem;

    right: 1rem;
}

.Edit:hover,
.Trash:hover {
    opacity: 1;
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
}

.admin-profile-name {
    color: #794646;

    font-weight: 600;

    font-size: 0.9rem;
}

.menu-container {
    margin-left: 0;
}

.account-details {
    margin-top: 1rem;

    padding: 0.5rem;

    background: #f8f9fa;

    border-radius: 4px;
}

.detail-item {
    margin: 0.5rem 0;

    color: #666;
}

.loading {
    text-align: center;

    padding: 2rem;

    color: #666;
}

.error {
    color: #dc3545;

    text-align: center;

    padding: 1rem;

    margin: 1rem;

    background: #f8d7da;

    border-radius: 4px;
}

.accounts-container {
    padding: 1rem;
}

.account-section {
    margin-bottom: 2rem;
}

.section-title {
    color: #794646;

    font-size: 1.5rem;

    margin-bottom: 1rem;

    padding-bottom: 0.5rem;

    border-bottom: 2px solid #794646;
}

.no-accounts {
    text-align: center;

    padding: 2rem;

    color: #666;
}

.containss {
    background: white;

    border: 1px solid #ddd;

    border-radius: 8px;

    padding: 1.5rem;

    margin-bottom: 1rem;

    position: relative;

    transition: box-shadow 0.3s ease;

    width: 90%;
}

.containss:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.sets {
    font-size: 1.2rem;

    color: #794646;

    font-weight: bold;

    margin-bottom: 0.5rem;
}

.user,
.number {
    color: #666;

    margin: 0.3rem 0;
}

.account-details {
    margin-top: 1rem;

    padding: 0.5rem;

    background: #f8f9fa;

    border-radius: 4px;
}

.detail-item {
    margin: 0.5rem 0;

    color: #666;
}

.user-info {
    padding: 1rem;

    background: #f8f9fa;

    border-radius: 8px;

    margin: 1rem 0;
}

.info-row {
    display: flex;

    padding: 0.5rem 0;

    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;

    color: #794646;

    width: 100px;

    flex-shrink: 0;
}

.info-value {
    color: #666;

    flex-grow: 1;
}

.Trash {
    position: absolute;

    top: 1.5rem;

    right: 1.5rem;

    cursor: pointer;

    opacity: 0.7;

    transition: opacity 0.2s;

    width: 24px;

    height: 24px;
}

.Trash:hover {
    opacity: 1;
}

.sets {
    font-size: 1.4rem;

    color: #794646;

    font-weight: bold;

    margin-bottom: 1rem;

    padding-right: 3rem;
}

.section-title {
    color: #794646;

    font-size: 1.8rem;

    margin-bottom: 1.5rem;

    padding-bottom: 0.5rem;

    border-bottom: 2px solid #794646;
}

.admin-container {
    border: 2px solid #794646;

    background: #fff5f5;
}

.admin-container .sets {
    color: #794646;
}

.admin-container .info-label {
    color: #794646;
}

.account-header {
    display: flex;

    align-items: center;

    gap: 1rem;

    margin-bottom: 1rem;
}

.account-profile-pic {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #794646;
    background-color: #f8f9fa; /* Light background for empty state */
}

.sets {
    margin: 0;
}

.info-row {
    display: flex;
    padding: 0.8rem;
    border-bottom: 1px solid #eee;
    background: white;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.info-row:last-child {
    margin-bottom: 0;
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #794646;
    width: 100px;
    flex-shrink: 0;
}

.info-value {
    color: #666;
    flex-grow: 1;
}

.user-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
}

.admin-container .account-profile-pic {
    border-color: #794646;
    background: #fff;
}

.action-icons {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    display: flex;
    gap: 1rem;
}

.Edit,
.Trash {
    width: 24px;
    height: 24px;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.Edit:hover,
.Trash:hover {
    opacity: 1;
}

.Trash {
    position: static;
}

.info-row:first-child {
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.home {
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 0 2rem;
}

.helper {
    color: #794646;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    text-align: left;
}

.add-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #794646;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background-color 0.2s;
    border: none;
    cursor: pointer;
    align-self: flex-start;
}

.add-btn:hover {
    background: #693c3c;
}

.user-type {
    color: #794646;
    font-size: 0.9em;
    font-style: italic;
    margin-left: 0.5rem;
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
