<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonListResource;
use App\Models\Client;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    protected $filters = [
        'telegram' => ['telegram']
    ];

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

        /**
         * testing purposes
         */
        $userQuery = User::selectRaw(<<<SQL
            id,
            first_name,
            last_name,
            middle_name,
            ? as entity_type
        SQL, [
            User::class
        ])->with('photo');

        $this->filter($request, $query);
        $this->filter($request, $teacherQuery);
        $this->filter($request, $userQuery);

        $query->union($teacherQuery)->union($userQuery);

        return $this->handleIndexRequest($request, $query, PersonListResource::class);
    }

    /**
     * Только те, у кого есть телеграм
     */
    protected function filterTelegram(&$query)
    {
        $query->whereHas('phones', fn ($q) => $q->whereNotNull('telegram_id'));
    }
}
