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

// JavaScript to show the selected file name
document.getElementById('fileInput').addEventListener('change', function() {
    const fileName = document.getElementById('fileInput').files[0]?.name || "No file chosen";
    document.getElementById('fileName').textContent = fileName;
});
