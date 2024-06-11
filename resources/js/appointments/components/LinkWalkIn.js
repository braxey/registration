export default class LinkWalkIn {
    constructor() {
        this._linkingContainer = $('#appointments_linking_container')
        this._applyFilterButton = $('#linking_filter_apply_button')
        this._resetFilterButton = $('#linking_filter_reset_button')
        this._startDateInput = $('#start_date')
        this._startTimeInput = $('#start_time')
        this._endDateInput = $('#end_date')
        this._endTimeInput = $('#end_time')

        this._initialize()
    }

    _initialize() {
        if (this._linkingContainer.length) {
            this._listen()
        }
    }

    _listen() {
        this._filterListeners()
    }

    _filterListeners() {
        this._resetFilterButton.on('click', (e) => {
            this._clearForm()
            this._applyFilterButton.trigger('click')
        })

        return this
    }

    _clearForm() {
        this._startDateInput.val('')
        this._startTimeInput.val('')
        this._endDateInput.val('')
        this._endTimeInput.val('')
    }
}
