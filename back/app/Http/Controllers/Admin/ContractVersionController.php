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

        $activeCv = ContractVersion::query()
            ->where('contract_id', $request->contract_id)
            ->active()
            ->first();

        // создаём архив на основе активной версии
        $activeCv->createArchive();

        // обновляем активную версию
        $activeCv->fill($request->all());
        $activeCv->setCreatedAt(now());
        $activeCv->save();
        $activeCv->syncRelation($request->all(), 'programs');
        $activeCv->syncRelation($request->all(), 'payments');

        return new ContractVersionListResource($activeCv);
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
        return new ContractVersionListResource($contractVersion);
    }

    protected function filterContract(&$query, $value, $field)
    {
        $query->whereHas('contract', fn ($q) => $q->where($field, $value));
    }
}
