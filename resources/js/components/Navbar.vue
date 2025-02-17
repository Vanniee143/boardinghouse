import { handleLogout } from '@/utils/auth';

export default {
    methods: {
        async logout() {
            try {
                await handleLogout(this.$router);
                // Optional: Show success message
            } catch (error) {
                // Handle error (show error message, etc)
                console.error('Logout failed:', error);
            }
        }
    }
} 