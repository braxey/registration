$(function(){
    let form = document.getElementById('verify-form')
    form.addEventListener('submit', function(e){
        e.preventDefault()

        // Serialize the form data
        var formData = $(form).serialize();

        // Send the AJAX request
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.ref !== undefined) {
                    var ref = response.ref;
                    if (ref) {
                        // Redirect the user to the URL
                        window.location.href = '/' + ref;
                    } else {
                        // Handle the case where the response does not contain a valid URL
                        window.location.href = '/appointments';
                    }
                } else {
                    window.location.href = '/appointments';
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 400) {
                    var errorResponse = JSON.parse(xhr.responseText);
                    var errorMessage = errorResponse.message;
                    if (errorMessage === 'Invalid phone') {
                        showInvalidPhone()
                    } else {
                        showWrongTokenMessage()
                    }
                } else {
                console.log('An error occurred:', error);
                }
            }
        })
    })

    function showWrongTokenMessage() {
        $('#invalid-phone').hide()
        $('#wrong-token').show()
    }

    function showInvalidPhone() {
        $('#wrong-token').hide()
        $('#invalid-phone').show()
    }
})
