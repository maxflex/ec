<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Direction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PeopleSelectorResource;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\Teacher;
use Illuminate\Http\Request;

class PeopleSelectorController extends Controller
{
    protected $clientFilters = [
        'equals' => ['client_id'],
        'direction' => ['direction'],
        'searchByName' => ['q'],
    ];

    protected $teacherFilters = [
    ];

    public function __invoke(Request $request)
    {
        return $request->input('mode') === 'clients'
            ? $this->clients($request)
            : $this->teachers($request);
    }

    private function clients(Request $request)
    {
        $query = Client::canLogin(true)
            ->orderByRaw('last_name, first_name, middle_name');

        $this->filter($request, $query, $this->clientFilters);

        $result = PeopleSelectorResource::collection((clone $query)->paginate(30));

        if (intval($request->page) === 1) {
            $result->additional([
                'extra' => [
                    'ids' => $query->pluck('clients.id')->all(),
                ],
            ]);
        }

        return $result;
    }

    private function teachers(Request $request)
    {
        $query = Teacher::query()
            ->canLogin()
            ->orderByRaw('last_name, first_name, middle_name');

        $this->filter($request, $query, $this->teacherFilters);

        $result = PersonResource::collection((clone $query)->paginate(30));

        if (intval($request->page) === 1) {
            $result->additional([
                'extra' => [
                    'ids' => $query->pluck('teachers.id')->all(),
                ],
            ]);
        }

        return $result;
    }

    protected function filterDirection($query, array $values)
    {
        if (count($values) === 0) {
            return;
        }

        $programs = collect();
        foreach ($values as $directionString) {
            $programs = $programs->concat(
                Direction::from($directionString)->toPrograms()
            );
        }
        $programs = $programs->unique();

        $query->whereHas(
            'contracts',
            fn ($q) => $q
                ->where('year', current_academic_year())
                ->whereHas('versions', fn ($q) => $q
                    ->where('is_active', true)
                    ->whereHas('programs', fn ($q) => $q->whereIn('program', $programs))
                )
        );
    }
}
