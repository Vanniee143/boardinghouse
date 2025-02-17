import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';

// Configure axios defaults for web routes
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Get CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]')?.content;
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
} else {
    console.error('CSRF token not found');
}

// Debug request headers
axios.interceptors.request.use(request => {
    console.log('Request Headers:', request.headers);
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
    return request;
}, error => {
    console.error('Request Error:', error);
    return Promise.reject(error);
});

// Handle CSRF token mismatch
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            console.error('CSRF token mismatch detected');
            // Get a fresh CSRF token
            axios.get('/sanctum/csrf-cookie').then(() => {
                // Refresh the page to get new token
                window.location.reload();
            });
        }
        return Promise.reject(error);
    }
);

// Add user ID to headers if available
axios.interceptors.request.use(config => {
    const userId = localStorage.getItem('userId');
    if (userId) {
        config.headers['X-User-Id'] = userId;
    }
    return config;
});

const app = createApp(App);
app.config.globalProperties.$axios = axios;
app.use(router);
app.mount('#app');

export default axios;