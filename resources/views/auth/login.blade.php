@extends('partials.layout')

@section('title', 'Login - Greplens')

@section('content')
    <div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 56px);">
        <div class="w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
                <img src="{{ asset('images/greplens.svg') }}" alt="Greplens" height="48">
                <h4 class="mt-3 mb-1">Welcome back</h4>
                <p class="text-muted small">Sign in to your account</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label text-muted small text-uppercase fw-semibold">Email</label>
                    <input type="email" name="email" id="email"
                        class="form-control bg-transparent text-white border-secondary @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label text-muted small text-uppercase fw-semibold">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control bg-transparent text-white border-secondary @error('password') is-invalid @enderror"
                        required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted small" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </form>

            <p class="text-center text-muted small mt-3">
                Contact your administrator to get an account.
            </p>
        </div>
    </div>
@endsection
