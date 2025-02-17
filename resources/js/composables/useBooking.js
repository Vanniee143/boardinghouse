import { ref } from 'vue'
import axios from 'axios'

export function useBooking() {
    const bookings = ref([])
    const loading = ref(false)
    const error = ref(null)

    const fetchBookings = async () => {
        loading.value = true
        try {
            const response = await axios.get('/user/bookings')
            bookings.value = response.data.data || []
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch bookings'
            bookings.value = []
        } finally {
            loading.value = false
        }
    }

    const createBooking = async (bookingData) => {
        loading.value = true
        try {
            const response = await axios.post('/user/bookings/create', bookingData)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to create booking'
            throw err
        } finally {
            loading.value = false
        }
    }

    const cancelBooking = async (bookingId) => {
        if (!confirm('Are you sure you want to cancel this booking?')) {
            return
        }

        loading.value = true
        try {
            await axios.post(`/user/bookings/${bookingId}/cancel`)
            await fetchBookings()
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to cancel booking'
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        bookings,
        loading,
        error,
        fetchBookings,
        createBooking,
        cancelBooking
    }
} 