// rating-script.js

const stars = document.querySelectorAll('.star');
const ratingValueDisplay = document.getElementById('rating-value');
let selectedRating = 0;

stars.forEach(star => {
    star.addEventListener('click', (e) => {
        selectedRating = e.target.getAttribute('data-value');
        updateRating(selectedRating);
    });

    star.addEventListener('mouseover', (e) => {
        const hoverValue = e.target.getAttribute('data-value');
        highlightStars(hoverValue);
    });

    star.addEventListener('mouseout', () => {
        highlightStars(selectedRating);
    });
});

function highlightStars(value) {
    stars.forEach(star => {
        if (star.getAttribute('data-value') <= value) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });
}

function updateRating(value) {
    ratingValueDisplay.textContent = `Rating: ${value}`;
}
