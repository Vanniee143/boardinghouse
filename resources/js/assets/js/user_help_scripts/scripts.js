document.getElementById('supportSelect').addEventListener('change', function() {
    const haventInfo = document.getElementById('haventInfo');
    
    // Show the "havent" section if "I haven't received my booking confirmation" is selected
    if (this.value === 'ihavent') {
        haventInfo.style.display = 'block';
    } else {
        haventInfo.style.display = 'none'; // Hide it if other options are selected
    }
});

document.getElementById('supportSelect').addEventListener('change', function() {
    const haveInfo = document.getElementById('haveInfo');
    
    // Show the "havent" section if "I haven't received my booking confirmation" is selected
    if (this.value === 'payment') {
        haveInfo.style.display = 'block';
    } else {
        haveInfo.style.display = 'none'; // Hide it if other options are selected
    }
});

document.getElementById('supportSelect').addEventListener('change', function() {
    const wantInfo = document.getElementById('wantInfo');
    
    // Show the "havent" section if "I haven't received my booking confirmation" is selected
    if (this.value === 'cancel') {
        wantInfo.style.display = 'block';
    } else {
        wantInfo.style.display = 'none'; // Hide it if other options are selected
    }
});

document.getElementById('supportSelect').addEventListener('change', function() {
    const errorInfo = document.getElementById('errorInfo');
    
    // Show the "havent" section if "I haven't received my booking confirmation" is selected
    if (this.value === 'error') {
        errorInfo.style.display = 'block';
    } else {
        errorInfo.style.display = 'none'; // Hide it if other options are selected
    }
});