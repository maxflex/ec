<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Direction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContractVersionListResource;
use App\Http\Resources\ContractVersionResource;
use App\Models\Contract;
use App\Models\ContractVersion;
use App\Models\ScheduleDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractVersionController extends Controller
{
    protected $filters = [
        'contract' => ['year', 'company'],
        'direction' => ['direction'],
        'isActive' => ['is_active'],
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
            'contract.id' => ['required', 'exists:contracts,id'],
            'apply_move_groups' => ['sometimes', 'exists:schedule_drafts,id'],
        ]);

        $contract = Contract::find($request->contract['id']);

        $contractVersion = $contract->versions()->create([
            ...$request->all(),
            'user_id' => auth()->id(),
        ]);

        foreach ($request->programs as $p) {
            $contractVersionProgram = $contractVersion->programs()->create($p);
            $contractVersionProgram->prices()->createMany($p['prices']);
        }

        $isRelinked = $contractVersion->relinkIds(
            $contractVersion->prev
        );

        if (! $isRelinked) {
            $contractVersion->programs->each->delete();
            $contractVersion->delete();
            abort(422);
        }

        sync_relation($contractVersion, 'payments', $request->all());

        // применяем перемещения в группах согласно проекту, если нужно
        if ($request->has('apply_move_groups')) {
            $scheduleDraft = ScheduleDraft::find($request->apply_move_groups);
            $scheduleDraft->applyMoveGroups($contract->id);
        }

        return new ContractVersionListResource($contractVersion);
    }

    public function update(ContractVersion $contractVersion, Request $request)
    {
        $contractVersion->update($request->all());
        sync_relation($contractVersion, 'programs', $request->all());
        foreach ($contractVersion->programs as $index => $program) {
            sync_relation($program, 'prices', $request->programs[$index]);
            $program->updateStatus();
        }
        sync_relation($contractVersion, 'payments', $request->all());

        $contractVersion->contract->update([
            'source' => $request->contract['source'] ?? null,
        ]);

        return new ContractVersionListResource($contractVersion);
    }

    public function destroy(ContractVersion $contractVersion)
    {
        // если сносим активную и она не последняя
        if ($contractVersion->is_active && $contractVersion->prev) {
            $isRelinked = $contractVersion->prev->relinkIds($contractVersion);
            abort_if(! $isRelinked, 422);
        }

        DB::transaction(fn () => $contractVersion->delete());

        return new ContractVersionListResource($contractVersion);
    }

    protected function filterContract($query, $value, $field)
    {
        $query->whereHas('contract', fn ($q) => $q->where($field, $value));
    }

    protected function filterDirection($query, $value)
    {
        if (is_array($value)) {
            if (count($value) === 0) {
                return;
            }

            $programs = collect();
            foreach ($value as $directionString) {
                $programs = $programs->concat(
                    Direction::from($directionString)->toPrograms()
                );
            }
        } else {
            $programs = Direction::from($value)->toPrograms();
        }

        $query->whereHas('programs', fn ($q) => $q->whereIn('program', $programs));
    }

    protected function filterIsActive($query, $value)
    {
        $value
            ? $query->where('is_active', true)
            : $query->whereRaw('
                contract_versions.id = (
                    select min(id) from contract_versions as cv2
                    where cv2.contract_id = contract_versions.contract_id
                )
            ');
    }
}
