<?php

namespace App\Console\Commands;

use App\Models\ContractVersionProgram;
use DB;
use Illuminate\Console\Command;

class SetContractVersionProgramStatusCommand extends Command
{
    protected $signature = 'app:set-contract-version-program-status';

    protected $description = 'Reset statuses in ContractVersionProgram';

    public function handle(): void
    {
        DB::table('contract_version_programs')->update([
            'status' => null,
        ]);

        $contractVersionPrograms = ContractVersionProgram::query()
            ->whereHas('contractVersion', fn ($q) => $q->where('is_active', true))
            ->get();

        $bar = $this->output->createProgressBar(count($contractVersionPrograms));

        foreach ($contractVersionPrograms as $cvp) {
            $cvp->updateStatus();
            $bar->advance();
        }
        $bar->finish();
    }
}
