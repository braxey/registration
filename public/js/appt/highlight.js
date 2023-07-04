$(function(){
    
    var table = $('.appt-pagination')
    
    if(table.length > 0){
        table.DataTable({
            paging: true,        // Enable pagination
            pageLength: 20,      // Number of rows per page
            lengthChange: false,  // Disable the option to change the number of rows per page
            order: [], // Disable automatic sorting
            searching: false,  // Disable search option
            ordering: false,   // Disable column sorting
        })
        // Event handler for table page change
        table.on('draw.dt', function() {
            highlight()
        });
        
    }

    highlight()
})

function highlight(){
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
}
