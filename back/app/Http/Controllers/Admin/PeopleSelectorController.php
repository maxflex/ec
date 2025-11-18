<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\PeopleSelectorResource;
use App\Models\Event;
use App\Utils\PeopleSelector;
use Illuminate\Http\Request;

class PeopleSelectorController extends Controller
{
    public function getAll()
    {
        return PeopleSelectorResource::collection(
            PeopleSelector::getAll()
        );
    }

    public function getForEvent(Event $event)
    {
        return PeopleSelectorResource::collection(
            PeopleSelector::getForEvent($event)
        );
    }

    public function unpackLocalStorage(Request $request)
    {
        $request->validate([
            'selected' => ['array'],
            'selected.*' => ['string'],
        ]);

        return PeopleSelectorResource::collection(
            PeopleSelector::unpackLocalStorage($request->selected)
        );
    }

    public function unpackEvent(Event $event)
    {
        return [
            'event' => new EventResource($event),
            'people' => PeopleSelectorResource::collection(PeopleSelector::unpackEvent($event)),
        ];
    }
}
