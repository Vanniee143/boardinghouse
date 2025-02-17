export const initializeCsrf = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            return true;
        }
        return false;
    } catch (error) {
        console.error('CSRF initialization failed:', error);
        return false;
    }
}; 