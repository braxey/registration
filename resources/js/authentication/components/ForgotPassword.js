import { axios } from '../../bootstrap'

export default class ForgotPassword {
    constructor() {
        this._forgotPasswordContainer = $('#forgot_password_container')
        this._forgotPasswordForm = $('#forgot_password_form')
        this._emailInput = $('#email')
        this._noAccountError = $('#no_account_error')

        this._initialize()
    }

    _initialize() {
        if (this._forgotPasswordContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._emailChangeListener()
            ._emailSubmissionListener()
    }

    _emailSubmissionListener() {
        this._forgotPasswordForm.on('submit', (e) => {
            e.preventDefault()

            axios.post(this._forgotPasswordForm.attr('action'), this._forgotPasswordForm.serialize())
                .then((response) => {
                    window.location.href = '/forgot-password/verify-email';
                })
                .catch((error) => {
                    if (error.response && error.response.status === 400) {
                        let errorMessage = error.response.data.message;
                        if (errorMessage === 'No user found') {
                            this._noAccountError.show()
                        }
                    }
                })
        })

        return this
    }

    _emailChangeListener() {
        this._emailInput.on('input', () => {
            this._noAccountError.hide()
        })

        return this
    }
}
