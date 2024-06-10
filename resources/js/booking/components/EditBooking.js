import { errorPopup } from '../../utils/swalUtils'
import Swal from 'sweetalert2'

export default class EditBooking {
    constructor() {
        this._editBookingContainer = $('#edit_booking_container')
        this._editBookingForm = $('#edit_booking_form')
        this._cancelBookingForm = $('#cancel_booking_form')
        this._slotsInput = $('#slots')
        this._slotsRemaining = Math.max(parseInt($('#slots_remaining').val()), 0)
        this._currentNumberOfSlots = parseInt($('#current_number_of_slots').val())
        this._slotsForAppointment = parseInt($('#slots_for_appointment').val())
        this._maxSlotsPerUser = parseInt($('#max_slots').val())
        this._submitting = false

        this._initialize()
    }

    _initialize() {
        if (this._editBookingContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._editFormSubmissionListener()
            ._cancelFormSubmissionListener()
    }

    _editFormSubmissionListener() {
        this._editBookingForm.on('submit', (e) => {
            if (this._submitting === true) {
                return
            }

            e.preventDefault()

            let slots = Number(this._slotsInput.val().trim())

            if (!Number.isInteger(slots) || slots < 0) {
                return errorPopup('Error', 'Please enter a valid number')
            }

            if (slots - this._slotsForAppointment > this._slotsRemaining) {
                let message = 'The requested number of slots is not available. There are only ' + this._slotsRemaining + ' open slots for this time'
                if (this._slotsRemaining === 1) {
                    message = 'The requested number of slots is not available. There is only ' + this._slotsRemaining + ' open slot for this time'
                } else if (this._slotsRemaining === 0) {
                    message = 'There are no slots remaining for this appointment'
                }

                return errorPopup('Error', message)
            }

            if (slots + this._currentNumberOfSlots - this._slotsForAppointment > this._maxSlotsPerUser) {
                let message = 'You can only book ' + this._maxSlotsPerUser + ' slots at a time, and '
                let finish = 'you currently have no bookings'
                if (this._currentNumberOfSlots > 0) {
                    finish = 'you already have ' + this._currentNumberOfSlots + ' slots booked'
                }

                return errorPopup('Error', message + finish)
            }

            Swal.fire({
                title: 'Confirmation',
                text: (slots === 0) 
                    ? 'Are you sure you want to cancel your appointment?'
                    : 'Are you sure you want to update your booking to '+ slots + ' slots?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: "#088708"
            }).then((result) => {
                if (result.isConfirmed) {
                    this._submitting = true
                    this._editBookingForm.trigger('submit')
                }
            })
        })

        return this
    }

    _cancelFormSubmissionListener() {
        this._cancelBookingForm.on('submit', (e) => {
            if (this._submitting === true) {
                return
            }

            e.preventDefault()

            Swal.fire({
                title: 'Confirmation',
                text: 'Are you sure you want to cancel your appointment?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: "#088708"
            }).then((result) => {
                if (result.isConfirmed) {
                    this._submitting = true
                    this._cancelBookingForm.trigger('submit')
                }
            })
        })

        return this
    }
}
