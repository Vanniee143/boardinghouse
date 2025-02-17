export const handleLogout = async (router) => {
    try {
        // Call backend logout endpoint
        const response = await axios.post('/logout');
        
        // Clear all localStorage items
        localStorage.clear();
        
        // Clear axios default headers
        delete axios.defaults.headers.common['X-CSRF-TOKEN'];
        delete axios.defaults.headers.common['X-User-Id'];
        
        // Redirect to login page
        router.push('/login');
        
        return response;
    } catch (error) {
        console.error('Logout error:', error);
        // Still clear local storage and redirect even if API call fails
        localStorage.clear();
        router.push('/login');
        throw error;
    }
}; 