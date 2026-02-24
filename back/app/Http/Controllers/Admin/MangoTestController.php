<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Request as RequestModel;
use App\Models\Teacher;
use App\Utils\Mango;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MangoTestController extends Controller
{
    public function clearActiveCalls(): array
    {
        abort_if(! is_localhost(), 404);

        // Активные звонки Mango хранятся в tagged-cache "calls".
        // flush по тэгу очищает все entry_id для текущего окружения.
        cache()->tags('calls')->flush();

        return [
            'ok' => true,
        ];
    }

    public function callEvent(Request $request): array
    {
        abort_if(! is_localhost(), 404);

        $validated = $request->validate([
            'scenario' => ['required', 'string', 'in:appeared,connected,disconnected,outgoingConnected,outgoingDisconnected'],
            'filters' => ['required', 'array'],
            'filters.entity_type' => ['nullable', 'string', 'in:'.implode(',', $this->incomingEntityTypes())],
            'filters.last_interaction' => ['required', 'boolean'],
            'filters.user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $scenario = $validated['scenario'];
        $filters = $validated['filters'];
        $userId = (int) ($filters['user_id'] ?? $request->user()->id);

        $payload = match ($scenario) {
            'appeared' => $this->buildIncomingAppearedPayload($request, $filters),
            'connected' => $this->buildIncomingConnectedPayload($request, $filters, $userId),
            'disconnected' => $this->buildIncomingDisconnectedPayload($request, $filters, $userId),
            'outgoingConnected' => $this->buildOutgoingConnectedPayload(),
            'outgoingDisconnected' => $this->buildOutgoingDisconnectedPayload(),
        };

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw ValidationException::withMessages([
                'scenario' => 'Не удалось сериализовать payload',
            ]);
        }

        Mango::handle('call', $json);

        return [
            'ok' => true,
            'scenario' => $scenario,
            'entry_id' => $payload['entry_id'],
            'number' => $payload['from']['number'] ?? $payload['to']['number'] ?? null,
            'user_id' => $payload['to']['extension'] ?? $payload['from']['extension'] ?? null,
        ];
    }

    private function incomingEntityTypes(): array
    {
        return [
            Client::class,
            Representative::class,
            Teacher::class,
            RequestModel::class,
        ];
    }

    private function buildIncomingAppearedPayload(Request $request, array $filters): array
    {
        $number = $this->resolveNumber($filters);
        if (! $number) {
            throw ValidationException::withMessages([
                'filters' => 'Для выбранной комбинации (тип + last_interaction) не найден номер в БД.',
            ]);
        }

        $entryId = $this->newEntryId();
        $cacheKey = $this->incomingContextKey($request, $filters);
        cache()->put($cacheKey, [
            'entry_id' => $entryId,
            'number' => $number,
        ], now()->addHours(4));

        return [
            'entry_id' => $entryId,
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 1,
            'call_state' => 'Appeared',
            'location' => 'ivr',
            'from' => [
                'number' => $number,
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'number' => Mango::LINE_NUMBER,
                'line_number' => Mango::LINE_NUMBER,
            ],
            'dct' => [
                'type' => 0,
            ],
        ];
    }

    private function buildIncomingConnectedPayload(Request $request, array $filters, int $userId): array
    {
        $context = $this->getIncomingContext($request, $filters);

        return [
            'entry_id' => $context['entry_id'],
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 2,
            'call_state' => 'Connected',
            'location' => 'abonent',
            'from' => [
                'number' => $context['number'],
                'taken_from_call_id' => $this->newCallId(),
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'extension' => $userId,
                'number' => 'sip:test@kapralovka.mangosip.ru',
                'line_number' => Mango::LINE_NUMBER,
                'acd_group' => '',
            ],
            'dct' => [
                'type' => 0,
            ],
        ];
    }

    private function buildIncomingDisconnectedPayload(Request $request, array $filters, int $userId): array
    {
        $context = $this->getIncomingContext($request, $filters);

        $payload = [
            'entry_id' => $context['entry_id'],
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 3,
            'call_state' => 'Disconnected',
            'location' => 'abonent',
            'from' => [
                'number' => $context['number'],
                'taken_from_call_id' => $this->newCallId(),
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'extension' => $userId,
                'number' => 'sip:test@kapralovka.mangosip.ru',
                'line_number' => Mango::LINE_NUMBER,
                'acd_group' => '',
            ],
            'disconnect_reason' => 1110,
            'dct' => [
                'type' => 0,
            ],
        ];

        cache()->forget($this->incomingContextKey($request, $filters));

        return $payload;
    }

    private function buildOutgoingConnectedPayload(): array
    {
        return [
            'entry_id' => $this->newEntryId(),
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 2,
            'call_state' => 'Connected',
            'location' => 'abonent',
            'from' => [
                'extension' => '190',
                'number' => 'sip:test@kapralovka.mangosip.ru',
                'line_number' => '74956461080',
            ],
            'to' => [
                'number' => '79653155538',
            ],
            'dct' => [
                'type' => 0,
            ],
        ];
    }

    private function buildOutgoingDisconnectedPayload(): array
    {
        return [
            'entry_id' => $this->newEntryId(),
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 3,
            'call_state' => 'Disconnected',
            'location' => 'abonent',
            'from' => [
                'extension' => '190',
                'number' => 'sip:test@kapralovka.mangosip.ru',
                'line_number' => '74956461080',
            ],
            'to' => [
                'number' => '79653155538',
            ],
            'disconnect_reason' => 1120,
            'dct' => [
                'type' => 0,
            ],
        ];
    }

    private function getIncomingContext(Request $request, array $filters): array
    {
        $context = cache()->get($this->incomingContextKey($request, $filters));

        if (! is_array($context) || ! isset($context['entry_id'], $context['number'])) {
            throw ValidationException::withMessages([
                'scenario' => 'Сначала выполните Appeared (входящий) с текущими фильтрами.',
            ]);
        }

        return $context;
    }

    private function incomingContextKey(Request $request, array $filters): string
    {
        $signature = implode('|', [
            $filters['entity_type'] ?? 'unknown',
            (int) ($filters['last_interaction'] ?? 0),
            (int) ($filters['user_id'] ?? $request->user()->id),
        ]);

        return 'mango-test:incoming:'.$request->user()->id.':'.md5($signature);
    }

    private function resolveNumber(array $filters): ?string
    {
        $entityType = $filters['entity_type'] ?? null;
        $hasLastInteraction = (bool) ($filters['last_interaction'] ?? false);

        if ($entityType === null) {
            return $hasLastInteraction
                ? $this->findUnknownNumberWithLastInteraction()
                : $this->findUnknownNumberWithoutLastInteraction();
        }

        $query = Phone::query()
            ->where('entity_type', $entityType)
            ->whereNotNull('number')
            ->where('number', '<>', '');

        if ($hasLastInteraction) {
            $query->whereExists(fn (Builder $subquery) => $this->callsByPhoneNumberSubquery($subquery));
        } else {
            $query->whereNotExists(fn (Builder $subquery) => $this->callsByPhoneNumberSubquery($subquery));
        }

        return $query
            ->inRandomOrder()
            ->value('number');
    }

    private function callsByPhoneNumberSubquery(Builder $query): void
    {
        $query
            ->selectRaw('1')
            ->from('calls')
            ->whereColumn('calls.number', 'phones.number');
    }

    private function findUnknownNumberWithLastInteraction(): ?string
    {
        $row = Call::query()
            ->selectRaw('calls.number')
            ->leftJoin('phones', 'phones.number', '=', 'calls.number')
            ->whereNull('phones.id')
            ->groupBy('calls.number')
            ->inRandomOrder()
            ->first();

        return $row?->number;
    }

    private function findUnknownNumberWithoutLastInteraction(): ?string
    {
        // Для теста генерируем номер, отсутствующий и в phones, и в calls.
        for ($i = 0; $i < 100; $i++) {
            $number = '79'.str_pad((string) random_int(0, 999_999_999), 9, '0', STR_PAD_LEFT);
            if (! $this->numberExistsInPhonesOrCalls($number)) {
                return $number;
            }
        }

        return null;
    }

    private function numberExistsInPhonesOrCalls(string $number): bool
    {
        return Phone::query()->where('number', $number)->exists()
            || Call::query()->where('number', $number)->exists();
    }

    private function newEntryId(): string
    {
        return rtrim(base64_encode('entry-'.microtime(true).'-'.random_int(1000, 9999)), '=');
    }

    private function newCallId(): string
    {
        return rtrim(base64_encode('call-'.microtime(true).'-'.random_int(1000, 9999)), '=');
    }
}
