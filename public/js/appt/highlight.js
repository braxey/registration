$(function(){
    $('.highlight').each(function() {
        let text = $(this).text() // Get the text content of the span
        let bgColor = '' // Define the background color variable

        // Set the background color based on the status
        if (text === 'completed') {
            bgColor = '#fc035a'
        } else if (text === 'upcoming') {
            bgColor = '#03adfc'
        } else if (text === 'in progress') {
            bgColor = '#32ba20'
        }

        // Apply the background color to the span
        $(this).css('background-color', bgColor);
    })
})
