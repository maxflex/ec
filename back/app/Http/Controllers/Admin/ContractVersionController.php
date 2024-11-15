<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractVersionListResource;
use App\Http\Resources\ContractVersionResource;
use App\Models\ContractVersion;
use Illuminate\Http\Request;

class ContractVersionController extends Controller
{
    protected $filters = [
        'equals' => ['is_active'],
        'contract' => ['year', 'company'],
    ];

    public function index(Request $request)
    {
        $query = ContractVersion::query()
            ->with(['contract', 'contract.client'])
            ->withCount(['payments', 'programs'])
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

    /**
     * Новая версия договора
     * (новая цепь договора в ContractController@store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract.id' => ['required', 'exists:contracts,id']
        ]);
        $request->merge(['contract_id' => $request->contract['id']]);
        $contractVersion = auth()->user()->contractVersions()->create($request->all());
        $contractVersion->syncRelation($request->all(), 'programs');
        foreach ($contractVersion->programs as $index => $program) {
            $program->syncRelation($request->programs[$index], 'prices');
        }

        $isRelinked = $contractVersion->relinkIds(
            $contractVersion->prev
        );

        if (!$isRelinked) {
            $contractVersion->programs->each->delete();
            $contractVersion->delete();
            abort(422);
        }

        $contractVersion->syncRelation($request->all(), 'payments');
        return new ContractVersionListResource($contractVersion);
    }

    public function update(ContractVersion $contractVersion, Request $request)
    {
        $contractVersion->update($request->all());
        $contractVersion->syncRelation($request->all(), 'programs');
        foreach ($contractVersion->programs as $index => $program) {
            $program->syncRelation($request->programs[$index], 'prices');
        }
        $contractVersion->syncRelation($request->all(), 'payments');
        return new ContractVersionListResource($contractVersion);
    }

    public function destroy(ContractVersion $contractVersion)
    {
        // если сносим активную и она не последняя
        if ($contractVersion->is_active && $contractVersion->prev) {
            $isRelinked = $contractVersion->prev->relinkIds($contractVersion);
            abort_if(!$isRelinked, 422);
        }
        $contractVersion->delete();
        return new ContractVersionListResource($contractVersion);
    }

    protected function filterContract(&$query, $value, $field)
    {
        $query->whereHas('contract', fn ($q) => $q->where($field, $value));
    }
}
