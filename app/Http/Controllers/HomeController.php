<?php

namespace App\Http\Controllers;

use App\Models\Finding;
use App\Models\Project;
use App\Models\Rule;

class HomeController extends Controller
{
    public function index()
    {
        $totalRules    = Rule::count();
        $activeRules   = Rule::where('is_active', true)->count();
        $totalProjects = Project::count();

        $recentProjects = Project::withCount('latestFindings as findings_count')
            ->orderByDesc('last_seen_at')
            ->limit(5)
            ->get();

        $severityCounts = [
            'error'   => Finding::where('severity', 'ERROR')->count(),
            'warning' => Finding::where('severity', 'WARNING')->count(),
            'info'    => Finding::where('severity', 'INFO')->count(),
        ];

        $totalFindings = array_sum($severityCounts);

        return view('home', compact(
            'totalRules',
            'activeRules',
            'totalProjects',
            'recentProjects',
            'severityCounts',
            'totalFindings',
        ));
    }
}
