$(function(){
    let form = document.getElementById('change-phone-form')
    form.addEventListener('submit', function(e){
        e.preventDefault()
        let phone = $('#phone_number').val().toString().replace(/[^0-9]/g, '')

        // make sure the phone number is valid
        if (phone.length == 11 && phone[0] == '1') {
            phone = phone.substring(1)
        }
        if (phone.length != 10) {
            showInvalidPhone()
            hidePhoneExists()
            return false
        }

        // Serialize the form data
        var formData = $(form).serialize();

        // Send the AJAX request
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            success: function() {
                window.location.href = '/verify-phone/verify'
            },
            error: function(xhr, status, error) {
                if (xhr.status === 400) {
                    var errorResponse = JSON.parse(xhr.responseText);
                    var errorMessage = errorResponse.message;
                    if (errorMessage === 'User exists') {
                        hideInvalidPhone()
                        showPhoneExists()
                    } else {
                        showInvalidPhone()
                        hidePhoneExists()
                    }
                } else {
                console.log('An error occurred:', error);
                }
            }
        })
    })

    function showInvalidPhone() {
        $('#invalid-number').show()
    }

    function hideInvalidPhone() {
        $('#invalid-number').hide()
    }

    function showPhoneExists() {
        $('#existing-account').show()
    }

    function hidePhoneExists() {
        $('#existing-account').hide()
    }
})
