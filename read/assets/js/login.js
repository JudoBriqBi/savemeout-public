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


    $("#loginForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var email = $("#email").val().trim();
        var pass = CryptoJS.MD5($("#password").val().trim()).toString();

        if (email === "" || pass === "") {
            $("#error-message").text("Please fill in all fields.").show();
            return;
        } else {
            $("#error-message").hide();
        }

        $.ajax({
            url: "backend/login.php", // Adjust this to the correct backend URL
            type: "POST",
            data: {
                email: email,
                password: pass
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    window.location.href = "dashboard.html"; // Redirect on success
                } else {
                    $("#error-message").text(response.error || "Invalid email or password.").show();
                }
            },
            error: function () {
                $("#error-message").text("Something went wrong. Please try again later.").show();
            }
        });
    });

    $("#idForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var tagId = $("#tagId").val().trim();
        var tagPin = $("#tagPin").val().trim();

        if (tagId === "" || tagPin === "") {
            $("#error-messageTag").text("Please fill in all fields.").show();
            return;
        } else {
            $("#error-messageTag").hide();
        }

        $.ajax({
            url: "backend/check_tag.php", // Adjust this to the correct backend URL
            type: "POST",
            data: {
                tagId: tagId,
                tagPin: tagPin
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    window.location.href = `view.html?tag_id=${encodeURIComponent(tagId)}&pin=${encodeURIComponent(tagPin)}`;
                } else {
                    $("#error-messageTag").text("Invalid Tag Id or Pin.").show();
                }
            },
            error: function () {
                $("#error-messageTag").text("Something went wrong. Please try again later.").show();
            }
        });
    });
});
