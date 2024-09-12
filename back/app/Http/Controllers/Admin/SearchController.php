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
            'q' => ['required', 'string', 'min:3']
        ]);

        $result = Client::search($request->q)
            ->options([
                'include_fields' => 'id',
                'query_by' => 'last_name,first_name,middle_name,phones',
                'infix' => 'off,off,off,fallback',
                'enable_highlight_v1' => false,
                'per_page' => 30,
            ])
            ->raw();

//        $data = [];
//        foreach ($result['hits'] as $r) {
//            $document = (object)$r['document'];
//            [$class, $id] = explode('-', $document->id);
//            $model = "App\\Models\\$class";
//            $data[] = $model::find($id);
//        }

        return [
            'data' => SearchResultResource::collection($result['hits']),
            'meta' => [
                'total' => $result['found']
            ]
        ];
    }
}
