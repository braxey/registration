import { axios } from '../../bootstrap'

export default class UserLookup {
    constructor() {
        this._landingContainer = $('#user_bookings_landing_container')
        this._lookupForm = $('#user_bookings_lookup_form')
        this._nameInput = $('#name')
        this._resultsContainer = $('#user_bookings_results_container')

        this._initialize()
    }

    _initialize() {
        if (this._landingContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._lookupNameChangeListener()
    }

    _lookupNameChangeListener() {
        this._nameInput.on('keyup', (e) => {
            axios.post(this._lookupForm.attr('action'), {
                name: this._nameInput.val().trim()
            })
            .then((response) => {
                this._resultsContainer.html(response.data)
            })
        })

        return this
    }
}
