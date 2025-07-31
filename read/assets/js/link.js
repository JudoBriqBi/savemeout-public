$(document).ready(function () {
    $("#linkForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var tafid = $("#tagid").val();
        var tagpass = $("#tagpin").val();

        $.ajax({
            url: "backend/check_link.php",
            type: "GET",
            data: { tagid: tafid, tagpin: tagpass },
            success: function (response) {
                response = JSON.parse(response);


                if (response.success === false) {
                    switch (response.errorCode) {
                        case "ALTAP001":
                            Swal.fire({
                                title: "Error!",
                                text: "The Tag is already in use",
                                icon: "warning",
                                showConfirmButton: true
                            });
                            break;

                        case "COMI001":
                            Swal.fire({
                                title: "Error!",
                                text: "Please check your ID or PIN and try again",
                                icon: "warning",
                                showConfirmButton: true
                            });
                            break;

                        case "UNLI087":
                            Swal.fire({
                                title: "Error!",
                                text: "Please login and try again",
                                icon: "warning",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "./login.html";
                            });
                            break;
                    }
                } else {
                    Swal.fire({
                        title: "Success!",
                        text: "Tag linked successfully",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = `./dashboard.html`;
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Failed",
                    text: "Failed to save data.",
                    icon: "error"
                });
            }
        });
    });
});
