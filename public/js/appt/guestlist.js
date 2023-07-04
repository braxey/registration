$(function(){
    var filterForm = document.getElementById("filter-form")

    $("#toggle-filter-button").on("click", function(e){
        e.preventDefault()
        $(".togglers").toggle()
        $("#filter-buttons").css('justify-content', ($("#filter-buttons").css('justify-content') == "center"
            ? "left"
            : "center"))
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
})

function clearForm(){
    $("#guest_name").val('')
    $("#start_date").val('')
    $("#start_time").val('')
    $("#appointment_name").val('')
    $("#status").val('')
}
