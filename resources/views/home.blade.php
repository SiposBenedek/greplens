@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Dashboard</h4>
        </div>

        {{-- Stats cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Projects</div>
                    <div class="stat-value">{{ $totalProjects }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Rules</div>
                    <div class="stat-value">{{ $activeRules }}<span class="stat-secondary"> / {{ $totalRules }}</span></div>
                    <div class="stat-hint">active</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">Total Findings</div>
                    <div class="stat-value">{{ number_format($totalFindings) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card h-100">
                    <div class="stat-label">By Severity</div>
                    <div class="d-flex gap-2 mt-2">
                        <span class="badge bg-danger">{{ $severityCounts['error'] }} error</span>
                        <span class="badge bg-warning text-dark">{{ $severityCounts['warning'] }} warn</span>
                        <span class="badge bg-info text-dark">{{ $severityCounts['info'] }} info</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="stat-card h-100">
                    <div class="stat-label mb-3">Quick Actions</div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('rules.create') }}" class="btn btn-sm btn-primary">New Rule</a>
                        <a href="{{ route('rules.import') }}" class="btn btn-sm btn-outline-secondary">Import Rules</a>
                        <a href="{{ route('projects.create') }}" class="btn btn-sm btn-outline-secondary">New Project</a>
                        <a href="{{ route('findings.index') }}" class="btn btn-sm btn-outline-secondary">View Findings</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent projects --}}
        @if ($recentProjects->isNotEmpty())
            <div class="stat-card">
                <div class="stat-label mb-3">Recent Projects</div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 rules-table">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Project</th>
                                <th>Findings</th>
                                <th>Last Seen</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentProjects as $project)
                                <tr style="cursor: pointer;" onclick="window.location='{{ route('projects.show', $project) }}'">
                                    <td class="fw-semibold">{{ $project->name }}</td>
                                    <td>{{ $project->findings_count ?? 0 }}</td>
                                    <td class="text-muted">{{ $project->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                                    <td>
                                        <span class="badge {{ $project->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $project->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="stat-card text-center py-5">
                <p class="text-muted mb-3">No projects yet. Create one to get started.</p>
                <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
            </div>
        @endif
    </div>
@endsection
