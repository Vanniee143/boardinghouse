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
            <div class="profile-container">
                <h4 class="helper">My Profile</h4>

                <div class="profile-content">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img
                                :src="
                                    getProfilePicture(formData.profile_picture)
                                "
                                alt="Profile Picture"
                                class="profile-picture"
                                @error="handleImageError"
                            />

                            <label class="change-photo-btn">
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    class="hidden"
                                />

                                Change Photo
                            </label>
                        </div>
                    </div>

                    <form @submit.prevent="updateProfile" class="profile-form">
                        <div class="form-group">
                            <label>Name</label>

                            <input
                                type="text"
                                v-model="formData.name"
                                required
                                class="form-input"
                            />
                        </div>

                        <div class="form-group">
                            <label>Email</label>

                            <input
                                type="email"
                                v-model="formData.email"
                                required
                                class="form-input"
                            />
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>

                            <input
                                type="tel"
                                v-model="formData.phone_number"
                                pattern="[0-9]*"
                                class="form-input"
                            />
                        </div>

                        <div class="form-group">
                            <label>Gender</label>

                            <select
                                v-model="formData.gender"
                                class="form-input"
                            >
                                <option value="">Select Gender</option>

                                <option value="male">Male</option>

                                <option value="female">Female</option>

                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Birthdate</label>

                            <input
                                type="date"
                                v-model="formData.birthdate"
                                class="form-input"
                            />
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

import defaultProfilePic from "@/assets/images/Profile.png";

export default {
    name: "LandlordProfile",

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

            formData: {
                name: "",

                email: "",

                phone_number: "",

                gender: "",

                birthdate: "",

                profile_picture: null,
            },
        };
    },

    methods: {
        getProfilePicture(profilePicture) {
            try {
                // Check if profilePicture is null or undefined

                if (!profilePicture) {
                    return this.defaultProfilePic;
                }

                // If it's an object (File), create URL

                if (profilePicture instanceof File) {
                    return URL.createObjectURL(profilePicture);
                }

                // If it's a string and not empty

                if (
                    typeof profilePicture === "string" &&
                    profilePicture.trim() !== ""
                ) {
                    return profilePicture;
                }

                return this.defaultProfilePic;
            } catch (error) {
                console.error("Error getting profile picture:", error);

                return this.defaultProfilePic;
            }
        },

        handleImageError(e) {
            e.target.src = this.defaultProfilePic;
        },

        handleImageUpload(event) {
            const file = event.target.files[0];

            if (file) {
                this.formData.profile_picture = file;

                const reader = new FileReader();

                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };

                reader.readAsDataURL(file);
            }
        },

        navigateMenu(event) {
            const route = event.target.value;

            if (route === "/landlord/login") {
                this.logout();
            } else {
                this.$router.push(route);
            }
        },

        async logout() {
            try {
                await axios.post("/landlord/logout");

                localStorage.clear();

                delete axios.defaults.headers.common["Authorization"];

                this.$router.push("/landlord/login");
            } catch (error) {
                console.error("Logout error:", error);

                this.$router.push("/landlord/login");
            }
        },

        async fetchProfile() {
            try {
                const userId = this.landlordProfile.user_id;

                if (!userId) {
                    throw new Error("User ID not found");
                }

                const response = await axios.get(
                    `/landlord/fetch-profile/${userId}`,
                    {
                        headers: {
                            Accept: "application/json",

                            "X-User-Id": userId,
                        },
                    }
                );

                console.log("Fetched profile data:", response.data);

                if (response.data.status === "success") {
                    const userData = response.data.data;

                    // Update form data

                    this.formData = {
                        name: userData.name || "",

                        email: userData.email || "",

                        phone_number: userData.phone_number || "",

                        gender: userData.gender || "",

                        birthdate: userData.birthdate || "",

                        profile_picture: userData.profile_picture || null,
                    };

                    // Update component data

                    this.landlordName = userData.name;

                    this.landlordProfile.profile_picture =
                        userData.profile_picture;

                    // Update localStorage

                    localStorage.setItem("userName", userData.name);

                    if (userData.profile_picture) {
                        localStorage.setItem(
                            "profilePicture",
                            userData.profile_picture
                        );
                    }

                    localStorage.setItem("userEmail", userData.email);

                    localStorage.setItem("userPhone", userData.phone_number);

                    localStorage.setItem("userGender", userData.gender);

                    localStorage.setItem("userBirthdate", userData.birthdate);
                } else {
                    throw new Error(
                        response.data.message || "Failed to fetch profile"
                    );
                }
            } catch (error) {
                console.error("Error fetching profile:", error);

                alert("Failed to load profile data");
            }
        },

        async updateProfile() {
            try {
                const formData = new FormData();

                // Add all form fields to FormData

                formData.append("name", this.formData.name);

                formData.append("email", this.formData.email);

                formData.append(
                    "phone_number",
                    this.formData.phone_number || ""
                );

                formData.append("gender", this.formData.gender || "");

                formData.append("birthdate", this.formData.birthdate || "");

                // Only append profile picture if it's a new file

                if (this.formData.profile_picture instanceof File) {
                    formData.append(
                        "profile_picture",
                        this.formData.profile_picture
                    );
                }

                const userId = this.landlordProfile.user_id;

                // Add debug logs

                console.log("Updating profile for user:", userId);

                console.log("Form data being sent:", {
                    name: this.formData.name,

                    email: this.formData.email,

                    phone_number: this.formData.phone_number,

                    gender: this.formData.gender,

                    birthdate: this.formData.birthdate,

                    has_new_picture:
                        this.formData.profile_picture instanceof File,
                });

                const response = await axios.post(
                    `/landlord/update-profile/${userId}`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",

                            Accept: "application/json",

                            "X-User-Id": userId,
                        },
                    }
                );

                if (response.data.status === "success") {
                    // Update local storage with new values

                    localStorage.setItem("userName", this.formData.name);

                    if (response.data.data.profile_picture) {
                        localStorage.setItem(
                            "profilePicture",
                            response.data.data.profile_picture
                        );
                    }

                    localStorage.setItem("userEmail", this.formData.email);

                    localStorage.setItem(
                        "userPhone",
                        this.formData.phone_number
                    );

                    localStorage.setItem("userGender", this.formData.gender);

                    localStorage.setItem(
                        "userBirthdate",
                        this.formData.birthdate
                    );

                    // Update landlord name in the component

                    this.landlordName = this.formData.name;

                    alert("Profile updated successfully");

                    await this.fetchProfile(); // Refresh the profile data
                } else {
                    throw new Error(
                        response.data.message || "Failed to update profile"
                    );
                }
            } catch (error) {
                console.error("Error updating profile:", error);

                if (error.response) {
                    console.error("Server response:", error.response.data);
                }

                alert(
                    error.response?.data?.message || "Failed to update profile"
                );
            }
        },
    },

    async created() {
        await this.fetchProfile();
    },
};
</script>

