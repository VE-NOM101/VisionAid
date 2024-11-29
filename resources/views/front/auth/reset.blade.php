@include('front.auth.header')

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="{{ url('/') }}" class="h1"><b>Vision</b>Aid</a>
            </div>
            <div class="card-body">
                @include('front.auth.message')

                <p class="login-box-msg">You are only one step a way from your new password, recover your password now.
                </p>
                <form action="{{ url('reset/' . $email . '/' . $token) }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <input placeholder="Email" type="email" name="email" class="form-control" id="yourUsername"
                            value="{{ $email }}" disabled required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input placeholder="Password" type="password" name="password" class="form-control"
                            id="yourPassword" required>
                        @if ($errors->first('password'))
                            <div class="alert alert-danger">{{ $errors->first('confirm_password') }}</div>
                        @endif
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group mb-3">
                            <input placeholder="Confirm Password" type="password" name="confirm_password"
                                class="form-control" id="yourCPassword" required>
                            @if ($errors->first('confirm_password'))
                                <div class="alert alert-danger">{{ $errors->first('confirm_password') }}</div>
                            @endif
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Change password</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

                <p class="form-label">Don't have account? <a href="{{ route('register') }}">Create an
                        account</a></p>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->
    @include('front.auth.footer')
