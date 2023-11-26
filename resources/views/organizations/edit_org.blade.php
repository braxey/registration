<x-app-layout>
    <html>
        <head>
            <title>Edit Organization</title>
            <script type="text/javascript" src="{{ version('js/dist/jquery.min.js') }}"></script>
            <script type="text/javascript" src="{{ version('js/dist/sweetalert2.all.min.js') }}"></script>
            <link rel="stylesheet" href="{{ version('css/main.css') }}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <h1>Edit Organization</h1>

                    <form action="{{ route('organization.update', ['organizationId' => $organization->getId()]) }}" method="POST" id="update-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="org_name">Organization Name</label>
                            <input type="text" name="org_name" id="org_name" class="form-control" value="{{ $organization->getName() }}">
                        </div>

                        <div class="form-group">
                            <label for="max_slots_per_user">Max Slots per User</label>
                            <input type="text" name="max_slots_per_user" id="max_slots_per_user" class="form-control" value="{{ $organization->getMaxSlotsPerUser() }}" min="0">
                        </div>

                        <button type="submit" class="grn-btn">Update</button>
                    </form>
                    <form action="{{ route('organization.toggle-registration', ['organizationId' => $organization->getId()]) }}" method="POST" id="toggle-reg-form">
                        @csrf
                        @method('POST')
                        <div class="form-group flex flex-row justify-end">
                            <label for="toggle-reg" style="margin: auto 10px auto 0">Registration: </label>
                            <button id="toggle-btn" type="submit" 
                                class="{{ $organization->registrationIsOpen() ? 'red-btn' : 'grn-btn' }}"
                                >{{ $organization->registrationIsOpen() ? 'Close' : 'Open' }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <script type="text/javascript" src="{{ version('js/organizations/edit_org.js') }}"></script>
            <script type="text/javascript" src="{{ version('js/organizations/toggle_registration.js') }}"></script>
        </body>
    </html>
</x-app-layout>
