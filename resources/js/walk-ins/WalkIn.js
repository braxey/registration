import '../bootstrap'
import Waitlist from './components/Waitlist'
import CreateWalkIn from './components/CreateWalkIn'
import EditWalkIn from './components/EditWalkIn'
import StatusHighlighter from '../components/StatusHighlighter'

$(() => {
    new Waitlist()
    new CreateWalkIn()
    new EditWalkIn()
    new StatusHighlighter()
})
