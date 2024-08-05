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
        'telegram' => ['telegram'],
    ];

    public function __invoke(Request $request)
    {
        $query = Client::query()
            ->active()
            ->whereNotNull('last_name')
            ->selectRaw(<<<SQL
                id,
                first_name,
                last_name,
                middle_name,
                id as `entity_id`,
                ? as entity_type
            SQL, [
                Client::class,
            ])
            ->with('photo');

        $teacherQuery = Teacher::query()
            ->active()
            ->selectRaw(<<<SQL
                id,
                first_name,
                last_name,
                middle_name,
                id as `entity_id`,
                ? as entity_type
            SQL, [
                Teacher::class,
            ])
            ->with('photo');

        /**
         * testing purposes
         */
        $userQuery = User::query()
            ->selectRaw(<<<SQL
                id,
                first_name,
                last_name,
                middle_name,
                id as `entity_id`,
                ? as entity_type
            SQL, [
                User::class,
            ])
            ->with('photo');

        $this->filter($request, $query);
        $this->filter($request, $teacherQuery);
        $this->filter($request, $userQuery);

        $query
            ->union($teacherQuery)
            ->union($userQuery)
            ->orderByRaw(<<<SQL
                CASE
                    WHEN entity_type = ? THEN 1
                    WHEN entity_type = ? THEN 2
                    WHEN entity_type = ? THEN 3
                END ASC,
                last_name ASC,
                first_name ASC
            SQL, [
                Client::class,
                Teacher::class,
                User::class
            ]);

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
