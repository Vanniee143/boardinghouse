document.addEventListener("DOMContentLoaded", function() {
    const stars = document.querySelectorAll('.starsss');
    const ratingValueDisplay = document.getElementById('rating-valuese');
    let selectedRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', (e) => {
            selectedRating = e.target.getAttribute('data-values');
            updateRating(selectedRating);
        });

        star.addEventListener('mouseover', (e) => {
            const hoverValue = e.target.getAttribute('data-values');
            highlightStars(hoverValue);
        });

        star.addEventListener('mouseout', () => {
            highlightStars(selectedRating);
        });
    });

    function highlightStars(value) {
        stars.forEach(star => {
            if (star.getAttribute('data-values') <= value) {
                star.classList.add('selecteds');
            } else {
                star.classList.remove('selecteds');
            }
        });
    }

    function updateRating(value) {
        ratingValueDisplay.textContent = `Rating: ${value}`;
    }
});
