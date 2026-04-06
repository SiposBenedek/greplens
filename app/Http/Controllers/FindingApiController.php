<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FindingApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var Project $project */
        $project = $request->attributes->get('project');

        if (! $project) {
            return response()->json(['error' => 'Project API key required.'], 403);
        }

        $request->validate([
            'results'                => 'required|array|max:10000',
            'results.*.check_id'    => 'required|string|max:500',
            'results.*.path'        => 'required|string|max:1000',
            'results.*.start'       => 'required|array',
            'results.*.start.line'  => 'required|integer|min:0',
            'results.*.start.col'   => 'required|integer|min:0',
            'results.*.end'         => 'required|array',
            'results.*.end.line'    => 'required|integer|min:0',
            'results.*.end.col'     => 'required|integer|min:0',
            'results.*.extra'       => 'required|array',
            'results.*.extra.message' => 'required|string|max:5000',
        ]);

        $results = $request->input('results');
        $scannedAt = now();

        DB::transaction(function () use ($project, $results, $scannedAt) {
            foreach ($results as $result) {
                $project->findings()->create([
                    'check_id'     => $result['check_id'],
                    'file_path'    => $result['path'],
                    'start_line'   => $result['start']['line'],
                    'start_col'    => $result['start']['col'],
                    'end_line'     => $result['end']['line'],
                    'end_col'      => $result['end']['col'],
                    'message'      => $result['extra']['message'],
                    'severity'     => strtoupper($result['extra']['severity'] ?? 'WARNING'),
                    'code_snippet' => $result['extra']['lines'] ?? null,
                    'metadata'     => $result['extra']['metadata'] ?? null,
                    'scanned_at'   => $scannedAt,
                ]);
            }

            $project->update(['last_seen_at' => $scannedAt]);
        });

        return response()->json([
            'message' => 'Findings synced.',
            'count'   => count($results),
        ]);
    }
}
