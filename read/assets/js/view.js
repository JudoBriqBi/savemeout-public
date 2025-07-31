$(document).ready(function () {
    // Get user ID from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const tagID = urlParams.get("tag_id");
    const tagPIN = urlParams.get("pin");

    if (!tagID) {
        alert("Invalid User ID");
        return;
    }
    if (!tagPIN) {
        alert("Pin missing or invalid");
        return;
    }


    $.ajax({
        url: `backend/get_all_details.php?tag_id=${tagID}&pin=${tagPIN}`,
        type: "GET",
        dataType: "json",
        success: function (res) {
            data = res.data;
            if (!res.success) {
                msg  =""
                if (res.errorCode="NULLDATA"){
                    msg = "No data for user"
                    if (res.qr) {
                        $("#qr-code").attr("src", `data:image/png;base64,${res.qr}`);
                    } else {
                        $("#qr-code").hide();
                    }
                }else{
                    msg = "Invalid Tag Id"
                }
                Swal.fire({
                    title: "Error!",
                    text: msg,
                    icon: "error",
                    timer: 2000,
                    showConfirmButton: false
                })
            }

            $("#full-name").text(`${data.user_first_name} ${data.user_last_name}`);
            $("#email").text(data.user_email);
            $("#mobile").text(data.user_mobile);
            $("#country").text(data.user_country);
            $("#state").text(data.user_state);
            $("#city").text(data.user_city);
            $("#pincode").text(data.user_pincode);
            $("#address").text(data.user_address);
            $("#birth-date").text(data.user_birth_date);
            $("#gender").text(data.user_gender);

            $("#blood-group").text(data.medical_blood_group);
            $("#height").text(data.medical_height);
            $("#weight").text(data.medical_weight);
            $("#any-disease").text(data.medical_any_disease);
            $("#disease").text(data.medical_disease);
            $("#any-allergies").text(data.medical_any_allergies);
            $("#allergies").text(data.medical_allergies);
            $("#prescription").text(data.medical_prescription);
            $("#important-notes").text(data.medical_important_notes);

            $("#emergency-full-name").text(`${data.emergency_first_name} ${data.emergency_last_name}`);
            $("#emergency-mobile").text(data.emergency_mobile);
            $("#emergency-email").text(data.emergency_email);
            $("#emergency-relation").text(data.emergency_relation);

            $("#doctor-name").text(data.doctor_name);
            $("#doctor-phone").text(data.doctor_phone);

            if (data.user_image) {
                $("#profile-image").attr("src", `backend/${data.user_image}`);
            }else{
                $("#profile-image").attr("src", `assets/image/No_image_available.svg`);

            }

            if (data.medical_doc) {
                // $(".docPreview").removeClass("d-none");

                $(".docPreview").removeClass("d-none");
                $("#docPreview").attr("src", `backend/${data.medical_doc}`);                
            }else{
                // $(".docPreview").addClass("d-none");


                $(".docPreview").addClass("d-none");
            }


            if (data.qr) {
                $("#qr-code").attr("src", `data:image/png;base64,${data.qr}`);
            } else {
                $("#qr-code").hide();
            }

        },
        error: function (xhr, status, error) {
            console.error("Error fetching user details:", error);
        }
    });
});
