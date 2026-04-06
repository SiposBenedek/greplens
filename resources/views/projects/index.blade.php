@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Projects</h4>
            <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">New project</a>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 rules-table">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Project</th>
                        <th>Last seen</th>
                        <th>Status</th>
                        <th>Created by</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr class="rule-row" data-href="{{ route('projects.show', $project) }}" style="cursor: pointer;">
                            <td>
                                <span class="fw-semibold">{{ $project->name }}</span>
                                @if ($project->description)
                                    <br><span class="text-muted small">{{ Str::limit($project->description, 80) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($project->last_seen_at)
                                    <span class="text-muted small">{{ $project->last_seen_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-muted small">Never</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $project->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $project->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><span class="text-muted small">{{ $project->creator?->name }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                No projects yet. <a href="{{ route('projects.create') }}" class="text-decoration-none" style="color: var(--blue-1);">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-muted small mt-3">{{ $projects->count() }} {{ Str::plural('project', $projects->count()) }}</div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.rule-row').forEach(row => {
            row.addEventListener('click', () => {
                window.location.href = row.dataset.href;
            });
        });
    </script>
@endpush
