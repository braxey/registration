<x-app-layout>
    <html>
        <head>
            <title>Edit Organization</title>
            <script src="{{ asset('js/dist/jquery.min.js') }}"></script>
            <script src="{{ asset('js/dist/sweetalert2.all.min.js') }}"></script>
            <link rel="stylesheet" href="{{ asset('css/main.css') }}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <h1>Edit Organization</h1>

                    <form action="{{ route('organization.edit', ['id' => 1]) }}" method="POST" id="update-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="org_name">Organization Name</label>
                            <input type="text" name="org_name" id="org_name" class="form-control" value="{{ $organization->org_name }}">
                        </div>

                        <div class="form-group">
                            <label for="max_slots_per_user">Max Slots per User</label>
                            <input type="text" name="max_slots_per_user" id="max_slots_per_user" class="form-control" value="{{ $organization->max_slots_per_user }}" min="0">
                        </div>

                        <button type="submit" class="grn-btn">Update</button>
                    </form>
                </div>
            </div>
            <script type="module" src="{{ asset('js/organizations/edit_org.js') }}"></script>
        </body>
    </html>
</x-app-layout>