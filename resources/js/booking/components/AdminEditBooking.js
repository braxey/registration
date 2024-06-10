import EditBooking from './EditBooking'

export default class AdminEditBooking extends EditBooking {
    constructor() {
        super()
    }

    _slotLimitReachedMessage() {
        let message = 'Users can only book ' + this._maxSlotsPerUser + ' slots at a time, and '

        if (this._currentNumberOfSlots > 0) {
            return message + 'this user already has ' + this._currentNumberOfSlots + ' slots booked'
        }

        return message + 'this user currently has no bookings'
    }

    _editPrompt(slots) {
        if (slots === 0) {
            return 'Are you sure you want to cancel this appointment?'
        }

        return 'Are you sure you want to update this booking to '+ slots + ' slots?'
    }

    _cancelPrompt() {
        return 'Are you sure you want to cancel this appointment?'
    }
}
