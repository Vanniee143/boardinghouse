
function showStep2() {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}

function showReceipt() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('receipt').style.display = 'block';
}

function redirectToBoardingHouse() {
    window.location.href = "user_boarding_house.html";
}