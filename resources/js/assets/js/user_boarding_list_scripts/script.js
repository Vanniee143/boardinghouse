import { ref, onMounted } from 'vue';

export default {
    setup() {
        const beds = ref(1);
        const persons = ref(1);
        const rooms = ref(1);
        const controlsVisible = ref(false);
        const maxPersons = ref(48);
        const maxBeds = ref(48);
        const maxRooms = ref(6);

        const increase = (type) => {
            if (type === 'persons' && persons.value < maxPersons.value) {
                persons.value++;
                updateBedsAndRooms();
            } else if (type === 'beds' && beds.value < maxBeds.value) {
                beds.value++;
                if (persons.value < maxPersons.value) {
                    persons.value++;
                }
                updateBedsAndRooms();
            } else if (type === 'rooms' && rooms.value < maxRooms.value) {
                if (persons.value < maxPersons.value) persons.value++;
                if (beds.value < maxBeds.value) beds.value++;
                rooms.value++;
            }
        };

        const decrease = (type) => {
            if (type === 'persons' && persons.value > 1) {
                persons.value--;
                updateBedsAndRooms();
            } else if (type === 'beds' && beds.value > 1) {
                beds.value--;
                updateBedsAndRooms();
            } else if (type === 'rooms' && rooms.value > 1) {
                rooms.value--;
            }
        };

        const updateBedsAndRooms = () => {
            // Update beds based on persons (1:1 ratio)
            beds.value = Math.min(persons.value, maxBeds.value);
            
            // Update rooms (assuming 8 persons per room)
            rooms.value = Math.min(Math.ceil(persons.value / 8), maxRooms.value);
        };

        const handleBookNow = (roomId) => {
            try {
                // Save current selection to localStorage
                localStorage.setItem('bookingDetails', JSON.stringify({
                    beds: beds.value,
                    persons: persons.value,
                    rooms: rooms.value,
                    roomId: roomId
                }));
                
                // Navigate to booking page
                window.location.href = `/user_book_now/${roomId}`;
            } catch (error) {
                console.error('Error saving booking details:', error);
                // Still navigate even if storage fails
                window.location.href = `/user_book_now/${roomId}`;
            }
        };

        const handleViewReviews = (roomId) => {
            window.location.href = `/user_room_review/${roomId}`;
        };

        onMounted(() => {
            // Initialize event listeners for booking and view buttons
            document.querySelectorAll('[id^="BookButton"]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const roomId = button.getAttribute('data-room-id');
                    handleBookNow(roomId);
                });
            });

            document.querySelectorAll('[id^="viewButton"]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const roomId = button.getAttribute('data-room-id');
                    handleViewReviews(roomId);
                });
            });
        });

        return {
            beds,
            persons,
            rooms,
            controlsVisible,
            increase,
            decrease,
            updateBedsAndRooms,
            handleBookNow,
            handleViewReviews
        };
    }
};