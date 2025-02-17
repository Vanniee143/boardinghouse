    // Function to store selected radio button in localStorage
    function storeUserTypeSelection() {
        let selectedRadio = document.querySelector('input[name="userType"]:checked');
        if (selectedRadio) {
            localStorage.setItem('selectedUserType', selectedRadio.value);
        }
    }

    // Add event listener to radio buttons
    document.querySelectorAll('input[name="userType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            storeUserTypeSelection();
            // Redirect based on the selected radio button value
            if (this.value === 'Admin') {
                window.location.href = 'admin_register.html';  // Redirect to admin_register.html
            } else if (this.value === 'Landlord') {
                window.location.href = 'landlord_register.html';  // Redirect to landlord_register.html
            } else if (this.value === 'Users') {
                window.location.href = 'register.html';  // Redirect to register.html
            }
        });
    });

    // Function to load selected radio button from localStorage
    function loadUserTypeSelection() {
        let storedSelection = localStorage.getItem('selectedUserType');
        if (storedSelection) {
            document.querySelector(`input[value="${storedSelection}"]`).checked = true;
        }
    }

    // Load the stored selection on page load
    window.onload = loadUserTypeSelection;