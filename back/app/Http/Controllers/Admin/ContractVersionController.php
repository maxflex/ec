<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ContractVersionResource;
use App\Models\ContractVersion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractVersionController extends Controller
{
    public function store(Request $request)
    {
        $contractVersion = auth()->user()->entity->contractVersions()->create($request->all());
        $contractVersion->syncRelation($request->all(), 'programs');
        $contractVersion->syncRelation($request->all(), 'payments');
        return new ContractVersionResource($contractVersion);
    }

    public function update(ContractVersion $contractVersion, Request $request)
    {
        $contractVersion->update($request->all());
        $contractVersion->syncRelation($request->all(), 'programs');
        $contractVersion->syncRelation($request->all(), 'payments');
        return new ContractVersionResource($contractVersion);
    }

    public function destroy(ContractVersion $contractVersion)
    {
        $contractVersion->programs->each->delete();
        $contractVersion->payments->each->delete();
        $contractVersion->delete();
        // если удалили последнюю версию, то сносим весь договор
        if (!$contractVersion->chain()->exists()) {
            $contractVersion->contract->delete();
        }
    }
}
