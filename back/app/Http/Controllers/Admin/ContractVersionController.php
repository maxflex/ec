<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ContractVersionResource;
use App\Models\ContractVersion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContractVersionListResource;

class ContractVersionController extends Controller
{
    protected $filters = [
        'contract' => ['year', 'company'],
        'version' => ['version']
    ];

    public function index(Request $request)
    {
        $query = ContractVersion::query()
            ->with(['contract', 'contract.client'])
            ->withCount([
                'payments',
                'programs as programs_active_count' => fn ($q) => $q->active(),
                'programs as programs_closed_count' => fn ($q) => $q->closed(),
            ])
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            ContractVersionListResource::class
        );
    }

    public function show(ContractVersion $contractVersion)
    {
        return new ContractVersionResource($contractVersion);
    }

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
        return new ContractVersionListResource($contractVersion);
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

    protected function filterContract(&$query, $value, $field)
    {
        $query->whereHas('contract', fn ($q) => $q->where($field, $value));
    }

    protected function filterVersion(&$query, $value)
    {
        $value === 'first'
            ? $query->where('version', 1)
            : $query->lastVersions();
    }
}
