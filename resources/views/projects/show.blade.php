@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4" style="max-width: 720px;">

        <a href="{{ route('projects.index') }}" class="text-muted small text-decoration-none">← Back to projects</a>

        {{-- One-time API key reveal --}}
        @if (session('api_key'))
            <div class="alert mt-3 p-3" style="background-color: rgba(63, 114, 175, 0.15); border: 1px solid var(--blue-1); border-radius: 6px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-semibold small text-uppercase" style="color: var(--blue-1);">API Key</span>
                    <span class="badge bg-warning text-dark">Shown once</span>
                </div>
                <code id="api-key-value" class="d-block p-2 rounded" style="background-color: rgba(0,0,0,0.3); font-size: 0.85rem; word-break: break-all;">{{ session('api_key') }}</code>
                <p class="text-muted small mb-0 mt-2">Save this key now. It cannot be displayed again.</p>
            </div>
        @endif

        {{-- Project header --}}
        <div class="d-flex align-items-center justify-content-between mt-3 mb-2">
            <div class="d-flex align-items-center gap-3">
                <h4 class="mb-0">{{ $project->name }}</h4>
                <span class="badge {{ $project->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $project->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>

        @if ($project->description)
            <p class="text-muted">{{ $project->description }}</p>
        @endif

        {{-- Details --}}
        <div class="mt-4">
            <table class="table table-dark table-borderless mb-0" style="--bs-table-bg: transparent; font-size: 0.875rem;">
                <tbody>
                    @if ($project->url)
                        <tr>
                            <td class="text-muted" style="width: 140px;">Repository</td>
                            <td><a href="{{ $project->url }}" target="_blank" class="text-decoration-none" style="color: var(--blue-1);">{{ $project->url }}</a></td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Last seen</td>
                        <td>{{ $project->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created by</td>
                        <td>{{ $project->creator?->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $project->created_at->format('M j, Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Edit form --}}
        <div class="mt-4 pt-3" style="border-top: 1px solid rgba(63, 114, 175, 0.2);">
            <h6 class="text-muted small text-uppercase fw-semibold mb-3">Settings</h6>

            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="name" class="form-label text-muted small text-uppercase fw-semibold">Name</label>
                    <input type="text" name="name" id="name"
                        class="form-control bg-transparent text-white border-secondary @error('name') is-invalid @enderror"
                        value="{{ old('name', $project->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label text-muted small text-uppercase fw-semibold">Repository URL</label>
                    <input type="url" name="url" id="url"
                        class="form-control bg-transparent text-white border-secondary @error('url') is-invalid @enderror"
                        value="{{ old('url', $project->url) }}">
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label text-muted small text-uppercase fw-semibold">Description</label>
                    <textarea name="description" id="description" rows="2"
                        class="form-control bg-transparent text-white border-secondary @error('description') is-invalid @enderror">{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                            id="isActiveSwitch" {{ $project->is_active ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="isActiveSwitch">Active</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary">Save</button>
            </form>
        </div>

        {{-- Danger zone --}}
        <div class="mt-4 pt-3" style="border-top: 1px solid rgba(220, 53, 69, 0.3);">
            <h6 class="text-muted small text-uppercase fw-semibold mb-3">Danger zone</h6>

            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('projects.regenerate-key', $project) }}"
                    onsubmit="return confirm('This will invalidate the current API key. Continue?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning">Regenerate API key</button>
                </form>

                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                    onsubmit="return confirm('Delete this project and all its data? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete project</button>
                </form>
            </div>
        </div>
    </div>
@endsection
