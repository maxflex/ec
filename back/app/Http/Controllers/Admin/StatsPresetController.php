<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatsPresetResource;
use App\Models\StatsPreset;
use Illuminate\Http\Request;

class StatsPresetController extends Controller
{
    public function index()
    {
        return StatsPresetResource::collection(StatsPreset::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'metric' => ['required'],
            'color' => ['required'],
            'filters' => ['required'],
            'label' => ['required'],
        ]);

        return new StatsPresetResource(
            auth()->user()->statsPresets()->create($data)
        );
    }

    public function show(StatsPreset $statsPreset)
    {
        return new StatsPresetResource($statsPreset);
    }

    public function destroy(StatsPreset $statsPreset)
    {
        $statsPreset->delete();

        return response()->json();
    }
}
