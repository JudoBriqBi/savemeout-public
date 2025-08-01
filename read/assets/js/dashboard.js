$(document).ready(function () {

    $(".linkTagBtn").click(function () {
        window.location.href = "link.html"; // Redirect to the tag linking page
    });

    $("#buyTagBtn").click(function () {
        window.location.href = "https://savemeout.com/en/"; // Redirect to buy tags
    });

    $(".recordsContainer").on("click", ".viewLink", function () {
        let tagId = $(this).attr("data");
        let pin = $(this).attr("data-pin");
        window.open(
            `view.html?tag_id=${tagId}&pin=${pin}`,
            '_blank' // <- This is what makes it open in a new window.
        );
        // window.location.href = `view.html?tag_id=${tagId}&pin=${pin}`;
    });

    $(".recordsContainer").on("click", ".editLink", function () {
        let tagId = $(this).attr("data");
        let pin = $(this).attr("data-pin");
        window.location.href = `entry.html?tag_id=${tagId}&pin=${pin}`; // Redirect to buy tags
    });

    $.ajax({
        url: `backend/get_all_linked.php`,
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (!data.success) {
                alert(data.error);
                return;
            }

            let recordsContainer = $(".recordsContainer");
            let recordsContainerMain = $(".recordsContainerMain");

            let emptyRecord = $("#emptyRecord");

            if (data.recordsTotal == 0) {
                emptyRecord.removeClass("d-none"); // Show no records message
                recordsContainerMain.addClass("d-none"); // Show no records message
            } else {
                emptyRecord.addClass("d-none"); // Hide message
                recordsContainerMain.removeClass("d-none"); // Show no records message
                recordsContainer.empty(); // Clear previous records
                // Loop through data and create cards
                data.data.forEach(record => {
                    let cardHtml = `
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 p-3">
                            <div class="card-body text-center">
                                <!-- User Image -->
                                <img src="backend/${record.image_path}" alt="User image" 
                                     onerror="this.onerror=null; this.src='assets/image/No_image_available.svg';" 
                                    class="user-img rounded-circle mb-3">
                
                                <!-- Tag Details -->
                                <h5 class="card-title text-primary">Tag ID: ${record.tag_id}</h5>
                               <!-- <p class="card-text"><strong>Unique ID:</strong> ${record.unique_id}</p> -->
                                <p class="card-text"><strong>PIN:</strong> ${record.pin}</p>
                                <p class="card-text"><strong>Created On:</strong> ${record.created_on}</p>
                
                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-center mt-3">
                                    <button class="viewLink btn btn-outline-primary btn-sm me-2" data=${record.tag_id} data-pin=${record.pin}>👁 View Tag</button>
                                    <button class="editLink btn btn-outline-secondary btn-sm" data=${record.tag_id} data-pin=${record.pin}>✏️ Edit Tag</button>
                                </div>
                            </div>
                        </div>
                    </div>`;


                    recordsContainer.append(cardHtml);
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching user details:", error);
        }
    });
});

