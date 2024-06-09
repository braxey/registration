$(function(){
    $('#token').on('keyup', function () {
        hideWrongTokenMessage()
        hideRateLimitMessage()
    })

    const form = $('#verify-form')
    form.on('submit', function(e) {
        e.preventDefault()

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function() {
                window.location.href = '/forgot-password/reset'
            },
            error: function(xhr, status, error) {
                if (xhr.status === 400) {
                    showWrongTokenMessage()
                }
            }
        })
    })

    function showWrongTokenMessage() {
        $('#wrong-token').show()
        $('#rate-limit').hide()
    }

    function hideWrongTokenMessage() {
        $('#wrong-token').hide()
        $('#rate-limit').hide()
    }

    function hideRateLimitMessage() {
        $('#wrong-token').hide()
        $('#rate-limit').hide()
    }
})
