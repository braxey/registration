export default class Waitlist {
    constructor() {
        this._waitlistContainer = $('#walk_ins_waitlist_container')
        this._allWalkInsTab = $('#all_walk_ins_tab')
        this._unassignedWalkInsTab = $('#unassigned_walk_ins_tab')
        this._assignedWalkInsTab = $('#assigned_walk_ins_tab')
        this._allWalkInsTable = $('#all_walk_ins_table')
        this._unassignedWalkInsTable = $('#unassigned_walk_ins_table')
        this._assignedWalkInsTable = $('#assigned_walk_ins_table')

        this._initialize()
    }

    _initialize() {
        if (this._waitlistContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._allWalkInsTabListener()
            ._unassignedWalkInsTabListener()
            ._assignedWalkInsTabListener()
    }

    _allWalkInsTabListener() {
        this._allWalkInsTab.on('click', (e) => {
            this._allWalkInsTable.show()
            this._unassignedWalkInsTable.hide()
            this._assignedWalkInsTable.hide()

            this._allWalkInsTab.addClass('active')
            this._unassignedWalkInsTab.removeClass('active')
            this._assignedWalkInsTab.removeClass('active')
        })

        return this
    }

    _unassignedWalkInsTabListener() {
        this._unassignedWalkInsTab.on('click', (e) => {
            this._allWalkInsTable.hide()
            this._unassignedWalkInsTable.show()
            this._assignedWalkInsTable.hide()

            this._allWalkInsTab.removeClass('active')
            this._unassignedWalkInsTab.addClass('active')
            this._assignedWalkInsTab.removeClass('active')
        })

        return this
    }

    _assignedWalkInsTabListener() {
        this._assignedWalkInsTab.on('click', (e) => {
            this._allWalkInsTable.hide()
            this._unassignedWalkInsTable.hide()
            this._assignedWalkInsTable.show()

            this._allWalkInsTab.removeClass('active')
            this._unassignedWalkInsTab.removeClass('active')
            this._assignedWalkInsTab.addClass('active')
        })

        return this
    }
}
