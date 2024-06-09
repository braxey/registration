import { axios } from '../../bootstrap'

export default class ForgotPasswordVerify {
    constructor() {
        this._forgotPasswordVerifyContainer = $('#forgot_password_verify_container')
        this._forgotPasswordVerifyForm = $('#forgot_password_verify_form')
        this._tokenInput = $('#token')
        this._wrongTokenError = $('#wrong_token_error')
        this._rateLimitError = $('#rate_limit_error')

        this._initialize()
    }

    _initialize() {
        if (this._forgotPasswordVerifyContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._tokenChangeListener()
            ._tokenSubmissionListener()
    }

    _tokenSubmissionListener() {
        this._forgotPasswordVerifyForm.on('submit', (e) => {
            e.preventDefault()

            if (!this._isValidToken()) {
                this._wrongTokenError.show()
                return this._rateLimitError.hide()
            }

            axios.post(this._forgotPasswordVerifyForm.attr('action'), this._forgotPasswordVerifyForm.serialize())
                .then((response) => {
                    window.location.href = '/forgot-password/reset';
                })
                .catch((error) => {
                    if (error.response && error.response.status === 400) {
                        this._wrongTokenError.show()
                        this._rateLimitError.hide()
                    }
                })
        })
    }

    _tokenChangeListener() {
        this._tokenInput.on('input', () => {
            this._wrongTokenError.hide()
            this._rateLimitError.hide()
        })

        return this
    }

    _isValidToken() {
        return /^\d{7}$/.test(this._tokenInput.val().trim());
    }
}
