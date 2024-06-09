import { axios } from '../../bootstrap'

export default class NewPassword {
    constructor() {
        this._resetPasswordContainer = $('#reset_password_container')
        this._resetPasswordForm = $('#reset_password_form')
        this._passwordInput = $('#password')
        this._passwordConfirmationInput = $('#password_confirmation')
        this._noMatchError = $('#no_match_error')
        this._invalidPasswordError = $('#invalid_password_error')

        this._initialize()
    }

    _initialize() {
        if (this._resetPasswordContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._inputChangeListeners()
            ._passwordSubmissionListener()
    }

    _passwordSubmissionListener() {
        this._resetPasswordForm.on('submit', (e) => {
            e.preventDefault()

            let password = this._passwordInput.val().trim()
            let passwordConfirmation = this._passwordConfirmationInput.val().trim()

            if (password !== passwordConfirmation) {
                return this._noMatchError.show()
            }

            axios.post(this._resetPasswordForm.attr('action'), this._resetPasswordForm.serialize())
                .then((response) => {
                    window.location.href = '/login';
                })
                .catch((error) => {
                    if (error.response && error.response.status === 400) {
                        let errorMessage = error.response.data.message;
                        if (errorMessage === 'The password does not match the confirmation') {
                            this._noMatchError.show()
                            return this._invalidPasswordError.hide()
                        }

                        this._invalidPasswordError.show()
                        return this._noMatchError.hide()
                    }
                })
        })

        return this
    }

    _inputChangeListeners() {
        this._passwordInput.on('input', () => {
            this._noMatchError.hide()
            this._invalidPasswordError.hide()
        })

        this._passwordConfirmationInput.on('input', () => {
            this._noMatchError.hide()
            this._invalidPasswordError.hide()
        })

        return this
    }
}
