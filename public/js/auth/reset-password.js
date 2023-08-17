$(function(){
    let form = document.getElementById('reset-form')
    form.addEventListener('submit', function(e){
        let password = $('#password').val().toString()
        let password_confirmation = $('#password_confirmation').val().toString()

        e.preventDefault()

        if (password !== password_confirmation) {
            showPasswordsDontMatch()
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
                window.location.href = '/login'
            },
            error: function(xhr, status, error) {
                // Handle error response
                if (xhr.status === 400) {
                  var errorResponse = JSON.parse(xhr.responseText);
                  var errorMessage = errorResponse.message;
                  if (errorMessage === 'The password does not match the confirmation') {
                    showPasswordsDontMatch()
                  } else {
                    showInvalidPassword()
                  }
                } else {
                  console.log('An error occurred:', error);
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
})
