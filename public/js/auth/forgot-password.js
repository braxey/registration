$(function(){
    let form = document.getElementById('forgot-password-form')
    form.addEventListener('submit', function(e){
        e.preventDefault()
        let phone = $('#phone_number').val().toString().replace(/[^0-9]/g, '')

        // make sure the phone number is valid
        if (phone.length == 11 && phone[1] == '1') {
            phone = phone.substring(1)
        } else if (phone.length != 10) {
            showInvalidPhone()
            hidePhoneDoesNotExist()
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
                window.location.href = '/reset-verify-number?phone_number=' + encodeURIComponent(phone)
            },
            error: function() {
                hideInvalidPhone()
                showPhoneDoesNotExist()
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
