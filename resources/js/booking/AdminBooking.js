import '../bootstrap'
import UserLookup from './components/UserLookup'
import AdminEditBooking from './components/AdminEditBooking'
import StatusHighlighter from '../components/StatusHighlighter'

$(() => {
    new UserLookup()
    new AdminEditBooking()
    new StatusHighlighter()
})
