import { axios } from '../../bootstrap'
import Swal from 'sweetalert2'
import { errorPopup, successPopup } from '../../utils/swalUtils'

export default class EditWalkIn {
    constructor() {
        this._editWalkInContainer = $('#edit_walk_in_container')
        this._editWalkInForm = $('#edit_walk_in_form')
        this._deleteWalkInForm = $('#delete_walk_in_form')
        this._nameInput = $('#name')
        this._slotsInput = $('#slots')
        this._deleting = false

        this._initialize()
    }

    _initialize() {
        if (this._editWalkInContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._editFormSubmissionListener()
            ._deleteFormSubmissionListener()
    }

    _editFormSubmissionListener() {
        this._editWalkInForm.on('submit', (e) => {
            e.preventDefault()

            let name = this._nameInput.val().trim()
            if (name.length === 0) {
                return errorPopup('Error', 'Please enter a name')
            }

            let slots = Number(this._slotsInput.val().trim())
            if (!Number.isInteger(slots) || slots <= 0) {
                return errorPopup('Error', 'Please enter a valid number of slots')
            }

            axios.post(this._editWalkInForm.attr('action'), this._editWalkInForm.serialize())
                .then((response) => {
                    successPopup('Success', 'The walk-in has been successfully updated')
                })
                .catch((error) => {
                    errorPopup('Error', 'Form submission failed')
                })
        })

        return this
    }

    _deleteFormSubmissionListener() {
        this._deleteWalkInForm.on('submit', (e) => {
            if (this._deleting === true) {
                return
            }

            e.preventDefault()

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this walk-in!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                confirmButtonColor: "#088708"
            }).then((result) => {
                if (result.isConfirmed) {
                    this._deleting = true
                    this._deleteWalkInForm.trigger('submit')
                }
            })
        })

        return this
    }
}
