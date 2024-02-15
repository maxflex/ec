<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Grade;
use App\Enums\Subject;
use App\Models\ContractVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferContracts extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:contracts';
    protected $description = 'Transfer contracts';

    public function handle()
    {
        DB::table('contract_subjects')->delete();
        DB::table('contract_payments')->delete();
        DB::table('contract_versions')->delete();
        DB::table('contracts')->delete();

        $contracts = DB::connection('egecrm')
            ->table('contracts')
            ->get();
        $bar = $this->output->createProgressBar($contracts->count());
        foreach ($contracts as $c) {
            DB::table('contracts')->insert([
                'id' => $c->id,
                'client_id' => $c->client_id,
                'grade' => Grade::getById($c->grade_id)->name,
                'year' => $c->year,
                'is_ip' => $c->type === 'ip'
            ]);
            $contractVersions = DB::connection('egecrm')
                ->table('contract_versions')
                ->where('contract_id', $c->id)
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
                $contractSubjects = DB::connection('egecrm')
                    ->table('contract_subjects')
                    ->where('contract_version_id', $cv->id)
                    ->get();
                foreach ($contractSubjects as $cs) {
                    DB::table('contract_subjects')->insert([
                        'contract_version_id' => $id,
                        'subject' => Subject::getById($cs->subject_id)->name,
                        'lessons' => $cs->lessons ?? 0,
                        'lessons_planned' => $cs->lessons_planned ?? 0,
                        'price' => $cs->price ?? 0,
                        'is_closed' => $cs->status === 'terminated',
                    ]);
                }
                $contractPayments = DB::connection('egecrm')
                    ->table('contract_payments')
                    ->where('contract_version_id', $cv->id)
                    ->get();
                foreach ($contractPayments as $cp) {
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
