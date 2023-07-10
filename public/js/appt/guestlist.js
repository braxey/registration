$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': csrfToken
    }
})

$(function(){
    var filterForm = document.getElementById("filter-form")

    $("#toggle-filter-button").on("click", function(e){
        e.preventDefault()
        $(".togglers").toggle()
        $("#filter-buttons").css('justify-content', ($("#filter-buttons").css('justify-content') == "center"
            ? "left"
            : "center"))
        $("#toggle-filter-button").text(($("#filter-buttons").css('justify-content') == "center"
        ? "Close"
        : "Filter"))
    })

    $("#filter-clear-button").on("click", function(e) {
        e.preventDefault()
        clearForm()
        filterForm.submit()
    })

    $("#filter-apply-button").on('click', function(e){
        e.preventDefault()
        filterForm.submit()
    })

    // Listen for input event on number fields
    $('.showed-up-input').on('change', function() {
        const guestId = $(this).data('guest-id');
        const showedUpValue = $(this).val();

        // Send AJAX request to update the showed up value
        $.ajax({
            url: "/guestlist/update",
            type: 'POST',
            data: {
                guest_id: guestId,
                showed_up: showedUpValue
            },
            success: function(response) {
                let totalShowed = $('#totalShowed')
                totalShowed.text((parseInt(totalShowed.text())+response.countChange))
            }
        })
    })
})

function clearForm(){
    $("#first_name").val('')
    $("#last_name").val('')
    $("#start_date").val('')
    $("#start_time").val('')
    $("#status").val('')
}
