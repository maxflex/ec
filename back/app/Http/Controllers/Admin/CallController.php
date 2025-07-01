<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallListResource;
use App\Models\Call;
use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Teacher;
use Illuminate\Http\Request;

class CallController extends Controller
{
    protected $filters = [
        'search' => ['q'],
    ];

    public function index(Request $request)
    {
        $query = Call::query()
            ->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, CallListResource::class);
    }

    public function active()
    {
        return Call::getActive();
    }

    public function recording($action, Call $call)
    {
        return $call->getRecording($action);
    }

    public function destroy(Call $call)
    {
        if ($call->is_missed && ! $call->is_missed_callback) {
            $call->delete();
        }
    }

    public function filterSearch($query, $value)
    {
        if (! $value) {
            return;
        }
        if (is_numeric($value)) {
            $query->where('number', 'like', '%'.$value.'%');
        } else {
            $query->whereHas('phone', fn ($q) => $q
                ->whereHasMorph('entity', [
                    Client::class,
                    ClientParent::class,
                    Teacher::class,
                ], fn ($q) => $q->whereRaw('
                CONCAT(last_name, first_name) LIKE ?
            ', ["%$value%"])
                ));
        }
    }
}
