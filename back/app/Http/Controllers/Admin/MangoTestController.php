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
            'scenario' => ['required', 'string', 'in:appeared,connected,disconnected,outgoingAppeared,outgoingConnected,outgoingDisconnected'],
            'filters' => ['required', 'array'],
            'filters.entity_type' => ['nullable', 'string', 'in:'.implode(',', $this->incomingEntityTypes())],
            'filters.last_interaction' => ['required', 'string', 'in:none,missed,incoming,outgoing,outgoing_no_answer'],
            'filters.user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $scenario = $validated['scenario'];
        $filters = $validated['filters'];
        $userId = (int) ($filters['user_id'] ?? $request->user()->id);

        $payload = match ($scenario) {
            'appeared' => $this->buildIncomingAppearedPayload($request, $filters),
            'connected' => $this->buildIncomingConnectedPayload($request, $filters, $userId),
            'disconnected' => $this->buildIncomingDisconnectedPayload($request, $filters, $userId),
            'outgoingAppeared' => $this->buildOutgoingAppearedPayload($request, $filters, $userId),
            'outgoingConnected' => $this->buildOutgoingConnectedPayload($request, $filters),
            'outgoingDisconnected' => $this->buildOutgoingDisconnectedPayload($request, $filters),
        };

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw ValidationException::withMessages([
                'scenario' => 'Не удалось сериализовать payload',
            ]);
        }

        Mango::handle('call', $json);

        // Для outgoing в лог фронта нужно отдавать телефон абонента (to.number),
        // а не внутренний sip из from.number.
        $number = $payload['from']['number'] ?? $payload['to']['number'] ?? null;
        if (str_starts_with($scenario, 'outgoing')) {
            $number = $payload['to']['number'] ?? $number;
        }

        return [
            'ok' => true,
            'scenario' => $scenario,
            'entry_id' => $payload['entry_id'],
            'number' => $number,
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

    private function resolveNumber(array $filters): ?string
    {
        $entityType = $filters['entity_type'] ?? null;
        $lastInteractionMode = (string) ($filters['last_interaction'] ?? 'none');

        if ($entityType === null) {
            return $this->findUnknownNumberByLastInteractionMode($lastInteractionMode);
        }

        $query = Phone::query()
            ->where('entity_type', $entityType)
            ->whereNotNull('number')
            ->where('number', '<>', '');

        if ($lastInteractionMode === 'none') {
            $query->whereNotExists(fn (Builder $subquery) => $this->callsByPhoneNumberSubquery($subquery));
        } else {
            $query->whereExists(function (Builder $subquery) use ($lastInteractionMode) {
                $subquery
                    ->selectRaw('1')
                    ->from('calls as c')
                    ->whereColumn('c.number', 'phones.number')
                    // Смотрим только на последний звонок по номеру.
                    ->whereRaw(
                        'c.id = (
                            SELECT c2.id
                            FROM calls c2
                            WHERE c2.number = phones.number
                            ORDER BY c2.created_at DESC
                            LIMIT 1
                        )'
                    );

                $this->applyLastInteractionMode($subquery, 'c', $lastInteractionMode);
            });
        }

        return $query
            ->inRandomOrder()
            ->value('number');
    }

    private function findUnknownNumberByLastInteractionMode(string $lastInteractionMode): ?string
    {
        if ($lastInteractionMode === 'none') {
            // Для теста генерируем номер, отсутствующий и в phones, и в calls.
            for ($i = 0; $i < 100; $i++) {
                $number = '79'.str_pad((string) random_int(0, 999_999_999), 9, '0', STR_PAD_LEFT);
                if (! $this->numberExistsInPhonesOrCalls($number)) {
                    return $number;
                }
            }

            return null;
        }

        $query = Call::query()
            ->from('calls as c')
            ->selectRaw('c.number')
            ->leftJoin('phones as p', 'p.number', '=', 'c.number')
            ->whereNull('p.id')
            // Для каждого номера берём только последний звонок.
            ->whereRaw(
                'c.id = (
                    SELECT c2.id
                    FROM calls c2
                    WHERE c2.number = c.number
                    ORDER BY c2.created_at DESC
                    LIMIT 1
                )'
            );

        $this->applyLastInteractionMode($query, 'c', $lastInteractionMode);

        return $query
            ->inRandomOrder()
            ->value('c.number');
    }

    private function numberExistsInPhonesOrCalls(string $number): bool
    {
        return Phone::query()->where('number', $number)->exists()
            || Call::query()->where('number', $number)->exists();
    }

    private function callsByPhoneNumberSubquery(Builder $query): void
    {
        $query
            ->selectRaw('1')
            ->from('calls')
            ->whereColumn('calls.number', 'phones.number');
    }

    private function applyLastInteractionMode(Builder $query, string $alias, string $mode): void
    {
        // Классификация по последнему звонку:
        // - missed: входящий без ответа
        // - incoming: входящий с ответом
        // - outgoing: исходящий с ответом
        // - outgoing_no_answer: исходящий без ответа
        switch ($mode) {
            case 'missed':
                $query
                    ->where("{$alias}.type", 'incoming')
                    ->whereNull("{$alias}.answered_at");
                break;

            case 'incoming':
                $query
                    ->where("{$alias}.type", 'incoming')
                    ->whereNotNull("{$alias}.answered_at");
                break;

            case 'outgoing':
                $query
                    ->where("{$alias}.type", 'outgoing')
                    ->whereNotNull("{$alias}.answered_at");
                break;

            case 'outgoing_no_answer':
                $query
                    ->where("{$alias}.type", 'outgoing')
                    ->whereNull("{$alias}.answered_at");
                break;

            default:
                // Для mode=none условия на calls не применяем:
                // выборка "без взаимодействия" обрабатывается отдельно.
                break;
        }
    }

    private function newEntryId(): string
    {
        return rtrim(base64_encode('entry-'.microtime(true).'-'.random_int(1000, 9999)), '=');
    }

    private function incomingContextKey(Request $request, array $filters): string
    {
        $signature = implode('|', [
            $filters['entity_type'] ?? 'unknown',
            (string) ($filters['last_interaction'] ?? 'none'),
            (int) ($filters['user_id'] ?? $request->user()->id),
        ]);

        return 'mango-test:incoming:'.$request->user()->id.':'.md5($signature);
    }

    private function newCallId(): string
    {
        return rtrim(base64_encode('call-'.microtime(true).'-'.random_int(1000, 9999)), '=');
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

    private function outgoingContextKey(Request $request, array $filters): string
    {
        $signature = implode('|', [
            $filters['entity_type'] ?? 'unknown',
            (string) ($filters['last_interaction'] ?? 'none'),
            (int) ($filters['user_id'] ?? $request->user()->id),
        ]);

        return 'mango-test:outgoing:'.$request->user()->id.':'.md5($signature);
    }

    private function buildOutgoingAppearedPayload(Request $request, array $filters, int $userId): array
    {
        // Для outgoing фильтр "Кто звонит" трактуем как "Кому звоним":
        // берем телефон из той же выборки, что и для входящего (тип + last_interaction).
        $number = $this->resolveNumber($filters);
        if (! $number) {
            throw ValidationException::withMessages([
                'filters' => 'Для выбранной комбинации (тип + last_interaction) не найден номер в БД.',
            ]);
        }

        $entryId = $this->newEntryId();

        cache()->put($this->outgoingContextKey($request, $filters), [
            'entry_id' => $entryId,
            'number' => $number,
            'user_id' => $userId,
        ], now()->addHours(4));

        return [
            'entry_id' => $entryId,
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 1,
            'call_state' => 'Appeared',
            'location' => 'abonent',
            'from' => [
                'extension' => $userId,
                'number' => 'sip:test@kapralovka.mangosip.ru',
                // Для прохождения через Mango::eventCall должен совпадать с рабочей линией.
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'number' => $number,
            ],
            'dct' => [
                'type' => 0,
            ],
        ];
    }

    private function getOutgoingContext(Request $request, array $filters): array
    {
        $context = cache()->get($this->outgoingContextKey($request, $filters));

        if (! is_array($context) || ! isset($context['entry_id'], $context['number'], $context['user_id'])) {
            throw ValidationException::withMessages([
                'scenario' => 'Сначала выполните Outgoing Started.',
            ]);
        }

        return $context;
    }

    private function buildOutgoingConnectedPayload(Request $request, array $filters): array
    {
        $context = $this->getOutgoingContext($request, $filters);

        return [
            'entry_id' => $context['entry_id'],
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 2,
            'call_state' => 'Connected',
            'location' => 'abonent',
            'from' => [
                'extension' => $context['user_id'],
                'number' => 'sip:test@kapralovka.mangosip.ru',
                // Для прохождения через Mango::eventCall должен совпадать с рабочей линией.
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'number' => $context['number'],
            ],
            'dct' => [
                'type' => 0,
            ],
        ];
    }

    private function buildOutgoingDisconnectedPayload(Request $request, array $filters): array
    {
        $context = $this->getOutgoingContext($request, $filters);

        cache()->forget($this->outgoingContextKey($request, $filters));

        return [
            'entry_id' => $context['entry_id'],
            'call_id' => $this->newCallId(),
            'timestamp' => time(),
            'seq' => 3,
            'call_state' => 'Disconnected',
            'location' => 'abonent',
            'from' => [
                'extension' => $context['user_id'],
                'number' => 'sip:test@kapralovka.mangosip.ru',
                // Для прохождения через Mango::eventCall должен совпадать с рабочей линией.
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'number' => $context['number'],
            ],
            'disconnect_reason' => 1120,
            'dct' => [
                'type' => 0,
            ],
        ];
    }
}
