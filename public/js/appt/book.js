$(function(){
    $("#form-button").on("click", function(){
        let slotsRequested = parseInt($('#slots').val()) ?? 0;
        if (slotsRequested > slotsLeft) {
            Swal.fire({
                title: 'Error',
                text: 'The requested number of slots is not available.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
})