import { errorPopup } from '../../utils/swalUtils'

export default class CreateWalkIn {
    constructor() {
        this._createWalkInContainer = $('#create_walk_in_container')
        this._createWalkInForm = $('#create_walk_in_form')
        this._nameInput = $('#name')
        this._slotsInput = $('#slots')
        this._submitting = false

        this._initialize()
    }

    _initialize() {
        if (this._createWalkInContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._createFormSubmissionListener()
    }

    _createFormSubmissionListener() {
        this._createWalkInForm.on('submit', (e) => {
            if (this._submitting === true) {
                return
            }

            e.preventDefault()

            let name = this._nameInput.val().trim()
            if (name.length === 0) {
                return errorPopup('Error', 'Please enter a name')
            }

            let slots = Number(this._slotsInput.val().trim())
            if (!Number.isInteger(slots) || slots <= 0) {
                return errorPopup('Error', 'Please enter a valid number of slots')
            }

            this._submitting = true
            this._createWalkInForm.trigger('submit')
        })

        return this
    }
}
