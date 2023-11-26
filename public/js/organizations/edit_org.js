const MAX_CHAR_PER_STRING = 4

$(function () {
    let form = $('#update-form')
    
    form.on('submit', function (e) {
        e.preventDefault();
        if(!validateInput()) return false

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function() {
                confirmPop("Success", "The organization has been successfully updated.")
            },
            error: function() {
                errorPop("Error", "Form submission failed");
            }
        })
    })
})

function validateInput(){
    let max_slots = $('#max_slots_per_user').val().toString()
    return validNumberOfSlots(max_slots)
}

function validNumberOfSlots(slots){
    // make sure the input is an integer and the number isn't unreasonably big
    if (!isInteger(slots) || slots.length >= MAX_CHAR_PER_STRING){
        errorPop('Error', 'Must enter a valid number.')
        return false
    }

    // make sure the number is above 0
    slots = parseInt(slots)
    if (slots < 0) {
        errorPop('Error', 'The number must be greater than or equal to 0.')
        return false
    }
    return true
}

function isInteger(str) {
    if (typeof str != "string") return false // we only process strings!  
    return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
           !isNaN(parseFloat(str)) && // ...and ensure strings of whitespace fail
           !str.includes('.') // don't allow floats
}

function confirmPop(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: "success",
        confirmButtonText: 'OK',
        confirmButtonColor: "#088708"
    })
}

function errorPop(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: "#088708"
    })
}
