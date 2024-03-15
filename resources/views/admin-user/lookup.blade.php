<x-app-layout>
    <html>
        <head>
            <title>Appointments - WTB Registration</title>
            <link rel="stylesheet" href="{{ version('css/main.css') }}">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
            <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        </head>
        <body>
            <div class="flex justify-center items-center h-screen">
                
                <div class="container" >
                    <form>
                        <div class="filter-container flex justify-center items-center h-screen mb-6 mt-6">
                            <div id="filter-inputs-container" class="form-container togglers">
                                <p class="mb-4 text-lg">Enter a user's name:</p>
                                <div class="form-group">
                                    <label for="name"></label>
                                    <div class="flex">
                                        <input type="text" name="name" id="name" class="form-control mr-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="tab-container appointment-table" id="lookup-results"></div>

                    <script>
                        $(document).ready(function () {
                            let csrfToken = "{{ csrf_token() }}"

                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })

                            let input = $('#name')
                            let result = $('#lookup-results')
                            let url = "{{ route('admin-booking.lookup') }}"

                            input.focus()
                            input.keyup(function () {
                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: {
                                        name: input.val()
                                    },
                                    success: function(html) {
                                        result.html(html)
                                    }
                                })
                            })
                        })
                    </script>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>