import { errorPopup } from '../../utils/swalUtils'

export default class CreateAppointment {
    constructor() {
        this._createAppointmentContainer = $('#create_appointment_container')
        this._createAppointmentForm = $('#create_appointment_form')
        this._slotsInput = $('#total_slots')
        this._submitting = false

        this._initialize()
    }

    _initialize() {
        if (this._createAppointmentContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._createFormSubmissionListener()
    }

    _createFormSubmissionListener() {
        this._createAppointmentForm.on('submit', (e) => {
            if (this._submitting === true) {
                return
            }

            e.preventDefault()

            let slots = Number(this._slotsInput.val().trim())
            if (!Number.isInteger(slots) || slots <= 0) {
                return errorPopup('Error', 'Please enter a valid number of slots')
            }

            this._submitting = true
            this._createAppointmentForm.trigger('submit')
        })

        return this
    }
}
