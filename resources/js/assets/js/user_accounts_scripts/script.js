document.addEventListener('DOMContentLoaded', function() {
    const menuSelect = document.getElementById('menuSelect');
    const menuImage = document.getElementById('menuImage');

    // When the menu image is clicked, focus and open the select
    menuImage.addEventListener('click', function() {
        menuSelect.focus();
        menuSelect.dispatchEvent(new MouseEvent('click')); // Improved compatibility for opening the dropdown
    });

    // When the selection changes, redirect to the corresponding page
    menuSelect.addEventListener('change', function() {
        const selectedValue = menuSelect.value;

        if (selectedValue !== "#") { // Ignore the placeholder option
            window.location.href = selectedValue; // Redirect to the selected page
        }
    });
});

// Handle custom radio button behavior to allow deselecting
document.querySelectorAll('input[type="radio"]').forEach((radioButton) => {
    radioButton.addEventListener('click', function() {
        console.log("Radio button clicked:", this.value, "wasClicked:", this.wasClicked);
        if (this.wasClicked) {
            this.checked = false;
            this.wasClicked = false;
        } else {
            this.wasClicked = true;
        }

        // Reset other radio buttons in the same group
        document.querySelectorAll(`input[name="${this.name}"]`).forEach((otherRadio) => {
            if (otherRadio !== this) {
                otherRadio.wasClicked = false;
            }
        });
    });
});

// Store selected radio button in localStorage
function storeUserTypeSelection() {
    let selectedRadio = document.querySelector('input[name="userType"]:checked');
    if (selectedRadio) {
        localStorage.setItem('selectedUserType', selectedRadio.value);
        console.log("Stored user type:", selectedRadio.value);
    }
}

// Add event listener to radio buttons
document.querySelectorAll('input[name="userType"]').forEach(radio => {
    radio.addEventListener('change', function() {
        storeUserTypeSelection();
        if (this.value === 'Users') {
            console.log('Redirecting to register.html');
            window.location.href = 'register.html';
        }
    });
});

// Load the stored radio button selection from localStorage
function loadUserTypeSelection() {
    let storedSelection = localStorage.getItem('selectedUserType');
    if (storedSelection) {
        document.querySelector(`input[value="${storedSelection}"]`).checked = true;
        console.log("Loaded stored user type:", storedSelection);
    }
}

// Load the stored selection on page load
window.onload = loadUserTypeSelection;

// Show the selected file name
document.getElementById('fileInput').addEventListener('change', function() {
    const fileName = document.getElementById('fileInput').files[0]?.name || "No file chosen";
    console.log("File selected:", fileName);
    document.getElementById('fileName').textContent = fileName;
});

document.getElementById('accountLink').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent default action of the anchor
    const dropdown = document.getElementById('accountDropdown');
    
    // Toggle between block and none to show/hide the dropdown
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
});

document.addEventListener('click', function(event) {
    const accountItem = document.querySelector('.account-item');
    const dropdown = document.getElementById('accountDropdown');

    if (!accountItem.contains(event.target)) {
        dropdown.style.display = 'none'; // Close dropdown if clicked outside
    }
});


