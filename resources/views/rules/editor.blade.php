@extends('partials.layout')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/theme/material-darker.min.css">
@endpush

@section('content')
    <div class="editor-page d-flex flex-column" style="height: calc(100vh - 56px);">

        <div class="editor-header d-flex align-items-center justify-content-between px-4 py-2">
            <div>
                <a href="{{ route('rules.index', ['group' => $rule->rule_group_id, 'rule' => $rule->id]) }}"
                    class="text-muted small text-decoration-none">← Back to rules</a>
                <h5 class="mb-0 mt-1">{{ $rule->title }}</h5>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="run-btn"><i class="bi bi-play"></i>Run</button>
                <button class="btn btn-sm btn-primary" id="save-btn">Save YAML</button>
            </div>
        </div>

        <div class="d-flex flex-grow-1 overflow-hidden">

            <div class="editor-panel d-flex flex-column" style="width: 40%;">
                <div class="panel-label">Rule YAML</div>
                <textarea id="yaml-editor">{{ $rule->yaml_content }}</textarea>
            </div>

            <div class="panel-divider"></div>

            <div class="editor-panel d-flex flex-column" style="width: 30%;">
                <div class="panel-label">Test Code</div>
                <textarea id="test-code">{{ $rule->test_code }}</textarea>
            </div>

            <div class="panel-divider"></div>

            <div class="editor-panel d-flex flex-column" style="width: 30%;">
                <div class="panel-label d-flex align-items-center justify-content-between">
                    <span>Results</span>
                    <span id="result-badge" class="badge" style="display:none;"></span>
                </div>
                <div id="results-panel" class="results-panel flex-grow-1">
                    <p class="text-muted small mb-0">Run the test to see results here.</p>
                </div>
            </div>

        </div>
    </div>

    <form id="yaml-form" method="POST" action="{{ route('rules.update-yaml', $rule->id) }}" style="display:none;">
        @csrf
        @method('PATCH')
        <textarea name="yaml_content" id="yaml-form-content"></textarea>
        <textarea name="test_code" id="test-code-content"></textarea>
    </form>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/yaml/yaml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/ruby/ruby.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/go/go.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const modeMap = {
                php: 'application/x-httpd-php',
                javascript: 'javascript',
                typescript: { name: 'javascript', typescript: true },
                python: 'python',
                java: 'text/x-java',
                go: 'go',
                ruby: 'ruby',
            };

            const yamlEditor = CodeMirror.fromTextArea(document.getElementById('yaml-editor'), {
                mode: 'yaml',
                theme: 'material-darker',
                lineNumbers: true,
                tabSize: 2,
                indentWithTabs: false,
                lineWrapping: false,
            });

            const testEditor = CodeMirror.fromTextArea(document.getElementById('test-code'), {
                mode: 'application/x-httpd-php',
                theme: 'material-darker',
                lineNumbers: true,
                tabSize: 4,
                indentWithTabs: false,
                lineWrapping: false,
                placeholder: 'Paste code to test against the rule...',
            });

            let markers = [];

            // Auto-detect language from YAML and switch test editor mode
            function detectLanguage() {
                const match = yamlEditor.getValue().match(/languages:\s*\[([^\]]+)\]/);
                if (match) {
                    const lang = match[1].split(',')[0].trim();
                    testEditor.setOption('mode', modeMap[lang] || lang);
                }
            }

            // Clear highlights when user edits test code
            testEditor.on('change', clearMarkers);

            function applyHighlights(matches) {
                clearMarkers();
                matches.forEach(m => {
                    markers.push(testEditor.markText(
                        { line: m.start - 1, ch: 0 },
                        { line: m.end, ch: 0 },
                        { className: 'cm-highlight-match' }
                    ));
                });
            }

            function clearMarkers() {
                markers.forEach(m => m.clear());
                markers = [];
            }

            function escapeHtml(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            // Save
            document.getElementById('save-btn').addEventListener('click', function () {
                document.getElementById('yaml-form-content').value = yamlEditor.getValue();
                document.getElementById('test-code-content').value = testEditor.getValue();
                document.getElementById('yaml-form').submit();
            });

            // Run
            document.getElementById('run-btn').addEventListener('click', function () {
                const code = testEditor.getValue().trim();
                const yaml = yamlEditor.getValue().trim();
                const panel = document.getElementById('results-panel');
                const badge = document.getElementById('result-badge');

                if (!code) { panel.innerHTML = '<p class="text-muted small mb-0">Paste some test code first.</p>'; return; }
                if (!yaml) { panel.innerHTML = '<p class="text-muted small mb-0">YAML rule is empty.</p>'; return; }

                panel.innerHTML = '<p class="text-muted small mb-0">Running...</p>';
                badge.style.display = 'none';
                clearMarkers();

                fetch('{{ route('rules.run-test', $rule->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ test_code: code, yaml_content: yaml }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        panel.innerHTML = `<p class="text-danger small mb-0">${escapeHtml(data.error)}</p>`;
                        return;
                    }

                    badge.style.display = 'inline-block';

                    if (data.total === 0) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'No matches';
                        panel.innerHTML = `<div class="no-matches mt-1">✓ No matches — rule did not trigger.</div>`;

                        if (data.errors?.length) {
                            panel.innerHTML += data.errors.map(e => `
                                <div class="error-card mt-2">
                                    <span class="small fw-semibold text-warning">⚠ ${escapeHtml(e.type?.[0] ?? 'Error')}</span>
                                    <p class="small text-muted mb-0 mt-1">${escapeHtml(e.message)}</p>
                                </div>`).join('');
                        }
                        return;
                    }

                    applyHighlights(data.matches);
                    badge.className = 'badge bg-danger';
                    badge.textContent = `${data.total} match${data.total > 1 ? 'es' : ''}`;

                    panel.innerHTML = data.matches.map(m => `
                        <div class="match-card">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold" style="color:#f8a4a4;">
                                    Line ${m.start}${m.end !== m.start ? '–' + m.end : ''}
                                </span>
                                <span class="badge bg-secondary">${escapeHtml(m.severity)}</span>
                            </div>
                            <p class="small text-muted mb-0">${escapeHtml(m.message)}</p>
                            <div class="match-lines">${escapeHtml(m.snippet)}</div>
                        </div>`).join('');

                    if (data.errors?.length) {
                        panel.innerHTML += data.errors.map(e => `
                            <div class="error-card">
                                <span class="small fw-semibold text-warning">⚠ ${escapeHtml(e.type?.[0] ?? 'Error')}</span>
                                <p class="small text-muted mb-0 mt-1">${escapeHtml(e.message)}</p>
                            </div>`).join('');
                    }
                })
                .catch(() => {
                    panel.innerHTML = '<p class="text-danger small mb-0">Request failed.</p>';
                });
            });
            detectLanguage();
            yamlEditor.on('change', detectLanguage);
        });
    </script>
@endpush