const MAX_SLOTS_PER_USER = 6
const MAX_CHAR_PER_STRING = 4

$(function(){
    let form = document.getElementById('book-form')
    form.addEventListener('submit', function(e){
        let slotsRequested = $('#slots').val().toString()
        e.preventDefault()

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
        if (slotsRequested > slotsLeft) {
            errorPop('Error', 'The requested number of slots is not available.')
            return false
        // make sure the user doesn't surpass the max allowed per user
        }else if(slotsRequested+userSlots > MAX_SLOTS_PER_USER){
            errorPop('Error', 'You can only book '+MAX_SLOTS_PER_USER+' slots at a time.')
            return false
        }
        form.submit()
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