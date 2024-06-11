<x-app-layout>
    <html>
        <head>
            <title>{{ __('Edit Organization') }}</title>
            <script type="text/javascript" src="{{ version(mix('js/ModifyOrganization.js')) }}"></script>
            <link rel="stylesheet" href="{{ version(mix('css/main.css')) }}">
        </head>
        <body>
            <div id="edit_organization_container" class="flex justify-center text-center">
                <div class="container form-container">
                    <h1>{{ __('Edit Organization') }}</h1>

                    <form action="{{ route('organization.update', ['organizationId' => $organization->getId()]) }}" method="POST" id="edit_organization_form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="org_name">{{ __('Organization Name') }}</label>
                            <input type="text" name="org_name" id="org_name" class="form-control" value="{{ $organization->getName() }}">
                        </div>

                        <div class="form-group">
                            <label for="max_slots_per_user">{{ __('Max Slots per User') }}</label>
                            <input type="text" name="max_slots_per_user" id="max_slots_per_user" class="form-control" value="{{ $organization->getMaxSlotsPerUser() }}" min="0">
                        </div>

                        <button type="submit" class="grn-btn">{{ __('Update') }}</button>
                    </form>
                    <form action="{{ route('organization.toggle-registration', ['organizationId' => $organization->getId()]) }}" method="POST" id="toggle_registration_form">
                        @csrf
                        @method('POST')
                        <div class="form-group flex flex-row justify-end">
                            <label for="toggle-reg" style="margin: auto 10px auto 0">{{ __('Registration:') }} </label>
                            <button id="toggle_registration_button" type="submit" value="{{ $organization->registrationIsOpen() }}"
                                class="{{ $organization->registrationIsOpen() ? 'red-btn' : 'grn-btn' }}"
                                >{{ $organization->registrationIsOpen() ? __('Close') : __('Open') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>
