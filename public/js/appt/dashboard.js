var allTab, upcomingTab, pastTab;

$(function(){
    allTab = $("#all-tab")
    upcomingTab = $("#upcoming-tab")
    pastTab = $("#past-tab")

    allTab.on("click", function(){
        showAll()
    })

    upcomingTab.on("click", function(){
        showUpcoming()
    })

    pastTab.on("click", function(){
        showPast()
    })
})

function showAll(){
    // Hide/show tables
    $("#all-table").show()
    $("#upcoming-table").hide()
    $("#past-table").hide()

    // Set tab as active
    allTab.addClass('active')
    upcomingTab.removeClass('active')
    pastTab.removeClass('active')
}

function showUpcoming(){
    // Hide/show tables
    $("#all-table").hide()
    $("#upcoming-table").show()
    $("#past-table").hide()

    // Set tab as active
    allTab.removeClass('active')
    upcomingTab.addClass('active')
    pastTab.removeClass('active')
}

function showPast(){
    // Hide/show tables
    $("#all-table").hide()
    $("#upcoming-table").hide()
    $("#past-table").show()

    // Set tab as active
    allTab.removeClass('active')
    upcomingTab.removeClass('active')
    pastTab.addClass('active')
}
