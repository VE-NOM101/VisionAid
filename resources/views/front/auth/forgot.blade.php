@include('front.auth.header')
<body class="hold-transition login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <a href="{{url('/')}}" class="h1"><b>Vision</b>Aid</a>
        </div>
        <div class="card-body">
        @include('front.auth.message')
          <p class="login-box-msg">You forgot your password? Here you can easily retrieve a new password.</p>
          <form action="{{ route('forgot') }}" method="POST">
            @csrf
            <div class="input-group mb-3">
                <input placeholder="Enter Email" type="email" name="email" class="form-control"
                                                    id="yourUsername" required value="{{old('ema il')}}">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">Request new password</button>
              </div>
              <!-- /.col -->
            </div>
          </form>
          <p class="mt-3 mb-1">
            <p class="form-label">Already have an account? <a href="login">Log in</a>
            </p>
          </p>
        </div>
        <!-- /.login-card-body -->
      </div>
    </div>

@include('front.auth.footer')
