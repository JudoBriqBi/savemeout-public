$(document).ready(function () {
  // UTILITY FUNCTION -- START
  $(".cf").on("input", function () {
    $(this).val(function (_, val) {
      return val.charAt(0).toUpperCase() + val.slice(1).toLowerCase();
    });
  });

  $(".mob").on("input", function () {
    $(this).val(function (_, val) {
      return this.value.replace(/\D/g, "");
    });
  });

  $("select[name='any_disease']").on("change", function () {
    $(this).val(function (_, val) {
      if (val === "yes") {
        $("input[name='disease']")
          .prop("readonly", false)
          .prop("required", true);
      } else {
        $("input[name='disease']")
          .prop("readonly", true)
          .prop("required", false)
          .val("");
      }
      return val;
    });
  });

  $("select[name='any_allergies']").on("change", function () {
    $(this).val(function (_, val) {
      if (val === "yes") {
        $("input[name='allergies']")
          .prop("readonly", false)
          .prop("required", true);
      } else {
        $("input[name='allergies']")
          .prop("readonly", true)
          .prop("required", false)
          .val("");
      }
      return val;
    });
  });

  $("#userImage").on("change", function () {
    var input = this; // 'this' correctly refers to the input element
    var preview = $("#imagePreview"); // Use jQuery for consistency

    if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
        $(".imgPreview").removeClass("d-none");
        preview.attr("src", e.target.result);
      };

      reader.readAsDataURL(input.files[0]); // Read the file as Data URL
    } else {
      $(".imgPreview").addClass("d-none");
    }
  });

  $("#medicalDoc").on("change", function () {
    const file = this.files[0]; // Get selected file
    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
    const errorMessage = $('#file-error');

    if (file && file.size > maxSize) {
      errorMessage.text('File size must be 5MB or smaller.').show();
      $(this).val(''); // Clear file input
    } else {
      errorMessage.hide();
    }
    var input = this; // 'this' correctly refers to the input element
    var preview = $("#docPreview"); // Use jQuery for consistency

    if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function (e) {
        $(".docPreview").removeClass("d-none");
        preview.attr("src", e.target.result);
      };

      reader.readAsDataURL(input.files[0]); // Read the file as Data URL
    } else {
      $(".docPreview").addClass("d-none");
    }
  });
  // UTILITY FUNCTION --END

  // QUERY PARAM PROCESSING --START
  const urlParams = new URLSearchParams(window.location.search);
  const tagID = urlParams.get("tag_id");
  const tagPIN = urlParams.get("pin");

  if (tagID && tagPIN) {
    // Display tagID in the paragraph element
    $("#tagIDDisplay").text(tagID);
    $("#tagIDInput").val(tagID);
    // Display tagID in the paragraph element
    $("#tagPINDisplay").text(tagPIN);
    $("#tagPINInput").val(tagPIN);
  } else {
    // Show SweetAlert before redirecting
    Swal.fire({
      title: "Success!",
      text: "Invalid TagID",
      icon: "warning",
      timer: 2000, // Wait for 2 seconds before redirecting
      showConfirmButton: false,
    }).then(() => {
      window.location.href = `dashboard.html`;
    });
  }
  // QUERY PARAM PROCESSING --END

  // CHECKING IF TAG IS VALID AND LINKED TO USER
  $.ajax({
    url: `backend/check_user_link.php?tag_id=${tagID}`,
    type: "GET",
    success: function (response) {
      try {
        let res = JSON.parse(response);
        if (res.success !== "true") {
          Swal.fire({
            icon: "error",
            title: "Invalid Tag",
            text: "This tag is not linked to your account.",
            timer: 2000, // Wait for 2 seconds before redirecting
            confirmButtonText: "Link Tag",
          }).then(() => {
            window.location.href = "link.html"; // Redirect to link tag page
          });
        }
      } catch (e) {
        console.error("Invalid JSON response:", response);
      }
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Something went wrong. Please try again later.",
      });
    },
  });

  // TRY TO GET ALL THE DATA FROM THE LINKED TAG
  $.ajax({
    url: `backend/get_all_details.php?tag_id=${tagID}&pin=${tagPIN}`,
    type: "GET",
    success: function (response) {
      try {
        let res = JSON.parse(response);
        if (res.success == true) {
          // MAP ALL DATA
          const data = res.data;
          $("#first_name").val(data.user_first_name);
          $("#last_name").val(data.user_last_name);
          $("#gender").val(data.user_gender);
          $("#blood_group").val(data.medical_blood_group);
          $("#height").val(data.medical_height);
          $("#weight").val(data.medical_weight);
          $("#birth_date").val(data.user_birth_date);
          // Set random values
          $("#email").val(data.user_email);
          $("#mobile").val(data.user_mobile);

          // Emergency Contact
          $("#relation").val(data.emergency_relation);
          $("#emergency_first_name").val(data.emergency_first_name);
          $("#emergency_last_name").val(data.emergency_last_name);
          $("#emergency_mobile").val(data.emergency_mobile);
          $("#emergency_email").val(data.emergency_email);

          // Address Details
          $("#country").val(data.user_country);
          $("#state").val(data.user_state);
          $("#city").val(data.user_city);
          $("#pincode").val(data.user_pincode);
          $("#address").val(data.user_address);
          // Doc Details
          $("#doc_name").val(data.doctor_name);
          $("#doc_phone").val(data.doctor_phone);

          // Medical Details
          $("#any_allergies").val(data.medical_any_allergies);
          if (data.medical_any_allergies === "yes") {
            $("#allergies").prop("readonly", false).prop("required", true);
          } else {
            $("#allergies")
              .prop("readonly", true)
              .prop("required", false)
              .val("");
          }
          $("#allergies").val(data.medical_allergies);
          $("#any_disease").val(data.medical_any_disease);
          if (data.medical_any_disease === "yes") {
            $("#disease").prop("readonly", false).prop("required", true);
          } else {
            $("#disease")
              .prop("readonly", true)
              .prop("required", false)
              .val("");
          }
          $("#disease").val(data.medical_disease);

          // Simulate file upload (image preview won't work without actual user interaction)
          if (data.user_image != null) {
            $(".imgPreview").removeClass("d-none");
            $("#imagePreview").attr("src", `backend/${data.user_image}`);
            $("#userImage").attr("required", false);
          }
          if (data.medical_doc != null) {
            $(".docPreview").removeClass("d-none");
            $("#docPreview").attr("src", `backend/${data.medical_doc}`);  
            $("#medicalDoc").attr("required", false);
          }
        } else {
          if (res.errorCode == "NULLDATA") {
            // SET ALL TO NULL
            $("#profileForm").get(0).reset();
            $("input[name=tag_id]").val(tagID);
            $("input[name=tag_pin]").val(tagPIN);
          } else {
            sww();
          }
        }
      } catch (e) {
        sww();
      }
    },
    error: function (xhr, status, error) {

      sww();
    },
  });

  // UPDATING THE FORM
  $("#profileForm").submit(function (event) {
    event.preventDefault(); // Prevent default form submission
    var formData = new FormData(this); // Create FormData object
    $.ajax({
      url: "backend/put_all_details.php",
      type: "POST",
      data: formData,
      processData: false, // Required for file uploads
      contentType: false, // Required for file uploads
      success: function (response) {
        response = JSON.parse(response);

        if (response.success === true) {
          // Show SweetAlert before redirecting
          Swal.fire({
            title: "Success!",
            text: "Data has been saved successfully. Redirecting...",
            icon: "success",
            timer: 2000, // Wait for 2 seconds before redirecting
            showConfirmButton: false,
          }).then(() => {
            window.open(
              `view.html?tag_id=${tagID}&pin=${tagPIN}`,
              '_blank' // <- This is what makes it open in a new window.
            );
            // window.location.href = `view.html?tag_id=${encodeURIComponent(
            //   tagID
            // )}&pin=${encodeURIComponent(
            //   tagPIN
            // )}`;
          });
        } else {
          Swal.fire({
            title: "Error",
            text: "Something went wrong. Please try again.",
            icon: "error",
          });
        }
      },
      error: function (error) {
        Swal.fire({
          title: "Failed",
          text: "Failed to save data.",
          icon: "error",
        });
      },
    });
  });

  let sww = () =>
    Swal.fire({
      title: "Huh..!",
      text: "Something went wrong...",
      icon: "error",
      timer: 2000,
      showConfirmButton: false,
    }).then(() => {
      window.location.href = `dashboard.html`;
    });
});
