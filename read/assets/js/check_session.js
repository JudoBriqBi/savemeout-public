document.addEventListener("DOMContentLoaded", function () {
    fetch("backend/check_session.php")
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                // Redirect to login page if not logged in
                window.location.href = "login.html";
            }
        })
        .catch(error => console.error("Error checking session:", error));
});