@extends('partials.layout')

@section('content')
    <div class="container-fluid px-4 py-4" style="max-width: 640px;">

        <a href="{{ route('rules.index') }}" class="text-muted small text-decoration-none d-inline-block mb-2">&larr; Back to rules</a>
        <h4 class="mb-4">New Rule</h4>

        <form method="POST" action="{{ route('rules.store') }}">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label">Rule ID <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="form-control bg-transparent text-white border-secondary @error('title') is-invalid @enderror"
                    placeholder="e.g. php-sql-injection" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text text-muted">Used as the rule identifier in YAML and scan results.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="2"
                    class="form-control bg-transparent text-white border-secondary @error('description') is-invalid @enderror"
                    placeholder="Brief description of what this rule detects...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="rule_group_id" class="form-label">Group</label>
                <select name="rule_group_id" id="rule_group_id"
                    class="form-select text-white border-secondary select-dark @error('rule_group_id') is-invalid @enderror">
                    <option value="">No group</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" {{ old('rule_group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                @error('rule_group_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="new_group" class="form-label">Or create new group</label>
                <input type="text" name="new_group" id="new_group" value="{{ old('new_group') }}"
                    class="form-control bg-transparent text-white border-secondary @error('new_group') is-invalid @enderror"
                    placeholder="New group name">
                @error('new_group')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text text-muted">If filled, this overrides the group selection above.</div>
            </div>

            <button type="submit" class="btn btn-primary">Create &amp; Open Editor</button>
        </form>
    </div>
@endsection