<style scoped>
@import "@/assets/css/landlord_panel.css";

/* Add/Update these navbar profile styles */

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

/* Add your styles here */

.profile-container {
    background: white;

    border-radius: 12px;

    padding: 2rem;

    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

    max-width: 800px;

    margin: 0 auto;
}

.helper {
    color: #794646;

    font-size: 24px;

    font-weight: 700;

    margin-bottom: 2rem;
}

.profile-content {
    display: flex;

    flex-direction: column;

    gap: 2rem;
}

.profile-header {
    display: flex;

    justify-content: center;

    margin-bottom: 2rem;
}

.profile-picture-container {
    display: flex;

    flex-direction: column;

    align-items: center;

    gap: 1rem;
}

.profile-picture {
    width: 150px;

    height: 150px;

    border-radius: 50%;

    object-fit: cover;

    border: 4px solid #794646;
}

.change-photo-btn {
    background: #794646;

    color: white;

    padding: 0.5rem 1rem;

    border-radius: 4px;

    cursor: pointer;

    font-weight: 600;

    transition: background-color 0.2s;
}

.change-photo-btn:hover {
    background: #5d3535;
}

.hidden {
    display: none;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;

    font-weight: 600;

    color: #374151;

    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;

    padding: 0.75rem;

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    font-size: 1rem;

    transition: border-color 0.2s;
}

.form-input:focus {
    border-color: #794646;

    outline: none;
}

.form-actions {
    display: flex;

    justify-content: flex-end;

    margin-top: 2rem;
}

.save-btn {
    background: #794646;

    color: white;

    padding: 0.75rem 1.5rem;

    border: none;

    border-radius: 6px;

    font-weight: 600;

    cursor: pointer;

    transition: background-color 0.2s;
}

.save-btn:hover {
    background: #5d3535;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 1.5rem;
    }

    .profile-picture {
        width: 120px;

        height: 120px;
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
