import { errorPopup } from '../../utils/swalUtils'

export default class CreateBooking {
    constructor() {
        this._createBookingContainer = $('#create_booking_container')
        this._createBookingForm = $('#create_booking_form')
        this._slotsInput = $('#slots')
        this._slotsRemaining = Math.max(parseInt($('#slots_remaining').val()), 0)
        this._currentNumberOfSlots = parseInt($('#current_number_of_slots').val())
        this._maxSlotsPerUser = parseInt($('#max_slots').val())
        this._submitting = false

        this._initialize()
    }

    _initialize() {
        if (this._createBookingContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._formSubmissionListener()
    }

    _formSubmissionListener() {
        this._createBookingForm.on('submit', (e) => {
            if (this._submitting === true) {
                return
            }

            e.preventDefault()

            let slots = Number(this._slotsInput.val().trim())

            if (!Number.isInteger(slots) || slots <= 0) {
                return errorPopup('Error', 'Please enter a valid number')
            }

            if (slots > this._slotsRemaining) {
                let message = 'The requested number of slots is not available. There are only ' + this._slotsRemaining + ' open slots for this time'
                if (this._slotsRemaining === 1) {
                    message = 'The requested number of slots is not available. There is only ' + this._slotsRemaining + ' open slot for this time'
                } else if (this._slotsRemaining === 0) {
                    message = 'There are no slots remaining for this appointment'
                }

                return errorPopup('Error', message)
            }

            if (slots + this._currentNumberOfSlots > this._maxSlotsPerUser) {
                let message = 'You can only book ' + this._maxSlotsPerUser + ' slots at a time, and '
                let finish = 'you currently have no bookings'
                if (this._currentNumberOfSlots > 0) {
                    finish = 'you already have ' + this._currentNumberOfSlots + ' slots booked'
                }

                return errorPopup('Error', message + finish)
            }

            this._submitting = true
            this._createBookingForm.trigger('submit')
        })

        return this
    }
}
