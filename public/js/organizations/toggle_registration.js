$(function(){
    let form = document.getElementById('toggle-reg-form')
    let registrationClosed = $('#toggle-btn').text() == "Open"
    try{
        form.addEventListener('submit', function(e){
            e.preventDefault()

            // sweetalert for confirmation
            Swal.fire({
                title: 'Confirmation',
                text: ('Are you sure you want to '
                    + ((registrationClosed) ? 'open' : 'close')
                    + ' registration?'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: "#088708"
            }).then((result) => {
                // If the user confirms, submit the form
                if (result.isConfirmed) {
                    form.submit()
                }
            })
        })
    }catch(e){
        
    }
})
