import { axios } from '../../bootstrap'
import { successPopup, errorPopup } from '../../utils/swalUtils'
import Swal from 'sweetalert2'

export default class EditOrganization {
    constructor() {
        this._editOrganizationContainer = $('#edit_organization_container')
        this._editOrganizationForm = $('#edit_organization_form')
        this._toggleRegistrationForm = $('#toggle_registration_form')
        this._maxSlotsPerUserInput = $('#max_slots_per_user')
        this._toggleRegistrationButton = $('#toggle_registration_button')
        this._registrationOpen = parseInt(this._toggleRegistrationButton.val()) === 1
        this._toggling = false

        this._initialize()
    }

    _initialize() {
        if (this._editOrganizationContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._editFormSubmissionListener()
            ._toggleRegistrationFormSubmissionListener()
    }

    _editFormSubmissionListener() {
        this._editOrganizationForm.on('submit', (e) => {
            e.preventDefault()

            if (!this._maxSlotsIsNumeric()) {
                return errorPopup('Error', 'Max slots per user must be an integer')
            }

            axios.post(this._editOrganizationForm.attr('action'), this._editOrganizationForm.serialize())
                .then((response) => {
                    successPopup('Success', 'The organization has been successfully updated')
                })
                .catch((error) => {
                    errorPopup('Error', 'Form submission failed')
                })
        })

        return this
    }

    _toggleRegistrationFormSubmissionListener() {
        this._toggleRegistrationForm.on('submit', (e) => {
            if (this._toggling === true) {
                return
            }

            e.preventDefault()

            if (!this._maxSlotsIsValid()) {
                return errorPopup('Error', 'Max slots per user must be an integer')
            }

            Swal.fire({
                title: 'Confirmation',
                text: 'Are you sure you want to ' + (this._registrationOpen ? 'close' : 'open')+ ' registration?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: "#088708"
            }).then((result) => {
                if (result.isConfirmed) {
                    this._toggling = true
                    this._toggleRegistrationForm.trigger('submit')
                }
            })
        })

        return this
    }

    _maxSlotsIsValid() {
        let maxSlots = Number(this._maxSlotsPerUserInput.val().trim())
        return Number.isInteger(maxSlots) && maxSlots > 0
    }
}
