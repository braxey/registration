$(function(){
    const form = $('#reset-form')
    const passwordInput = $('#password')
    const passwordConfirmationInput = $('#password_confirmation')

    passwordInput.on('keyup', function () {
      hideErrors()
    })

    passwordConfirmationInput.on('keyup', function () {
      hideErrors()
    })

    form.on('submit', function (e) {
        e.preventDefault()
        let password = passwordInput.val().toString()
        let passwordConfirmation = passwordConfirmationInput.val().toString()

        if (password !== passwordConfirmation) {
            showPasswordsDontMatch()
            return false
        }

        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function () {
                window.location.href = '/login'
            },
            error: function (xhr, status, error) {
                if (xhr.status === 400) {
                  var errorResponse = JSON.parse(xhr.responseText)
                  var errorMessage = errorResponse.message
                  if (errorMessage === 'The password does not match the confirmation') {
                    showPasswordsDontMatch()
                  } else {
                    showInvalidPassword()
                  }
                }
            }
        })
    })

    function showPasswordsDontMatch() {
        $('#no-match').show()
        $('#invalid-password').hide()
    }

    function showInvalidPassword() {
        $('#no-match').hide()
        $('#invalid-password').show()
    }

    function hideErrors() {
      $('#no-match').hide()
      $('#invalid-password').hide()
    }
})
