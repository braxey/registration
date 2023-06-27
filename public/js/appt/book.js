const MAX_SLOTS_PER_USER = 6
const MAX_CHAR_PER_STRING = 4

$(function(){
    let book_form = document.getElementById('book-form') ?? null
    let edit_book_form = document.getElementById('edit-book-form') ?? null
    let form = (book_form != null ? book_form : edit_book_form)
    form.addEventListener('submit', function(e){
        let slotsRequested = $('#slots').val().toString()
        e.preventDefault()

        // if appt user slots isnt defined, set to 0
        if(book_form != null) var apptUserSlots = 0

        // can't book after appt started
        if(startTime < new Date()){
            errorPop('Error', (book_form != null ? "Can't book slots after the start time." : "Can't change slot amount after the start time."))
            return false
        }

        // make sure the input is an integer and the number isn't unreasonably big
        if (!isInteger(slotsRequested) || slotsRequested.length >= MAX_CHAR_PER_STRING){
            errorPop('Error', 'Must enter a valid number.')
            return false
        }

        // make sure the number is above 0
        slotsRequested = parseInt(slotsRequested)
        if (slotsRequested <= 0){
            errorPop('Error', 'The number must be above 0.')
            return false
        }

        // make sure there are enough slots for the appt to allot
        if (slotsRequested - apptUserSlots > slotsLeft) {
            let left = Math.max(slotsLeft, 0)
            errorPop('Error', left>0 ? 'The requested number of slots is not available. There are only '+slotsLeft+' open slots for this time.'
                                     : 'There are no slots remaining for this appointment.')
            return false
        // make sure the user doesn't surpass the max allowed per user
        }else if(slotsRequested+userSlots-apptUserSlots > MAX_SLOTS_PER_USER){
            errorPop('Error', 'You can only book '+MAX_SLOTS_PER_USER+' slots at a time.')
            return false
        }

        // form submission
        if(book_form != null)
            form.submit()
        else{
            Swal.fire({
                title: 'Confirmation',
                text: 'Are you sure you want to update your booking to '+ slotsRequested + ' slots?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                // If the user confirms, submit the form
                if (result.isConfirmed) {
                    form.submit()
                }
            })
        }
    })
})

function isInteger(str) {
    if (typeof str != "string") return false // we only process strings!  
    return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
           !isNaN(parseFloat(str)) && // ...and ensure strings of whitespace fail
           !str.includes('.') // don't allow floats
}

function errorPop(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: 'error',
        confirmButtonText: 'OK'
    })
}