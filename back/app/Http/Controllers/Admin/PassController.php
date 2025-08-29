<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PassesPermanentResource;
use App\Http\Resources\PassResource;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\Pass;
use App\Models\PassLog;
use Illuminate\Http\Request;

class PassController extends Controller
{
    protected $filters = [
        'equals' => ['request_id'],
        'status' => ['status'],
        'direction' => ['direction'],
    ];

    protected $statsFilters = [
        'equals' => ['entity_type'],
        'gte' => ['date_from'],
        'lte' => ['date_to'],
    ];

    protected $mapFilters = [
        'date_from' => 'DATE(used_at)',
        'date_to' => 'DATE(used_at)',
    ];

    public function index(Request $request)
    {
        $query = Pass::orderBy('date', 'desc')->with('request')->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, PassResource::class);
    }

    public function permanent(Request $request)
    {
        $request->validate(['entity' => ['required', 'string']]);
        $entity = $request->entity;
        $query = $entity::canLogin()->orderByRaw('last_name, first_name');

        if ($entity === Client::class) {
            $query->with(['representative', 'contracts.versions.programs']);
        }

        $data = PassesPermanentResource::collection($query->get());

        return paginate($data);
    }

    public function stats(Request $request)
    {
        $query = PassLog::query()
            ->with('entity')
            ->where('entity_type', '<>', Pass::class)
            ->groupByRaw('entity_type, entity_id')
            ->selectRaw('
                entity_type, entity_id, count(*) as cnt,
                cast(sum(if(complaint is null, 0, 1)) as unsigned) as complaints_cnt
            ')
            ->orderBy('cnt', 'desc');

        $this->filter($request, $query, $this->statsFilters);

        $data = $query->get()->map(fn ($item) => extract_fields($item, [
            'entity_id', 'entity_type', 'cnt',
        ], [
            'entity' => new PersonResource($item->entity),
        ]));

        return paginate($data);
    }

    public function store(Request $request)
    {
        $pass = auth()->user()->passes()->create(
            $request->all()
        );

        return new PassResource($pass);
    }

    public function update(Pass $pass, Request $request)
    {
        $pass->update($request->all());

        return new PassResource($pass);
    }

    public function destroy(Pass $pass)
    {
        $pass->delete();
    }

    protected function filterStatus($query, $status)
    {
        switch ($status) {
            case 'active':
                $query->whereDoesntHave('passLog')->where('date', '>=', now()->format('Y-m-d'));
                break;

            case 'used':
                $query->whereHas('passLog');
                break;

            case 'expired':
                $query->whereDoesntHave('passLog')->where('date', '<', now()->format('Y-m-d'));
        }
    }

    protected function filterDirection($query, array $directions)
    {
        if (count($directions) === 0) {
            return;
        }

        $query->whereHas('request', fn ($q) => $q->whereIn('direction', $directions));
    }
}
