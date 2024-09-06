<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Utils\Swamp;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program', 'client_id'],
        'status' => ['status'],
    ];

    public function index(Request $request)
    {
        $query = Swamp::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    protected function filterStatus(&$query, $status)
    {
        switch ($status) {
            case 'toFulfil':
                $query->whereRaw("group_id IS NULL AND total_price_passed < total_price");
                break;

            case 'exceedNoGroup':
                $query->whereRaw("group_id IS NULL AND total_price_passed > total_price");
                break;

            case 'completeNoGroup':
                $query->whereRaw("group_id IS NULL AND total_price_passed = total_price");
                break;

            case 'inProcess':
                $query->whereRaw("group_id IS NOT NULL AND total_price_passed < total_price");
                break;

            case 'exceedInGroup':
                $query->whereRaw("group_id IS NOT NULL AND total_price_passed > total_price");
                break;

            case 'completeInGroup':
                $query->whereRaw("group_id IS NOT NULL AND total_price_passed = total_price");
                break;
        }
    }
}
