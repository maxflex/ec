<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonListResource;
use App\Models\Client;
use App\Models\Teacher;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Client::selectRaw(<<<SQL
            id,
            first_name,
            last_name,
            middle_name,
            ? as entity_type
        SQL, [
            Client::class
        ])->with('photo');

        $teacherQuery = Teacher::selectRaw(<<<SQL
            id,
            first_name,
            last_name,
            middle_name,
            ? as entity_type
        SQL, [
            Teacher::class
        ])->with('photo');

        $query->union($teacherQuery);

        return $this->handleIndexRequest($request, $query, PersonListResource::class);
    }
}
