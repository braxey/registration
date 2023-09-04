$(function(){
    let form = document.getElementById('forgot-password-form')
    form.addEventListener('submit', function(e){
        e.preventDefault()
        // let phone = $('#phone_number').val().toString().replace(/[^0-9]/g, '')

        // make sure the phone number is valid
        // if (phone.length == 11 && phone[0] == '1') {
        //     phone = phone.substring(1)
        // }
        // if (phone.length != 10) {
        //     showInvalidPhone()
        //     hidePhoneDoesNotExist()
        //     return false
        // }

        let email = $('#email').val().toString()
        // Serialize the form data
        var formData = $(form).serialize();

        // Send the AJAX request
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            success: function() {
                window.location.href = '/reset-verify-email?email=' + encodeURIComponent(email)
            },
            error: function(xhr, status, error) {
                if (xhr.status === 400) {
                    var errorResponse = JSON.parse(xhr.responseText);
                    var errorMessage = errorResponse.message;
                    if (errorMessage === 'No user found') {
                        hideInvalidPhone()
                        showPhoneDoesNotExist()
                    } else {
                        // showInvalidPhone()
                        // hidePhoneDoesNotExist()
                    }
                } else {
                // console.log('An error occurred:', error);
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

    function showPhoneDoesNotExist() {
        $('#no-account').show()
    }

    function hidePhoneDoesNotExist() {
        $('#no-account').hide()
    }
})
