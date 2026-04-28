<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string DEFAULT_MODEL = 'gemini-3-flash-preview';

    public function up(): void
    {
        if (! Schema::hasTable('reports')) {
            return;
        }

        $hasAiInstruction = Schema::hasColumn('reports', 'ai_instruction');
        $hasAiModel = Schema::hasColumn('reports', 'ai_model');

        if (! Schema::hasColumn('reports', 'instruction')) {
            Schema::table('reports', function (Blueprint $table): void {
                // Единый снапшот фактической instruction+prompt и модели генерации отчёта.
                $table->json('instruction')->nullable()->after('ai_comment');
            });
        }

        if ($hasAiInstruction || $hasAiModel) {
            $columns = [
                'id',
                'instruction',
                'to_check_at',
            ];

            $columns[] = $hasAiInstruction
                ? 'ai_instruction'
                : DB::raw('NULL as ai_instruction');
            $columns[] = $hasAiModel
                ? 'ai_model'
                : DB::raw('NULL as ai_model');

            DB::table('reports')
                ->select($columns)
                ->orderBy('id')
                ->chunkById(200, function ($items): void {
                    foreach ($items as $item) {
                        if ($this->hasCompleteInstruction($item->instruction)) {
                            continue;
                        }

                        if (! is_string($item->ai_instruction) || $item->ai_instruction === '') {
                            continue;
                        }

                        $model = is_string($item->ai_model) && $item->ai_model !== ''
                            ? $item->ai_model
                            : self::DEFAULT_MODEL;

                        $encodedInstruction = json_encode([
                            'text' => $item->ai_instruction,
                            'model' => $model,
                            'created_at' => $item->to_check_at,
                        ], JSON_UNESCAPED_UNICODE);

                        if (! is_string($encodedInstruction)) {
                            throw new RuntimeException("Не удалось закодировать instruction для reports.id={$item->id}");
                        }

                        DB::table('reports')
                            ->where('id', $item->id)
                            ->update(['instruction' => $encodedInstruction]);
                    }
                });
        }

        if ($hasAiInstruction || $hasAiModel) {
            Schema::table('reports', function (Blueprint $table): void {
                if (Schema::hasColumn('reports', 'ai_instruction')) {
                    $table->dropColumn('ai_instruction');
                }

                if (Schema::hasColumn('reports', 'ai_model')) {
                    $table->dropColumn('ai_model');
                }
            });
        }
    }

    public function down(): void
    {
        // Data-migration: откат не делаем, чтобы не терять новое поле instruction.
    }

    private function hasCompleteInstruction(mixed $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            && isset($decoded['text'], $decoded['model'], $decoded['created_at'])
            && is_string($decoded['text'])
            && is_string($decoded['model'])
            && is_string($decoded['created_at']);
    }
};
