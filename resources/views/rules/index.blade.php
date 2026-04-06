@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Rules</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('rules.create') }}" class="btn btn-sm btn-primary">New Rule</a>
                <a href="{{ route('rules.import') }}" class="btn btn-sm btn-outline-secondary">Import</a>
                <a href="{{ route('rules.export') }}" class="btn btn-sm btn-outline-secondary">Export</a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('rules.index') }}" class="row g-2 mb-4 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm bg-transparent text-white border-secondary"
                    placeholder="Search rules..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="language" class="form-select form-select-sm text-white border-secondary select-dark">
                    <option value="">All languages</option>
                    @foreach ($languages as $lang)
                        <option value="{{ $lang }}" {{ request('language') === $lang ? 'selected' : '' }}>
                            {{ ucfirst($lang) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="active" class="form-select form-select-sm text-white border-secondary select-dark">
                    <option value="" {{ !request()->has('active') ? 'selected' : '' }}>All status</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'language', 'active']))
                    <a href="{{ route('rules.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 rules-table">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Rule</th>
                        <th>Group</th>
                        <th>Severity</th>
                        <th>Languages</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rules as $rule)
                        <tr class="rule-row" data-href="{{ route('rules.editor', $rule->id) }}" style="cursor: pointer;">
                            <td>
                                <span class="fw-semibold">{{ $rule->title }}</span>
                                @if ($rule->description)
                                    <br><span class="text-muted small">{{ Str::limit($rule->description, 80) }}</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{ $rule->group?->name ?? '—' }}</span></td>
                            <td>
                                @if ($rule->severity)
                                    @php
                                        $severityClass = match(strtoupper($rule->severity)) {
                                            'ERROR'   => 'bg-danger',
                                            'WARNING' => 'bg-warning text-dark',
                                            'INFO'    => 'bg-info text-dark',
                                            default   => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $severityClass }}">{{ strtoupper($rule->severity) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if (!empty($rule->languages))
                                    @foreach ($rule->languages as $lang)
                                        <span class="badge bg-secondary fw-normal">{{ $lang }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $rule->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                @if (request()->hasAny(['search', 'language', 'active']))
                                    No rules match your filters.
                                    <a href="{{ route('rules.index') }}" class="text-decoration-none" style="color: var(--blue-1);">Clear filters</a>
                                @else
                                    No rules yet.
                                    <a href="{{ route('rules.import') }}" class="text-decoration-none" style="color: var(--blue-1);">Import some rules</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rules->hasPages())
            <div class="mt-3">
                {{ $rules->links() }}
            </div>
        @endif
        <div class="text-muted small mt-3">{{ $rules->total() }} {{ Str::plural('rule', $rules->total()) }}</div>
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
