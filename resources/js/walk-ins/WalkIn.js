import '../bootstrap'
import Waitlist from './components/Waitlist'
import StatusHighlighter from '../components/StatusHighlighter'

$(() => {
    new Waitlist()
    new StatusHighlighter()
})
