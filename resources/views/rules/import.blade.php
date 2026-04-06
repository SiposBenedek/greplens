@extends('partials.layout')

@section('content')
<div class="container mt-4" style="max-width: 720px;">
    <h1>Rule Import</h1>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Instructions --}}
    <div class="mb-4">
        <div class="">
            <h6 class="">Expected zip structure</h6>
            <pre class="mb-0"><code>rules.zip
├── Group 1/
│   ├── Rule 1.yaml
│   └── Rule 2.yaml
└── Group 2/
    ├── Rule 3.yaml
    └── Rule 4.yaml
</code></pre>
            <small class="text-muted d-block mt-2">
                Top-level folders become rule groups. Each <code>.yaml</code> file becomes one rule.
            </small>
        </div>
    </div>

    {{-- Upload form --}}
    <form action="{{ route('rules.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
        @csrf
        <div class="mb-3">
            <label for="zip_file" class="form-label">Upload Zip File</label>
            <input
                type="file"
                class="form-control @error('zip_file') is-invalid @enderror"
                id="zip_file"
                name="zip_file"
                accept=".zip"
                required
            >
            @error('zip_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary" id="submitBtn">
            Import Rules
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing…';
    });
</script>
@endpush