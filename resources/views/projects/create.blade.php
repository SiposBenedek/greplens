@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4" style="max-width: 600px;">

        <a href="{{ route('projects.index') }}" class="text-muted small text-decoration-none">← Back to projects</a>
        <h4 class="mt-2 mb-4">New project</h4>

        <form method="POST" action="{{ route('projects.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label text-muted small text-uppercase fw-semibold">Name</label>
                <input type="text" name="name" id="name"
                    class="form-control bg-transparent text-white border-secondary @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" required autofocus
                    placeholder="Application name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="url" class="form-label text-muted small text-uppercase fw-semibold">App URL <span class="fw-normal">(optional)</span></label>
                <input type="url" name="url" id="url"
                    class="form-control bg-transparent text-white border-secondary @error('url') is-invalid @enderror"
                    value="{{ old('url') }}"
                    placeholder="https://your-app.com">
                @error('url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="form-label text-muted small text-uppercase fw-semibold">Description <span class="fw-normal">(optional)</span></label>
                <textarea name="description" id="description" rows="2"
                    class="form-control bg-transparent text-white border-secondary @error('description') is-invalid @enderror"
                    placeholder="What does this project do?">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create project</button>
        </form>
    </div>
@endsection
