$(function(){
    let form = document.getElementById('verify-form')
    form.addEventListener('submit', function(e){
        let token = $('#token').val().toString()
        e.preventDefault()

        // make sure the input a string of 7 digits
        if (!isSevenDigits(token)){
            showWrongTokenMessage()
            return false
        }

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
            error: function() {
                showWrongTokenMessage()
            }
        })
    })

    function isSevenDigits(str) {
        // Check if the trimmed string is 7 digits long
        return (str.trim()).length === 7;
    }

    function showWrongTokenMessage() {
        $('#wrong-token').show()
    }
})
