const MAX_CHAR_PER_STRING = 4

$(function(){
    var update_form = document.getElementById('update-form') ?? null
    var delete_form = document.getElementById('delete-form') ?? null
    var create_form = document.getElementById('create-form') ?? null
    var form = (update_form == null) ? create_form : update_form
    
    // Add a submit event listener to the form (update or create)
    form.addEventListener('submit', function(e) {
        // Prevent the default form submission
        e.preventDefault();

        // Don't submit if the input is flawed
        if(!validateInput()) return false

        // Serialize the form data
        var formData = $(form).serialize();

        (update_form != null) ?
            // Send the AJAX request
            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                success: function(response) {
                    // Handle successful response
                    confirmPop("Success", "The appointment has been successfully updated.")
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    errorPop("Error", "Form submission failed");
                }
            }):
            form.submit()
    })

    if(delete_form != null){
        // Add listener on delete
        delete_form.addEventListener("submit", function(e){
            e.preventDefault()
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this appointment!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if(result.isConfirmed){
                    delete_form.submit()
                }
            })
        })
    }
})

function validateInput(){
    //title
    let title = $('#title').val().toString()
    let res = validateText(title)
    if(res[0]==false) {
        callErrorPop("title", res[1])
        return false
    }

    //description
    let description = $('#description').val().toString()
    res = validateText(description)
    if(res[0]==false) {
        callErrorPop("description", res[1])
        return false
    }

    // times
    let start_time = new Date($('#start_time').val())
    let end_time = new Date($('#end_time').val())
    if(start_time >= end_time){
        errorPop("Error", "The start time must be before the end time.")
        return false
    }

    // number of slots
    let slots = $('#total_slots').val().toString()
    if(!validNumberOfSlots(slots)) return false

    try{
        // number of slots can't change after start time
        if(start_time < new Date() && parseInt(slots) != slotsTaken){
            errorPop("Error", "Can't change the total slots after the appointment start time.")
            return false
        }
    }catch(error){
        // is not the update form, so slotsTaken not defined
    }

    return true
}

// -1 - title empty
// 0 - too long
// 1 - quotation mark included
// 2 - includes <script>
// 3 - changed by decoding
// 4 - clean
function validateText(str){
    if(str.length == 0) return [false, -1]
    if(str.length > 50) return [false, 0]
    if(str.includes("\"")) return [false, 1]

    let strtmp = ""
    let res = [true, 4]

    // URL-decode
    try{
        strtmp = decodeURI(str)
        if(strtmp != str) {
            str = strtmp
            res = [false, 3]
        }
    }catch(URIError){
        // there could still be part of the string that can be decoded
        res = [false, 3]
    }

    // remove <script>
    strtmp = str.replace(new RegExp("<script>", "g"), "")
    if(strtmp != str) {
        str = strtmp
        res = [false, 2]
    }
    return res
}

function validNumberOfSlots(slots){
    // make sure the input is an integer and the number isn't unreasonably big
    if (!isInteger(slots) || slots.length >= MAX_CHAR_PER_STRING){
        errorPop('Error', 'Must enter a valid number.')
        return false
    }

    // make sure the number is above 0
    slots = parseInt(slots)
    if (slots < 0){
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

function callErrorPop(form_field, error_code){
    switch(error_code){
        case -1:
            errorPop("Error", "The "+form_field+" cannot be empty.")
            break
        case 0:
            errorPop("Error", "The "+form_field+" cannot be more than 50 characters.")
            break
        case 1:
            errorPop("Error", "The "+form_field+" cannot include quotation marks.")
            break
        case 3:
            errorPop("Error", "The "+form_field+" contains invalid characters.")
            break
        case 2:
            errorPop("Error", "The "+form_field+" is invalid.")
            break
    }
}

function confirmPop(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: "success",
        confirmButtonText: 'OK'
    })
}

function errorPop(_title, _text){
    Swal.fire({
        title: _title,
        text: _text,
        icon: 'error',
        confirmButtonText: 'OK'
    })
}
