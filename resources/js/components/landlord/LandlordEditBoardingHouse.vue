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
            <h4 class="helper">Edit Boarding House</h4>

            <div class="form-container">
                <form
                    @submit.prevent="updateBoardingHouse"
                    class="boarding-house-form"
                >
                    <div class="form-group">
                        <label>Boarding House Name</label>

                        <input
                            type="text"
                            v-model="formData.name"
                            required
                            placeholder="Enter boarding house name"
                            class="form-input"
                        />
                    </div>

                    <div class="form-group">
                        <label>Description</label>

                        <textarea
                            v-model="formData.description"
                            required
                            placeholder="Enter description"
                            rows="4"
                            class="form-textarea"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label>Address</label>

                        <input
                            type="text"
                            v-model="formData.address"
                            required
                            placeholder="Enter complete address"
                            class="form-input"
                        />
                    </div>

                    <div class="form-group">
                        <label>Landlord Name</label>

                        <input
                            type="text"
                            v-model="formData.landlord_name"
                            required
                            placeholder="Enter landlord name"
                            class="form-input"
                        />
                    </div>

                    <div class="form-group">
                        <label>Landlord Phone</label>

                        <input
                            type="tel"
                            v-model="formData.landlord_phone"
                            required
                            placeholder="Enter landlord phone number"
                            pattern="[0-9]*"
                            class="form-input"
                        />
                    </div>

                    <div class="form-group">
                        <label>Boarding House Image</label>

                        <div class="image-upload-container">
                            <div class="image-preview" v-if="imagePreview">
                                <img
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="preview-image"
                                />

                                <button
                                    type="button"
                                    class="remove-image"
                                    @click="removeImage"
                                >
                                    &times;
                                </button>
                            </div>

                            <div class="upload-btn-wrapper">
                                <button
                                    type="button"
                                    class="upload-btn"
                                    @click="triggerFileInput"
                                >
                                    {{
                                        imagePreview
                                            ? "Change Image"
                                            : "Upload Image"
                                    }}
                                </button>

                                <input
                                    type="file"
                                    ref="fileInput"
                                    @change="handleImageUpload"
                                    accept="image/*"
                                    class="file-input"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button
                            type="button"
                            @click="$router.push('/landlord/boarding-houses')"
                            class="cancel-btn"
                        >
                            Cancel
                        </button>

                        <button type="submit" class="submit-btn">
                            Update Boarding House
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import "@/assets/css/landlord_panel.css";

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

.overall {
    padding: 2rem;

    width: 100%;

    max-width: 1200px;

    margin: 0 auto;

    transform: translate(0, 70px);
}

.form-container {
    background: white;

    padding: 2rem;

    border-radius: 12px;

    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.helper {
    color: #794646;

    font-size: 24px;

    font-weight: 700;

    margin-bottom: 2rem;
}

.boarding-house-form {
    display: flex;

    flex-direction: column;

    gap: 1.5rem;
}

.form-group {
    display: flex;

    flex-direction: column;

    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;

    color: #374151;
}

.form-input,
.form-textarea {
    padding: 0.75rem;

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    font-size: 1rem;

    transition: border-color 0.2s;
}

.form-input:focus,
.form-textarea:focus {
    border-color: #794646;

    outline: none;
}

.form-textarea {
    resize: vertical;

    min-height: 100px;
}

.form-actions {
    display: flex;

    gap: 1rem;

    margin-top: 1rem;
}

.cancel-btn,
.submit-btn {
    padding: 0.75rem 1.5rem;

    border: none;

    border-radius: 6px;

    font-weight: 600;

    cursor: pointer;

    transition: all 0.2s;
}

.cancel-btn {
    background: #f3f4f6;

    color: #374151;
}

.submit-btn {
    background: #794646;

    color: white;
}

.cancel-btn:hover {
    background: #e5e7eb;
}

.submit-btn:hover {
    background: #5d3535;
}

@media (max-width: 768px) {
    .form-container {
        padding: 1.5rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .cancel-btn,
    .submit-btn {
        width: 100%;
    }
}

.form-input[type="number"] {
    padding: 0.75rem;

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    font-size: 1rem;

    transition: border-color 0.2s;

    width: 100%;
}

.form-input[type="number"]:focus {
    border-color: #794646;

    outline: none;

    box-shadow: 0 0 0 2px rgba(121, 70, 70, 0.1);
}

.image-upload-container {
    margin-top: 1rem;
}

.image-preview {
    position: relative;

    width: 200px;

    height: 200px;

    margin-bottom: 1rem;
}

.preview-image {
    width: 100%;

    height: 100%;

    object-fit: cover;

    border-radius: 8px;
}

.remove-image {
    position: absolute;

    top: -10px;

    right: -10px;

    background: #ff4444;

    color: white;

    border: none;

    border-radius: 50%;

    width: 25px;

    height: 25px;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;
}

.upload-btn-wrapper {
    position: relative;

    overflow: hidden;

    display: inline-block;
}

.upload-btn {
    background: #794646;

    color: white;

    padding: 0.75rem 1.5rem;

    border: none;

    border-radius: 6px;

    cursor: pointer;
}

.file-input {
    position: absolute;

    left: 0;

    top: 0;

    opacity: 0;

    cursor: pointer;
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

<script>
import axios from "axios";

import defaultProfilePic from "@/assets/images/Profile.png";

export default {
    name: "LandlordEditBoardingHouse",

    props: {
        id: {
            type: [String, Number],

            required: true,
        },
    },

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

                description: "",

                address: "",

                landlord_name: "",

                landlord_phone: "",

                bh_images: null,

                user_id: localStorage.getItem("userId"),
            },

            imagePreview: null,

            originalImage: null,
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

        async fetchBoardingHouse() {
            try {
                const response = await axios.get(
                    `/landlord/get-boarding-house/${this.$route.params.id}`
                );

                this.formData = response.data.data;

                this.originalImage = this.formData.bh_images;

                this.imagePreview = this.formData.bh_images
                    ? `/storage/${this.formData.bh_images}`
                    : null;
            } catch (error) {
                console.error("Error fetching boarding house:", error);

                alert("Failed to load boarding house details");
            }
        },

        triggerFileInput() {
            this.$refs.fileInput.click();
        },

        handleImageUpload(event) {
            const file = event.target.files[0];

            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    // 2MB limit

                    alert("Image size should not exceed 2MB");

                    return;
                }

                this.formData.bh_images = file;

                this.imagePreview = URL.createObjectURL(file);
            }
        },

        removeImage() {
            this.formData.bh_images = null;

            this.imagePreview = null;

            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = "";
            }
        },

        async updateBoardingHouse() {
            try {
                const formDataToSend = new FormData();

                Object.keys(this.formData).forEach((key) => {
                    if (key === "bh_images") {
                        if (this.formData[key] instanceof File) {
                            formDataToSend.append(
                                "bh_images",
                                this.formData[key]
                            );
                        }
                    } else {
                        formDataToSend.append(key, this.formData[key]);
                    }
                });

                const response = await axios.post(
                    `/landlord/update-boarding-house/${this.$route.params.id}`,

                    formDataToSend,

                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );

                if (response.data.status === "success") {
                    alert("Boarding house updated successfully");

                    this.$router.push("/landlord/boarding-houses");
                }
            } catch (error) {
                console.error("Error updating boarding house:", error);

                alert(
                    error.response?.data?.message ||
                        "Failed to update boarding house"
                );
            }
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
    },

    async created() {
        await this.fetchLandlordProfile();

        await this.fetchBoardingHouse();
    },
};
</script>
