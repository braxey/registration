<html>
<head>
<script src="{{ asset('node_modules/sweetalert2/dist/sweetalert2.all.js') }}"></script>
<script src="{{ asset('node_modules/jquery/dist/jquery.min.js') }}"></script>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Book Slots</h2>

            <form action="{{ route('appointment.book', $appointment) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="slots">Slots</label>
                    <input type="number" name="slots" id="slots" class="form-control" min="1" max="{{ $availableSlots }}">
                </div>

                <button type="submit" class="btn btn-primary" id="form-button">Book</button>
            </form>
            <script>
                var slotsLeft = "<?php echo $availableSlots;?>"
            </script>
            <script src="{{ asset('appointments/book.js') }}"></script>
        </div>
    </div>
</div>
</body>
</html>







