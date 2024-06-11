import '../bootstrap'
import Landing from './components/Landing'
import CreateAppointment from './components/CreateAppointment'
import EditAppointment from './components/EditAppointment'
import Guestlist from './components/Guestlist'
import LinkWalkIn from './components/LinkWalkIn'
import StatusHighlighter from '../components/StatusHighlighter'

$(() => {
    new Landing()
    new CreateAppointment()
    new EditAppointment()
    new Guestlist()
    new LinkWalkIn()
    new StatusHighlighter()
})
