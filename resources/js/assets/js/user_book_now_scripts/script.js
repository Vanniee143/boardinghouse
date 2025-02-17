new Vue({
    el: '#app',
    data: {
        beds: 1,
        persons: 1,
        rooms: 1,
        controlsVisible: false,
        maxPersons: 48, // Maximum number of persons allowed
        maxBeds: 48,    // Maximum number of beds allowed
        maxRooms: 6     // Maximum number of rooms allowed
    },
    methods: {
        increase(type) {
            if (type === 'persons') {
                if (this.persons < this.maxPersons) {
                    this.persons++;
                    this.updateBedsAndRooms();
                } else {
                    alert(`You cannot exceed the maximum of ${this.maxPersons} persons.`);
                }
            } else if (type === 'beds') {
                if (this.beds < this.maxBeds) {
                    this.beds++;
                    // Automatically increase persons when beds increase
                    if (this.persons < this.maxPersons) {
                        this.persons++;
                    }
                    this.updateBedsAndRooms();
                } else {
                    alert(`You cannot exceed the maximum of ${this.maxBeds} beds.`);
                }
            } else if (type === 'rooms') {
                if (this.rooms < this.maxRooms) {
                    // Increase all counts by 1 when a room is added, respecting the limits
                    if (this.persons < this.maxPersons) {
                        this.persons++;
                    }
                    if (this.beds < this.maxBeds) {
                        this.beds++;
                    }
                    this.rooms++; // Increase room count
                } else {
                    alert(`You cannot exceed the maximum of ${this.maxRooms} rooms.`);
                }
            }
        },
        decrease(type) {
            if (type === 'persons' && this.persons > 1) { // Ensure persons don't go below 1
                this.persons--;
                this.updateBedsAndRooms(); // Adjust beds if needed
            } else if (type === 'beds' && this.beds > 1) { // Ensure beds don't go below 1
                this.beds--;
                // Optionally, decrease persons when beds decrease
                if (this.persons > 1) {
                    this.persons--; // Ensure persons do not go below 1
                }
                this.updateBedsAndRooms(); // Adjust if needed
            } else if (type === 'rooms' && this.rooms > 1) { // Ensure rooms don't go below 1
                this.rooms--; // Decrease rooms
                // Adjust persons and beds when rooms are decreased
                this.persons = Math.max(1, this.persons - 8); // Ensure min 1
                this.beds = Math.max(1, this.beds - 8); // Ensure min 1
            }
        },
        updateBedsAndRooms() {
            // Calculate the number of beds and rooms based on the number of persons
            this.beds = Math.min(Math.ceil(this.persons / 1), this.maxBeds); // 1 bed for every 1 person
            this.rooms = Math.min(Math.ceil(this.persons / 8), this.maxRooms); // 1 room for every 8 persons
        },
        reset() {
            this.beds = 1; // Reset to minimum value
            this.persons = 1; // Reset to minimum value
            this.rooms = 1; // Reset to minimum value
            this.reminderVisible = false; // Reset reminder visibility
        },
        confirm() {
            let errorMessage = '';

            if (this.beds < 1) {
                errorMessage += `Error: beds must not be ${this.beds}.\n`;
            }
            if (this.persons < 1) {
                errorMessage += `Error: persons must not be ${this.persons}.\n`;
            }
            if (this.rooms < 1) {
                errorMessage += `Error: rooms must not be ${this.rooms}.\n`;
            }

            if (errorMessage) {
                alert(errorMessage.trim()); // Show all errors at once
                return; // Exit if there are errors
            }
            
            alert(`Confirmed: Beds: ${this.beds}, Persons: ${this.persons}, Rooms: ${this.rooms}`);
            
            // Close the controls and hide reminder after confirmation
            this.controlsVisible = false;
            this.reminderVisible = false; // Ensure reminder is hidden
        },
        toggleControls() {
            this.controlsVisible = !this.controlsVisible;
            // Show reminder if controls are shown
            this.reminderVisible = this.controlsVisible;
        },
        toggleReminder() {
            this.reminderVisible = !this.reminderVisible; // Toggle the reminder visibility
        },  
        redirectToReminder() {
            window.location.href = 'user_boarding_house.html'; // Redirect to reminder.html
        }
    },
    mounted() {
        // Close the controls when clicking outside of them
        document.addEventListener('click', (event) => {
            const bookingControls = this.$el.querySelector('.booking-controls');
            const label = this.$el.querySelector('.cals');
            
            // Check if the clicked target is outside of the controls and the label
            if (this.controlsVisible && !bookingControls.contains(event.target) && !label.contains(event.target)) {
                this.controlsVisible = false; // Close the booking controls
                this.reminderVisible = false; // Hide the reminder if controls are closed
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const menuSelect = document.getElementById('menuSelect');
    const menuImage = document.getElementById('menuImage');

    // When the menu image is clicked, focus and open the select
    menuImage.addEventListener('click', function() {
        menuSelect.focus(); // Focus on the select element
        menuSelect.click(); // Simulate click to open the dropdown
    });

    // When the selection changes, redirect to the corresponding page
    menuSelect.addEventListener('change', function() {
        const selectedValue = menuSelect.value;

        if (selectedValue !== "#") { // Ignore the placeholder option
            window.location.href = selectedValue; // Redirect to the selected page
        }
    });
});