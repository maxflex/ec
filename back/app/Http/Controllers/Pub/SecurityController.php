<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Resources\PassLogResource;
use App\Http\Resources\SecurityPassResource;
use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Pass;
use App\Models\PassLog;
use App\Models\Teacher;
use App\Models\User;
use App\Utils\SecurityVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    protected $filters = [
        'search' => ['q'],
        'equals' => ['date'],
    ];

    protected $mapFilters = [
        'q' => 'comment',
        'date' => 'DATE(used_at)'
    ];

    public function sendCode()
    {
        SecurityVerificationService::sendCode();
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);

        return [
            'verified' => SecurityVerificationService::verifyCode($request->code)
        ];
    }

    /**
     * Все возможные пропуски. Потом ищется по ним на фронте
     */
    public function getAllPasses()
    {
        // сегодняшние разовые пропуски
        $result = Pass::whereDoesntHave('passLog')
            ->where('date', now()->format('Y-m-d'))
            ->get();

        // постоянные
        $result = $result->concat(Client::canLogin()->get());
        $result = $result->concat(ClientParent::canLogin()->get());
        $result = $result->concat(Teacher::canLogin()->get());
        $result = $result->concat(User::canLogin()->get());

        return [
            'result' => SecurityPassResource::collection($result),
            'appVersion' => 5, // TODO: снести всю эту логику
        ];
    }

    /**
     * Использовать пропуск
     */
    public function usePass(Request $request)
    {
        $request->validate([
            'id' => ['required', 'numeric'],
            'type' => ['required', 'in:client,parent,teacher,person,user']
        ]);
        $entityType = match ($request->type) {
            'client' => Client::class,
            'parent' => ClientParent::class,
            'teacher' => Teacher::class,
            'user' => User::class,
            default => Pass::class,
        };
        $entity = $entityType::find($request->id);
        PassLog::create([
            'used_at' => now()->format('Y-m-d H:i:s'),
            'complaint' => $request->complaint,
            'entity_id' => $request->id,
            'entity_type' => $entityType,
            'comment' => $entityType === Pass::class
                ? $entity->comment
                : $entity->formatName('full')
        ]);
        return $request->all();
    }

    /**
     * История проходов
     */
    public function history(Request $request)
    {
        $request->merge(['paginate' => 100]);
        $query = PassLog::with('entity')->orderBy('id', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, PassLogResource::class);
    }

    /**
     * Split by words and search by each word
     */
    protected function filterSearch(&$query, $value, $field)
    {
        if (strlen($value) < 2) {
            return;
        }
        $words = array_unique(array_filter(explode(' ', $value), 'trim'));
        $query->where(function ($query) use ($field, $words) {
            foreach ($words as $word) {
                $query->orWhere(DB::raw($this->getFieldName($field)), 'like', '%' . $word . '%');
            }
        });
    }
}


