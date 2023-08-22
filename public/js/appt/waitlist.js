var allTab, unassignedTab, assignedTab;

$(function(){
    allTab = $("#all-tab")
    unassignedTab = $("#unassigned-tab")
    assignedTab = $("#assigned-tab")

    allTab.on("click", function(){
        showAll()
    })

    unassignedTab.on("click", function(){
        showunassigned()
    })

    assignedTab.on("click", function(){
        showassigned()
    })
})

function showAll(){
    // Hide/show tables
    $("#all-table").show()
    $("#unassigned-table").hide()
    $("#assigned-table").hide()

    // Set tab as active
    allTab.addClass('active')
    unassignedTab.removeClass('active')
    assignedTab.removeClass('active')
}

function showunassigned(){
    // Hide/show tables
    $("#all-table").hide()
    $("#unassigned-table").show()
    $("#assigned-table").hide()

    // Set tab as active
    allTab.removeClass('active')
    unassignedTab.addClass('active')
    assignedTab.removeClass('active')
}

function showassigned(){
    // Hide/show tables
    $("#all-table").hide()
    $("#unassigned-table").hide()
    $("#assigned-table").show()

    // Set tab as active
    allTab.removeClass('active')
    unassignedTab.removeClass('active')
    assignedTab.addClass('active')
}
