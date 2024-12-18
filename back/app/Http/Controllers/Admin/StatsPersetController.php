<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StatsPresetResource;
use App\Models\StatsPreset;
use Illuminate\Http\Request;

class StatsPersetController extends Controller
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

        return new StatsPresetResource(StatsPreset::create($data));
    }

    public function show(StatsPreset $statsPerset)
    {
        return new StatsPresetResource($statsPerset);
    }

    public function update(Request $request, StatsPreset $statsPerset)
    {
        $data = $request->validate([
            'metric' => ['required'],
            'color' => ['required'],
            'filters' => ['required'],
            'label' => ['required'],
        ]);

        $statsPerset->update($data);

        return new StatsPresetResource($statsPerset);
    }

    public function destroy(StatsPreset $statsPerset)
    {
        $statsPerset->delete();

        return response()->json();
    }
}
