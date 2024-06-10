import { axios } from '../../bootstrap'
import Swal from 'sweetalert2'
import 'font-awesome/css/font-awesome.min.css'

export default class Guestlist {
    constructor() {
        this._guestlistContainer = $('#guestlist_container')
        this._guestlistFilterForm = $('#guestlist_filter_form')
        this._filterButtonsContainer = $('#guestlist_filter_buttons_container')
        this._togglers = $('.togglers')
        this._toggleFilterButton = $('#guestlist_toggle_filter_button')
        this._clearFilterButton = $('#guestlist_clear_filter_button')
        this._applyFilterButton = $('#guestlist_apply_filter_button')
        this._firstNameInput = $('#first_name')
        this._lastNameInput = $('#last_name')
        this._startDateInput = $('#start_date')
        this._startTimeInput = $('#start_time')
        this._statusInput = $('#status')
        this._totalAttendance = $('#guestlist_total_attendance')
        this._attendanceInputs = $('.guestlist_attendance_input')
        this._noteIcons = $('.walk_in_note')

        this._initialize()
    }

    _initialize() {
        if (this._guestlistContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._filterListeners()
            ._attendanceListener()
            ._noteOpenListener()
    }

    _filterListeners() {
        this._toggleFilterButton.on('click', (e) => {
            e.preventDefault()

            this._togglers.toggle()

            let justify = 'center'
            let toggleText = 'Close'
            if (this._filterButtonsContainer.css('justify-content') === 'center') {
                justify = 'left'
                toggleText = 'Filter'
            }

            this._filterButtonsContainer.css('justify-content', justify)
            this._toggleFilterButton.text(toggleText)
        })

        this._clearFilterButton.on('click', (e) => {
            e.preventDefault()
            this._clearForm()
            this._guestlistFilterForm.trigger('submit')
        })

        this._applyFilterButton.on('click', (e) => {
            e.preventDefault()
            this._guestlistFilterForm.trigger('submit')
        })

        return this
    }

    _attendanceListener() {
        const that = this
        this._attendanceInputs.on('change', function () {
            const guestId = $(this).data('guest-id')
            const showedUpValue = $(this).val()
    
            axios.post('/guestlist/update', {
                guest_id: guestId,
                showed_up: showedUpValue
            }).then((response) => {
                const total = parseInt(that._totalAttendance.text())
                that._totalAttendance.text(total + response.data.countChange)
            })
        })

        return this
    }

    _noteOpenListener() {
        this._noteIcons.on('click', function () {
            const name = $(this).data('name')
            const notes = $(this).data('notes')

            Swal.fire({
                title: 'Notes for ' + name,
                html: '<b>' + notes + '</b>',
                icon: 'info',
                confirmButtonText: 'Close',
            })
        })
    }

    _clearForm() {
        this._firstNameInput.val('')
        this._lastNameInput.val('')
        this._startDateInput.val('')
        this._startTimeInput.val('')
        this._statusInput.val('')
    }
}
