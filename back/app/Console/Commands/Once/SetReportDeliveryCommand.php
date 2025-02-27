<?php

namespace App\Console\Commands\Once;

use App\Enums\LogType;
use App\Enums\Program;
use App\Enums\ReportDelivery;
use App\Enums\ReportStatus;
use App\Enums\TeacherStatus;
use App\Enums\TelegramTemplate;
use App\Models\ClientParent;
use App\Models\Log;
use App\Models\Report;
use App\Models\Teacher;
use App\Models\TelegramMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SetReportDeliveryCommand extends Command
{
    protected $signature = 'once:set-report-delivery';

    protected $description = 'Проставить Report delivery';

    public function handle(): void
    {
        DB::table('reports')->update([
            'delivery' => null,
        ]);

        $telegramMessages = TelegramMessage::where('template', TelegramTemplate::reportRead)->get();

        $errors = 0;
        foreach ($telegramMessages as $telegramMessage) {
            $parent = ClientParent::find($telegramMessage->entity_id);
            preg_match('/<b>Программа:<\/b>\s*(.+)/u', $telegramMessage->text, $matchesProgram);
            preg_match('/<b>Составитель отчета:<\/b>\s*(.+)/u', $telegramMessage->text, $matchesTeacher);
            $programText = trim($matchesProgram[1] ?? '');
            $programLayer = $this->getProgramLayer($programText);
            $teacherText = trim($matchesTeacher[1] ?? '');
            [$lastName, $firstName, $middleName] = explode(' ', $teacherText);
            $teacher = Teacher::query()
                ->where('status', TeacherStatus::active)
                ->where('last_name', $lastName)
                ->first();

            $dateLimit = Carbon::parse($telegramMessage->created_at)->subWeeks(2)->format('Y-m-d H:i:s');
            $reports = Report::query()
                ->whereRaw('created_at BETWEEN ? AND ?', [$dateLimit, $telegramMessage->created_at])
                ->where('year', 2024)
                ->where('client_id', $parent->client->id)
                ->where('teacher_id', $teacher->id)
                ->get();

            $text = implode("\t", [
                $telegramMessage->id,
                $parent->client->id,
                $teacher->id,
                $reports->count(),
            ]);

            if ($reports->count() > 1) {
                $cnt = 0;
                $correctReport = null;
                foreach ($reports as $report) {
                    $layer = $this->getProgramLayer($report->program->getName());
                    if ($layer === $programLayer) {
                        $cnt++;
                        $correctReport = $report;
                    }
                }
                if ($cnt !== 1) {
                    $this->error($text);
                    $this->info("Program layer: $programLayer\tReport layer: $layer");
                    $this->info(PHP_EOL);
                    $errors++;
                } else {
                    $this->line($text);
                    DB::table('reports')->whereId($correctReport->id)->update([
                        'delivery' => ReportDelivery::read,
                    ]);
                }
            } else {
                $this->line($text);
                if ($reports->count() === 1) {
                    DB::table('reports')->whereId($reports->first()->id)->update([
                        'delivery' => ReportDelivery::read,
                    ]);
                }
            }
        }

        $this->line("\n\nErrors: ".$errors);
    }

    private function getProgramLayer(string $programText): ?int
    {
        $programText = str($programText)->lower();
        foreach ([
            'мат',
            'физ',
            'хим',
            'био',
            'инф',
            'рус',
            'лит',
            'общ',
            'ист',
            'анг',
        ] as $index => $str) {
            if ($programText->contains($str)) {
                return $index;
            }
        }

        return null;
    }

    private function getProgram(string $programText): ?Program
    {
        foreach (Program::cases() as $program) {
            if ($program->getName() === $programText) {
                return $program;
            }
        }

        return null;
    }

    private function solution1()
    {
        $reports = Report::where('created_at', '>=', '2024-12-16 00:00:00')
            ->where('status', ReportStatus::published)
            ->get();

        $bar = $this->output->createProgressBar($reports->count());
        foreach ($reports as $report) {
            $telegramId = $report->client->parent->phones()->whereNotNull('telegram_id')->first()->telegram_id;
            if (! $telegramId) {
                continue;
            }

            $logs = Log::query()
                ->where('table', 'telegram_messages')
                ->where('type', LogType::create)
                ->where('data', 'like', '%'.$report->homework_comment.'%')
                ->where('data', 'like', '%"telegram_id": '.$telegramId.'%')
                ->count();

            $this->line($report->id.': '.$logs);

            // $bar->advance();
        }
        $bar->finish();
    }
}
