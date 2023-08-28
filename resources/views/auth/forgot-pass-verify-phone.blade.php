<x-app-layout>
    <html>
        <head>
            <title>Verify Phone</title>
            <script src="{{asset('js/dist/jquery.min.js')}}"></script>
            <script src="{{asset('js/dist/sweetalert2.all.min.js')}}"></script>
            <script type="module" src="{{ asset('js/auth/forgot-pass-verify-phone.js') }}"></script>
            <link rel="stylesheet" href="{{asset('css/main.css')}}">
        </head>
        <body>
            <div class="flex justify-center items-center h-screen text-center">
                <div class="container form-container">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <h2><b>Verify Phone</b></h2>
                            <br>
                            <p>A verification code has been sent to: <b>{{$masked_phone}}</b></p>

                            <form action="{{ route('forgot-password.verify-phone-token') }}" method="POST" id="verify-form">
                                @csrf

                                <div class="form-group">
                                    <label for="token">Token</label>
                                    <input type="text" name="token" id="token" class="form-control">
                                </div>

                                <input type="hidden" name="valid" id="valid" value="{{ $valid_phone }}"/>

                                <p style="color:red; display:none" id="wrong-token">The entered token is incorrect.</p>

                                <p style="color:red; {{$valid_phone ? 'display:none' : ''}}" id="invalid-phone">Your phone number is invalid, so you will not be able to receive a code.</p>

                                <button class="grn-btn" type="submit">Verify</button>
                            </form>

                            <form action="{{ route('forgot-password.resend-verify-token') }}" method="POST">
                                @csrf
                                @method('POST')
                                <div class="form-group" style="text-align:center">
                                    <label for="resend">Do you need a new token?</label>
                                    <button type="submit" class="red-btn">Resend Token</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
</x-app-layout>