// JavaScript to show the selected file name
document.getElementById('fileInput').addEventListener('change', function() {
    const fileName = document.getElementById('fileInput').files[0]?.name || "No file chosen";
    document.getElementById('fileName').textContent = fileName;
});