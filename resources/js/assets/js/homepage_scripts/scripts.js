import { useBooking } from '@/composables/useBooking';

const { bookings, loading, error, fetchBookings, createBooking, cancelBooking } = useBooking();

export function showReminder(reminderVisible) {
    if (!bookings.value.length) {
        alert('Please check your booking details');
        return;
    }
    reminderVisible.value = true;
}

export function hideReminder(reminderVisible) {
    reminderVisible.value = false;
}

export function handleBookingUpdate(type, operation) {
    return updateCounts(type, operation);
}

export const initializeBooking = () => {
    return {
        showReminder,
        hideReminder,
        handleBookingUpdate
    };
};
