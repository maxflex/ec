<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use App\Models\{Client, ClientParent, ContractVersion, Request, Teacher, User};
use Illuminate\Support\Facades\DB;

trait TransferTrait
{
    protected $createdEmailId = [];

    // const CLIENT = 'App\\Models\\Client\\Client';
    // const PARENT = 'App\\Models\\Client\\Representative';
    // const REQUEST = 'App\\Models\\Request';
    // const ADMIN = 'App\\Models\\Admin\\Admin';
    // const TEACHER = 'App\\Models\\Teacher';

    protected function getUserId($createdEmailId): int | null
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
        return $text ? $text : null;
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

    protected function getContractVersionProgramId(
        int $contractId,
        int $gradeId,
        int $subjectId
    ): int
    {
//        dump([$contractId, $gradeId, $subjectId]);
        $program = Program::fromOld($gradeId, $subjectId);
        return ContractVersion::query()
            ->where('contract_id', $contractId)
            ->active()
            ->first()
            ->programs()
            ->where('program', $program)
            ->value('id') ?? 0;
    }
}
