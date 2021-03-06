@extends('layout.auth');

@section('content')

    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                <div class="col-lg-7">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                        </div>
                        <form class="user" method="post" action="register">
                            {{csrf_field()}}
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user" id="firstName"
                                           name="firstName" value="{{ old('firstName') }}"
                                           placeholder="First Name">
                                    @if(session('errors'))
                                        <span class="error"> {{session('errors')->first('firstName') }} </span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-user" id="lastName"
                                           name="lastName" value="{{ old('lastName') }}"
                                           placeholder="Last Name">
                                    @if(session('errors'))
                                        <span class="error"> {{session('errors')->first('lastName') }} </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user" id="emailAddress"
                                       name="email" value="{{ old('email') }}"
                                       placeholder="Email Address">
                                @if(session('errors'))
                                    <span class="error"> {{session('errors')->first('email') }} </span>
                                @endif
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="password" class="form-control form-control-user"
                                           id="password" placeholder="Password" name="password">
                                    @if(session('errors'))
                                        <span class="error"> {{session('errors')->first('password') }} </span>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control form-control-user"
                                           id="RepeatPassword" placeholder="Repeat Password" name="passwordRepeat">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Register Account
                            </button>
                            <hr>
                            <a href="/auth/google/redirect" class="btn btn-google btn-user btn-block">
                                <i class="fab fa-google fa-fw"></i> Register with Google
                            </a>
                            <a href="/auth/fb/redirect" class="btn btn-facebook btn-user btn-block">
                                <i class="fab fa-facebook-f fa-fw"></i> Register with Facebook
                            </a>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a class="small" href="/forgot_password">Forgot Password?</a>
                        </div>
                        <div class="text-center">
                            <a class="small" href="/login">Already have an account? Login!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
