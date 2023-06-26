$(function(){
    let form = document.getElementById('cancel-form')
    try{
        form.addEventListener('submit', function(e){
            e.preventDefault()
            console.log('here')

            // sweetalert for confirmation
            Swal.fire({
                title: 'Confirmation',
                text: 'Are you sure you want to cancel your appointment?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                // If the user confirms, submit the form
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        })
    }catch(e){
        // no appointments to cancel
    }
})
