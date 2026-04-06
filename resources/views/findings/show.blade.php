@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4">

        <a href="{{ route('findings.index') }}" class="text-muted small text-decoration-none">&larr; Back to findings</a>

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mt-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $project->name }}</h4>
                <span class="text-muted small">Last scanned {{ $project->last_seen_at?->diffForHumans() ?? 'never' }}</span>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-danger">{{ $counts['error'] }} errors</span>
                <span class="badge bg-warning text-dark">{{ $counts['warning'] }} warnings</span>
                <span class="badge bg-info text-dark">{{ $counts['info'] }} info</span>
                <span class="badge bg-secondary">{{ $counts['total'] }} total</span>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('findings.show', $project) }}" class="row g-2 mb-4">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm border-secondary"
                    value="{{ $search }}" placeholder="Search file, rule, or message...">
            </div>
            <div class="col-auto">
                <select name="severity" class="form-select form-select-sm border-secondary">
                    <option value="">All severities</option>
                    @foreach (['ERROR', 'WARNING', 'INFO'] as $sev)
                        <option value="{{ $sev }}" {{ $severity === $sev ? 'selected' : '' }}>
                            {{ ucfirst(strtolower($sev)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm border-secondary">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Finding::STATUSES as $s)
                        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            @if ($scans->count() > 1)
                <div class="col-auto">
                    <select name="scan" class="form-select form-select-sm border-secondary">
                        @foreach ($scans as $scan)
                            <option value="{{ $scan->scanned_at }}"
                                {{ $scannedAt && $scannedAt->eq($scan->scanned_at) ? 'selected' : '' }}>
                                {{ $scan->scanned_at->format('M j, Y H:i') }} ({{ $scan->findings_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                @if ($search || $severity || $status || request('scan'))
                    <a href="{{ route('findings.show', $project) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>

        {{-- Findings list --}}
        @forelse ($findings as $finding)
            <div class="finding-card mb-3">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span
                            class="badge bg-{{ $finding->severityColor() }} {{ in_array($finding->severity, ['WARNING', 'INFO']) ? 'text-dark' : '' }} me-2">{{ $finding->severity }}</span>
                        <span class="fw-semibold small">
                            @if ($ruleId = $finding->ruleId())
                                <a href="{{ route('rules.editor', $ruleId) }}"
                                    target="_blank">{{ $finding->check_id }}</a>
                            @else
                                {{ $finding->check_id }}
                            @endif
                        </span>
                    </div>
                    <span
                        class="text-muted small font-monospace">{{ $finding->file_path }}:{{ $finding->start_line }}</span>
                </div>
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <p class="mb-2 small">{{ $finding->message }}</p>
                    <div class="d-flex gap-1 flex-shrink-0 ms-2">
                        <form method="POST" action="{{ route('findings.update-status', $finding) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $finding->status === 'flagged' ? 'unreviewed' : 'flagged' }}">
                            <button type="submit" class="btn btn-sm {{ $finding->status === 'flagged' ? 'btn-warning' : 'btn-outline-secondary' }}" title="Flag for interesting finding">
                                <i class="bi bi-flag{{ $finding->status === 'flagged' ? '-fill' : '' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('findings.update-status', $finding) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $finding->status === 'suppressed' ? 'unreviewed' : 'suppressed' }}">
                            <button type="submit" class="btn btn-sm {{ $finding->status === 'suppressed' ? 'btn-dark' : 'btn-outline-secondary' }}" title="Suppress for false positive">
                                <i class="bi bi-eye-slash{{ $finding->status === 'suppressed' ? '-fill' : '' }}"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @if ($finding->code_snippet)
                    <div class="finding-code">
                        <span class="text-muted small me-2"
                            style="user-select: none;">{{ $finding->start_line }}</span>{{ $finding->code_snippet }}
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-5">
                No findings match your filters.
            </div>
        @endforelse

        {{-- Pagination --}}
        @if ($findings->hasPages())
            <div class="mt-3">
                {{ $findings->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
