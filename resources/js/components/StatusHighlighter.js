import DataTable from 'datatables.net-dt'
import 'datatables.net-dt/css/dataTables.dataTables.min.css'

export default class StatusHighlighter {
    constructor() {
        this._table = $('.appt-pagination')

        this._initialize()
    }

    _initialize() {
        this._initializeTable()
            ._highlight()
    }

    _initializeTable() {
        if (this._table.length) {
            new DataTable('.appt-pagination', {
                paging: true,           // Enable pagination
                pageLength: 20,         // Number of rows per page
                lengthChange: false,    // Disable the option to change the number of rows per page
                order: [],              // Disable automatic sorting
                searching: false,       // Disable search option
                ordering: false,        // Disable column sorting
            }).on('draw.dt', (e) => {
                this._highlight()
            })
        }

        return this
    }

    _highlight() {
        $('.highlight').each(function() {
            let $_this = $(this)
            let status = $_this.text()
    
            if (status === 'completed') {
                return $_this.css('background-color', '#fc035a')
            }
            
            if (status === 'upcoming') {
                return $_this.css('background-color', '#03adfc')
            }
            
            if (status === 'in progress') {
                return $_this.css('background-color', '#32ba20')
            }
        })
    }
}
