<?php

namespace App\Http\Controllers;

use App\Models\RecordingType;
use Illuminate\Http\Request;

class RecordingTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recordingTypes = RecordingType::active()->orderBy('name')->get();
        
        return response()->json([
            'recording_types' => $recordingTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'display_name' => $type->display_name,
                    'description' => $type->description,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
