@extends('partials.layout')

@section('title', "$code - Greplens")

@section('content')
    <div class="d-flex align-items-center justify-content-center text-center" style="min-height: calc(100vh - 56px);">
        <div>
            <h1 class="mb-0" style="font-size: 5rem; font-weight: 700; color: var(--blue-1); opacity: 0.6;">{{ $code }}</h1>
            <h4 class="mt-2 mb-2">{{ $title }}</h4>
            <p class="text-muted mb-4" style="max-width: 400px;">{{ $message }}</p>
            @auth
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('rules.index') }}"
                    class="btn btn-sm btn-outline-secondary me-2">Go back</a>
                <a href="{{ route('rules.index') }}" class="btn btn-sm btn-primary">Rules</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Sign in</a>
            @endauth
        </div>
    </div>
@endsection
