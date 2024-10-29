<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use App\Models\{Client, ClientParent, ContractVersion, Request, Teacher, User};
use Illuminate\Support\Facades\DB;

trait TransferTrait
{
    protected $createdEmailId = [];
    protected $subjects = [
        1 => 'math',
        2 => 'phys',
        3 => 'chem',
        4 => 'bio',
        5 => 'inf',
        6 => 'rus',
        7 => 'lit',
        8 => 'soc',
        9 => 'his',
        10 => 'eng',
        11 => 'geo',
        12 => 'soch',
        13 => 'inf-oge',
        14 => 'phys-oge',
        15 => 'chem-oge',
        16 => 'bio-oge',
        17 => 'lit-oge',
        18 => 'eng-oge',
        19 => 'soc-oge',
        20 => 'his-oge',
        21 => 'math-prof',
        22 => 'math-base',
        23 => 'math-online',
        24 => 'rus-online',
        25 => 'soc-online',
        26 => 'math-practicum',
        27 => 'soc-practicum',
        28 => 'phys-practicum',
        29 => 'chem-practicum',
        30 => 'bio-practicum',
        31 => 'inf-practicum',
        32 => 'rus-practicum',
        33 => 'his-practicum',
        34 => 'eng-practicum',
        35 => 'geo-practicum',
        36 => 'izl',
        37 => 'geo-oge',
        38 => 'eng-spoken',
    ];

    // const CLIENT = 'App\\Models\\Client\\Client';
    // const PARENT = 'App\\Models\\Client\\Representative';
    // const REQUEST = 'App\\Models\\Request';
    // const ADMIN = 'App\\Models\\Admin\\Admin';
    // const TEACHER = 'App\\Models\\Teacher';

    protected function getUserId($createdEmailId): ?int
    {
        if (!$createdEmailId) {
            return null;
        }
        if (isset($this->createdEmailId[$createdEmailId])) {
            return $this->createdEmailId[$createdEmailId];
        }
        $adminId = DB::connection('egecrm')->table('emails')->whereId($createdEmailId)->value('entity_id');
        $user = User::find($adminId);
        if ($user === null) {
            return 1;
            // throw new Exception("User not found for created_email_id: $createdEmailId / adminId: $adminId");
        }
        $this->createdEmailId[$createdEmailId] = $user->id;
        return $user->id;
    }

    protected function nullify(string | null $text): string | null
    {
        if ($text === null || $text === '0000-00-00') {
            return null;
        }
        $text = trim($text);
        return $text ?: null;
    }

    protected function mapEnum(string | null $commaSeparated, string $enumClass): string | null
    {
        if ($commaSeparated === "" || $commaSeparated === null) {
            return null;
        }
        return collect(explode(',', $commaSeparated))
            ->map(fn ($id) => $enumClass::getById(intval($id))->name)
            ->join(',');
    }


    private function mapEntity($entityType)
    {
        return match ($entityType) {
            ET_ADMIN => User::class,
            ET_TEACHER => Teacher::class,
            ET_REQUEST => Request::class,
            ET_CLIENT => Client::class,
            ET_PARENT => ClientParent::class,
            default => $entityType
        };
    }

    /**
     * @return object{id: int, error: bool}
     */
    protected function getContractVersionProgram(
        int $contractId,
        int $gradeId,
        int $subjectId,
    ): object
    {
        $program = Program::fromOld($gradeId, $subjectId);
        $programs = ContractVersion::query()
            ->where('contract_id', $contractId)
            ->active()
            ->first()
            ->programs;
        $result = $programs->where('program', $program);

        if ($result->isNotEmpty()) {
            return (object)[
                'id' => $result->value('id'),
                'error' => false
            ];
        }

        // пытаемся установить соответствие только по subject, игнорируя grade
        $maxVersion = DB::connection('egecrm')
            ->table('contract_versions')
            ->where('contract_id', $contractId)
            ->max('version');

        $sId = DB::connection('egecrm')
            ->table('contract_versions', 'cv')
            ->join('contract_subjects as cs', fn($join) => $join
                ->on('cs.contract_version_id', '=', 'cv.id')
                ->where('cs.subject_id', '=', $subjectId)
            )
            ->where('cv.contract_id', $contractId)
            ->where('cv.version', $maxVersion)
            ->value('subject_id');

        // нашли нужный предмет в договоре
        if ($sId) {
            $sValue = str($this->subjects[$sId])->camel()->value();
            foreach ($programs as $program) {
                if (str($program->program->value)->startsWith($sValue)) {
                    return (object)[
                        'id' => $program->id,
                        'error' => false
                    ];
                }
            }
        }

        return (object)[
            'id' => 0,
            'error' => true
        ];
    }
}
