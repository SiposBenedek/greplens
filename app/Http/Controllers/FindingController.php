<?php

namespace App\Http\Controllers;

use App\Models\Finding;
use App\Models\Project;
use Illuminate\Http\Request;

class FindingController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::whereHas('latestFindings')
            ->withCount('latestFindings as findings_count')
            ->withCount([
                'latestFindings as error_count'   => fn ($q) => $q->where('severity', 'ERROR'),
                'latestFindings as warning_count' => fn ($q) => $q->where('severity', 'WARNING'),
                'latestFindings as info_count'    => fn ($q) => $q->where('severity', 'INFO'),
            ])
            ->orderByDesc('findings_count')
            ->get();

        return view('findings.index', compact('projects'));
    }

    public function show(Request $request, Project $project)
    {
        $severity = $request->input('severity');
        $search = $request->input('search');
        $status = $request->input('status');

        // Available scans for this project (newest first)
        $scans = $project->findings()
            ->selectRaw('scanned_at, COUNT(*) as findings_count')
            ->groupBy('scanned_at')
            ->orderByDesc('scanned_at')
            ->get();

        // Resolve selected scan (default to latest)
        $selectedScan = $request->input('scan');
        $scannedAt = $selectedScan
            ? $scans->firstWhere('scanned_at', $selectedScan)?->scanned_at
            : $scans->first()?->scanned_at;

        $scopedFindings = $project->findings()->where('scanned_at', $scannedAt);

        $findings = (clone $scopedFindings)
            ->when($severity, fn ($q) => $q->where('severity', strtoupper($severity)))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('file_path', 'like', "%{$search}%")
                  ->orWhere('check_id', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            }))
            ->orderByRaw("CASE severity WHEN 'ERROR' THEN 1 WHEN 'WARNING' THEN 2 WHEN 'INFO' THEN 3 ELSE 4 END")
            ->orderBy('file_path')
            ->orderBy('start_line')
            ->paginate(10);

        $counts = [
            'total'   => (clone $scopedFindings)->count(),
            'error'   => (clone $scopedFindings)->where('severity', 'ERROR')->count(),
            'warning' => (clone $scopedFindings)->where('severity', 'WARNING')->count(),
            'info'    => (clone $scopedFindings)->where('severity', 'INFO')->count(),
        ];

        return view('findings.show', compact('project', 'findings', 'counts', 'severity', 'search', 'status', 'scans', 'scannedAt'));
    }

    public function updateStatus(Request $request, Finding $finding)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Finding::STATUSES),
        ]);

        $finding->update(['status' => $request->input('status')]);

        return back();
    }
}
