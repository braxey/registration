$(function(){
    const form = $('#forgot-password-form')

    $('#email').on('keyup', function () {
        hideAccountDoesNotExist()
    })

    form.on('submit', function(e){
        e.preventDefault()
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function() {
                window.location.href = '/forgot-password/verify-email'
            },
            error: function(xhr, status, error) {
                if (xhr.status === 400) {
                    var errorResponse = JSON.parse(xhr.responseText);
                    var errorMessage = errorResponse.message;
                    if (errorMessage === 'No user found') {
                        hideAccountDoesNotExist()
                        showAccountDoesNotExist()
                    }
                }
            }
        })
    })

    function showAccountDoesNotExist() {
        $('#no-account').show()
    }

    function hideAccountDoesNotExist() {
        $('#no-account').hide()
    }
})
