@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Findings</h4>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 rules-table">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Project</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Errors</th>
                        <th class="text-center">Warnings</th>
                        <th class="text-center">Info</th>
                        <th>Last seen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr class="rule-row" data-href="{{ route('findings.show', $project) }}" style="cursor: pointer;">
                            <td>
                                <span class="fw-semibold">{{ $project->name }}</span>
                                @if ($project->description)
                                    <br><span class="text-muted small">{{ Str::limit($project->description, 80) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold">{{ $project->findings_count }}</span>
                            </td>
                            <td class="text-center">
                                @if ($project->error_count > 0)
                                    <span class="badge bg-danger">{{ $project->error_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($project->warning_count > 0)
                                    <span class="badge bg-warning text-dark">{{ $project->warning_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($project->info_count > 0)
                                    <span class="badge bg-info text-dark">{{ $project->info_count }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $project->last_seen_at?->diffForHumans() ?? 'Never' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No findings yet. Connect a project and run a scan to see results here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
