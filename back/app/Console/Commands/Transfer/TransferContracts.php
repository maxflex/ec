<?php

namespace App\Console\Commands\Transfer;

use App\Enums\CompanyType;
use App\Enums\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferContracts extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:contracts';
    protected $description = 'Transfer contracts';

    public function handle()
    {
        DB::table('contract_programs')->delete();
        DB::table('contract_payments')->delete();
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
                'company' => $c->type === 'ip' ? CompanyType::ip->name : CompanyType::ooo->name
            ]);
            $contractVersions = $allContractVersions->where('contract_id', $c->id);
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
                $id = DB::table('contract_versions')->insertGetId([
                    'user_id' => $this->getUserId($cv->created_email_id),
                    'contract_id' => $cv->contract_id,
                    'version' => $cv->version,
                    'date' => $cv->date,
                    'sum' => $cv->sum,
                    'created_at' => $cv->updated_at,
                    'updated_at' => $cv->updated_at,
                ]);
                foreach ($contractSubjects->where('contract_version_id', $cv->id) as $cs) {
                    DB::table('contract_programs')->insert([
                        'contract_version_id' => $id,
                        'program' => Program::getById($c->grade_id, $cs->subject_id)->name,
                        'lessons' => $cs->lessons ?? 0,
                        'lessons_planned' => $cs->lessons_planned ?? 0,
                        'price' => $cs->price ?? 0,
                        'is_closed' => $cs->status === 'terminated',
                    ]);
                }
                foreach ($contractPayments->where('contract_version_id', $cv->id) as $cp) {
                    DB::table('contract_payments')->insert([
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
    }
}
