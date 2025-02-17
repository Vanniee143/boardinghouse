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

document.querySelectorAll('input[type="radio"]').forEach((radioButton) => {
    radioButton.addEventListener('click', function() {
        if (this.wasClicked) {
            this.checked = false;
            this.wasClicked = false;
        } else {
            this.wasClicked = true;
        }

        // Reset the other radio buttons in the same group
        document.querySelectorAll(`input[name="${this.name}"]`).forEach((otherRadio) => {
            if (otherRadio !== this) {
                otherRadio.wasClicked = false;
            }
        });
    });
});

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
            if (this.value === 'Users') {
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

    // JavaScript to show the selected file name
document.getElementById('fileInput').addEventListener('change', function() {
    const fileName = document.getElementById('fileInput').files[0]?.name || "No file chosen";
    document.getElementById('fileName').textContent = fileName;
});
