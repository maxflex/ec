<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientsBrowseResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientsBrowseController extends Controller
{
    protected $filters = [
        'year' => ['year'],
    ];

    public function __invoke(Request $request)
    {
        $query = Client::canLogin()
            ->with(['phones', 'parent.phones', 'reports']) // 'contracts.versions.programs'
            ->with('logs', fn ($q) => $q->whereRaw('created_at >= NOW() - INTERVAL 3 MONTH'))
            ->withCount('comments')
            ->orderByRaw('last_name, first_name');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ClientsBrowseResource::class);
    }

    protected function filterYear($query, $year)
    {
        return $query->whereHas('contracts', fn ($q) => $q->where('year', $year));
    }
}
