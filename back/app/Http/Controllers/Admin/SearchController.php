<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchResultResource;
use App\Models\Client;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'page' => ['required', 'integer', 'min:1'],
            'q' => ['required', 'string', 'min:3'],
        ]);

        $page = $request->page;
        $perPage = 30;

        $raw = Client::search(mb_strtolower($request->q))
            ->options([
                'include_fields' => 'id,is_active',
                'query_by' => 'last_name,first_name,middle_name,contract_ids,phones',
                'infix' => 'off,off,off,off,fallback',
                'sort_by' => 'is_active:desc,weight:desc,_text_match:desc',
                'enable_highlight_v1' => false,
                'per_page' => $perPage,
                'page' => $page,
            ])
            ->raw();

        return [
            'data' => SearchResultResource::collection($raw['hits']),
            'meta' => [
                'total' => $raw['found'],
                'current_page' => $page,
                'total_pages' => (int) ceil($raw['found'] / $perPage),
            ],
        ];
    }
}
