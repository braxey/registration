const MAX_SLOTS_PER_USER = 6

$(function(){
    let form = document.getElementById('book-form')
    form.addEventListener('submit', function(e){
        let slotsRequested = parseInt($('#slots').val()) ?? 0
        e.preventDefault()
        // console.log(slotsRequested, slotsLeft, userSlots)

        if (slotsRequested > slotsLeft) {
            Swal.fire({
                title: 'Error',
                text: 'The requested number of slots is not available.',
                icon: 'error',
                confirmButtonText: 'OK'
            })
            return false
        }else if(slotsRequested+userSlots > MAX_SLOTS_PER_USER){
            Swal.fire({
                title: 'Error',
                text: 'You can only book '+MAX_SLOTS_PER_USER+' at a time.',
                icon: 'error',
                confirmButtonText: 'OK'
            })
            return false
        }
        form.submit()
    })
})