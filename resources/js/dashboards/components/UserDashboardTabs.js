export default class UserDashboardTabs {
    constructor() {
        this._userDashboardContainer = $('#user_dashboard_container')
        this._allAppointmentsTab = $('#all_appointments_tab')
        this._upcomingAppointmentsTab = $('#upcoming_appointments_tab')
        this._completedAppointmentsTab = $('#completed_appointments_tab')
        this._allAppointmentsTable = $('#all_appointments_table')
        this._upcomingAppointmentsTable = $('#upcoming_appointments_table')
        this._completedAppointmentsTable = $('#completed_appointments_table')

        this._initialize()
    }

    _initialize() {
        if (this._userDashboardContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._allAppointmentsTabListener()
            ._upcomingAppointmentsTabListener()
            ._completedAppointmentsTabListener()
    }

    _allAppointmentsTabListener() {
        this._allAppointmentsTab.on('click', (e) => {
            this._allAppointmentsTable.show()
            this._upcomingAppointmentsTable.hide()
            this._completedAppointmentsTable.hide()

            this._allAppointmentsTab.addClass('active')
            this._upcomingAppointmentsTab.removeClass('active')
            this._completedAppointmentsTab.removeClass('active')
        })

        return this
    }

    _upcomingAppointmentsTabListener() {
        this._upcomingAppointmentsTab.on('click', (e) => {
            this._allAppointmentsTable.hide()
            this._upcomingAppointmentsTable.show()
            this._completedAppointmentsTable.hide()

            this._allAppointmentsTab.removeClass('active')
            this._upcomingAppointmentsTab.addClass('active')
            this._completedAppointmentsTab.removeClass('active')
        })

        return this
    }

    _completedAppointmentsTabListener() {
        this._completedAppointmentsTab.on('click', (e) => {
            this._allAppointmentsTable.hide()
            this._upcomingAppointmentsTable.hide()
            this._completedAppointmentsTable.show()

            this._allAppointmentsTab.removeClass('active')
            this._upcomingAppointmentsTab.removeClass('active')
            this._completedAppointmentsTab.addClass('active')
        })

        return this
    }
}
