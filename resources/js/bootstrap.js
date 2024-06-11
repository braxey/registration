import $ from 'jquery'
import axios from 'axios'

window.$ = window.jQuery = $
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export { axios }
