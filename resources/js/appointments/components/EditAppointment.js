import { axios } from '../../bootstrap'
import Swal from 'sweetalert2'
import { errorPopup, successPopup } from '../../utils/swalUtils'

export default class EditAppointment {
    constructor() {
        this._editAppointmentContainer = $('#edit_appointment_container')
        this._editAppointmentForm = $('#edit_appointment_form')
        this._deleteAppointmentForm = $('#delete_appointment_form')
        this._slotsInput = $('#total_slots')
        this._deleting = false

        this._initialize()
    }

    _initialize() {
        if (this._editAppointmentContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._editFormSubmissionListener()
            ._deleteFormSubmissionListener()
    }

    _editFormSubmissionListener() {
        this._editAppointmentForm.on('submit', (e) => {
            e.preventDefault()

            let slots = Number(this._slotsInput.val().trim())
            if (!Number.isInteger(slots) || slots <= 0) {
                return errorPopup('Error', 'Please enter a valid number of slots')
            }

            axios.post(this._editAppointmentForm.attr('action'), this._editAppointmentForm.serialize())
                .then((response) => {
                    successPopup('Success', 'The appointment has been successfully updated')
                })
                .catch((error) => {
                    errorPopup('Error', 'Form submission failed')
                })
        })

        return this
    }

    _deleteFormSubmissionListener() {
        this._deleteAppointmentForm.on('submit', (e) => {
            if (this._deleting === true) {
                return
            }

            e.preventDefault()

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this appointment!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                confirmButtonColor: "#088708"
            }).then((result) => {
                if (result.isConfirmed) {
                    this._deleting = true
                    this._deleteAppointmentForm.trigger('submit')
                }
            })
        })

        return this
    }
}
