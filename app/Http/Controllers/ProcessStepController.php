<?php

// app/Http/Controllers/ProcessStepController.php
namespace App\Http\Controllers;

use App\Models\ProcessStep;
use Illuminate\Http\Request;
use Artisan;
use CoreComponentRepository;


class ProcessStepController extends Controller
{
    public function index()
    {
        $processSteps = ProcessStep::paginate(10); // Adjust the number of items per page as needed
        return view('backend.process_steps.index', compact('processSteps'));
    }

    public function create()
    {
        return view('backend.process_steps.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'data.*.image' => 'required|string',
        ]);

        // Extract the first image data from the request
        $imageData = $request->data[0]['image'];

        ProcessStep::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageData,
        ]);

        return redirect()->route('process_steps.index')->with('success', 'Process step created successfully.');
    }


    public function edit(ProcessStep $processStep)
    {
        return view('backend.process_steps.edit', compact('processStep'));
    }

    public function update(Request $request, ProcessStep $processStep)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'data.*.image' => 'nullable|string',
        ]);

        $processStep->title = $request->title;
        $processStep->description = $request->description;

        // Handling image upload if it exists
        if (isset($request->data[0]['image'])) {
            // Assuming the path is directly stored in the hidden input field
            $processStep->image = $request->data[0]['image'];

            // Only update if the path is a valid image URL or file path
           /* if (filter_var($imageData, FILTER_VALIDATE_URL) || file_exists(storage_path('app/public/' . $imageData))) {
                $processStep->image = $imageData;
            }*/
        }

        $processStep->save();

        return redirect()->route('process_steps.index')->with('success', 'Process step updated successfully.');
    }



    public function destroy(ProcessStep $processStep)
    {
        $processStep->delete();
        return redirect()->route('process_steps.index')->with('success', 'Process step deleted successfully.');
    }
}

