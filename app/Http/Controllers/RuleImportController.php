<?php

namespace App\Http\Controllers;

use App\Services\RuleImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RuleImportController extends Controller
{
    public function __construct(private RuleImportService $importService) {}

    public function showForm()
    {
        return view('rules.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'zip_file' => ['required', 'file', 'mimes:zip', 'max:10240'],
        ]);

        try {
            $stats = $this->importService->importFromZip(
                $request->file('zip_file')->getRealPath()
            );

            return redirect()
                ->route('rules.import')
                ->with('success', "Import complete: {$stats['groups']} group(s), {$stats['rules']} rule(s) imported.");
        } catch (\Exception $e) {
            Log::error('Rule import failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('rules.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
