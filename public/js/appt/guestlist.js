$(function(){
    // const filterContent = document.getElementById('filter-apply-button');
    // const clearFilterBtn = document.getElementById('filter-clear-button');
    var filterForm = document.getElementById("filter-form")

    $("#toggle-filter-button").on("click", function(e){
        e.preventDefault()
        $("#filter-inputs-container").toggle()
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
    $("#start_time").val('')
    $("#appointment_name").val('')
}
