$(document).ready(function () {

    // CHECK IF ALREADY LOGGED IN
    $.ajax({
        url: "backend/check_session.php", // Adjust this to the correct backend URL
        type: "POST",
        dataType: "json",
        success: function (response) {
            if (response.logged_in) {
                window.location.href = "dashboard.html"; // Redirect on success
            }
        },
        error: function () {
            $("#error-message").text("Something went wrong. Please try again later.").show();
        }
    });

    // Check for pass and confirm pass
    $('#password, #confirmPassword').on('input', function () {
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();

        if (password && confirmPassword && password !== confirmPassword) {
            $('#error-message').text('Passwords do not match').show();
        } else {
            $('#error-message').hide(); // Hide the error message when passwords match
        }
    });


    $("#registrationForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var firstName = $("#firstName").val().trim();
        var lastName = $("#lastName").val().trim();
        var email = $("#email").val().trim();
        var pass = CryptoJS.MD5($("#password").val().trim()).toString();

        $.ajax({
            url: "backend/signup.php", // Adjust this to the correct backend URL
            type: "POST",
            data: {
                firstName: firstName,
                lastName: lastName,
                email: email,
                pass: pass
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Successful Registration Alert
                    Swal.fire({
                        title: "<strong>Registration Successful!</strong>",
                        html: "<p>Welcome to SaveMeOut! Redirecting you to the dashboard...</p>",
                        icon: "success",
                        showConfirmButton: false, // No confirm button, since redirection happens automatically
                        timer: 3000,  // Automatically closes after 3 seconds
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal-custom-popup', // For custom CSS styling
                        },
                    }).then(() => {
                        window.location.href = './dashboard.html';  // Redirect after alert closes
                    });
                } else {
                    switch (response.errorCode) {
                        case "UAE001":
                            Swal.fire({
                                title: "<strong>Error!</strong>",
                                html: "<p>The Email ID is already in use. Please login to continue.</p>",
                                icon: "warning",
                                showCloseButton: true,  // Close button in the top-right corner
                                showConfirmButton: true,
                                confirmButtonText: '<i class="bi bi-box-arrow-in-right"></i> Go to Login',  // Icon added for extra touch
                                confirmButtonColor: '#6f86d6',
                                customClass: {
                                    popup: 'swal-custom-popup',
                                },
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = './login.html';  // Redirect to login on confirmation
                                }
                            });

                            break;
                        default:
                            Swal.fire({
                                title: "Error!",
                                text: "Something went wrong",
                                icon: "error",  // Corrected the icon (use lowercase "error")
                                showConfirmButton: true,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#6f86d6',
                            });
                            break;
                    }
                }
            },
            error: function () {
                $("#error-message").text("Something went wrong. Please try again later.").show();
            }
        });
    });
});
