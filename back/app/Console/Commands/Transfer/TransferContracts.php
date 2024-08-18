<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Company;
use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferContracts extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:contracts';
    protected $description = 'Transfer contracts';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('contract_version_programs')->delete();
        DB::table('contract_version_payments')->delete();
        DB::table('contract_versions')->delete();
        DB::table('contracts')->delete();

        $contracts = DB::connection('egecrm')
            ->table('contracts')
            ->get();
        $allContractVersions = DB::connection('egecrm')
            ->table('contract_versions')
            ->get();
        $bar = $this->output->createProgressBar($contracts->count());
        foreach ($contracts as $c) {
            DB::table('contracts')->insert([
                'id' => $c->id,
                'client_id' => $c->client_id,
                'year' => $c->year,
                'company' => $c->type === 'ip' ? Company::ip->name : Company::ooo->name
            ]);
            $contractVersions = $allContractVersions->where('contract_id', $c->id);
            $maxVersion = $contractVersions->max('version');
            $ids = $contractVersions->pluck('id');
            $contractSubjects = DB::connection('egecrm')
                ->table('contract_subjects')
                ->whereIn('contract_version_id', $ids)
                ->get();
            $contractPayments = DB::connection('egecrm')
                ->table('contract_payments')
                ->whereIn('contract_version_id', $ids)
                ->get();
            foreach ($contractVersions as $cv) {
                $isActive = $cv->version === $maxVersion;
                $id = DB::table('contract_versions')->insertGetId([
                    'user_id' => $this->getUserId($cv->created_email_id),
                    'contract_id' => $cv->contract_id,
                    'is_active' => $isActive,
                    'date' => $cv->date,
                    'sum' => $cv->sum,
                    'created_at' => $cv->created_at,
                    'updated_at' => $cv->updated_at,
                ]);
                foreach ($contractSubjects->where('contract_version_id', $cv->id) as $cs) {
                    $lessons = $cs->lessons ?? 0;
                    $price = $cs->price ?? 0;
                    $cvpId = DB::table('contract_version_programs')->insertGetId([
                        'contract_version_id' => $id,
                        'program' => Program::getById($c->grade_id, $cs->subject_id)->name,
                        'lessons_planned' => $cs->lessons_planned ?? 0,
                    ]);
                    DB::table('contract_version_program_prices')->insert([
                        'contract_version_program_id' => $cvpId,
                        'lessons' => $lessons,
                        'price' => $price
                    ]);
                }
                foreach ($contractPayments->where('contract_version_id', $cv->id) as $cp) {
                    DB::table('contract_version_payments')->insert([
                        'contract_version_id' => $id,
                        'sum' => $cp->sum,
                        'date' => $cp->date
                    ]);
                }
                // DB::table('contract')
            }
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}
