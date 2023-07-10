const MAX_CHAR_PER_STRING = 4

$(function(){
    var form = document.getElementById('update-form')
    
    // Add a submit event listener to the form
    form.addEventListener('submit', function(e) {
        // Prevent the default form submission
        e.preventDefault();

        // Don't submit if the input is flawed
        if(!validateInput()) return false

        // Serialize the form data
        var formData = $(form).serialize();

        // Send the AJAX request
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            success: function(response) {
                // Handle successful response
                confirmPop("Success", "The organization has been successfully updated.")
            },
            error: function(xhr, status, error) {
                // Handle error response
                errorPop("Error", "Form submission failed");
            }
        })
    })
})

function validateInput(){
    //org name
    let org_name = $('#org_name').val().toString()
    let res = validateText(org_name)
    if(res[0]==false) {
        callErrorPop("organization name", res[1])
        return false
    }

    // number of max slots per user
    let max_slots = $('#max_slots_per_user').val().toString()
    if(!validNumberOfSlots(max_slots)) return false

    return true
}

// 0 - too long
// 1 - quotation mark included
// 2 - includes <script>
// 3 - changed by decoding
// 4 - clean
function validateText(str){
    if(str.length > 200) return [false, 0]
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
