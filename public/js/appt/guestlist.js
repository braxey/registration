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
})

function clearForm(){
    $("#first_name").val('')
    $("#last_name").val('')
    $("#start_date").val('')
    $("#start_time").val('')
    $("#status").val('')
}
