<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('creator')->orderBy('name')->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'url'         => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = new Project([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'url'         => $validated['url'],
            'description' => $validated['description'],
            'created_by'  => $request->user()->id,
        ]);

        $plainKey = $project->setApiKey();
        $project->save();

        return redirect()->route('projects.show', $project)
            ->with('api_key', $plainKey);
    }

    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'url'         => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function regenerateKey(Project $project)
    {
        $plainKey = $project->setApiKey();
        $project->save();

        return redirect()->route('projects.show', $project)
            ->with('api_key', $plainKey);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Project \"{$project->name}\" deleted.");
    }
}
