<?php

use App\Models\AiPrompt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('calls', 'instruction') || ! Schema::hasTable('ai_prompts')) {
            return;
        }

        $promptUpdatedAtById = DB::table('ai_prompts')
            ->whereIn('id', [AiPrompt::CALL_TRANSCRIPTION, AiPrompt::CALL_ANALYSIS])
            ->pluck('updated_at', 'id');

        $transcriptionCreatedAt = $promptUpdatedAtById->get(AiPrompt::CALL_TRANSCRIPTION);
        $analysisCreatedAt = $promptUpdatedAtById->get(AiPrompt::CALL_ANALYSIS);

        if (! is_string($transcriptionCreatedAt) || ! is_string($analysisCreatedAt)) {
            throw new \RuntimeException('Не найдены ai_prompts.updated_at для id=3 и id=4');
        }

        DB::table('calls')
            ->select(['id', 'instruction'])
            ->whereNotNull('instruction')
            ->orderBy('id')
            ->chunkById(200, function ($items) use ($transcriptionCreatedAt, $analysisCreatedAt): void {
                foreach ($items as $item) {
                    if (! is_string($item->instruction) || trim($item->instruction) === '') {
                        continue;
                    }

                    $decodedInstruction = json_decode($item->instruction, true);
                    if (! is_array($decodedInstruction)) {
                        continue;
                    }

                    $normalizedInstruction = $this->normalizeInstruction(
                        $decodedInstruction,
                        $transcriptionCreatedAt,
                        $analysisCreatedAt,
                    );

                    if ($normalizedInstruction === $decodedInstruction) {
                        continue;
                    }

                    $encodedInstruction = json_encode($normalizedInstruction, JSON_UNESCAPED_UNICODE);
                    if (! is_string($encodedInstruction)) {
                        throw new \RuntimeException("Не удалось закодировать instruction для calls.id={$item->id}");
                    }

                    DB::table('calls')
                        ->where('id', $item->id)
                        ->update(['instruction' => $encodedInstruction]);
                }
            });
    }

    public function down(): void
    {
        // Data-migration: откат не выполняем, чтобы не терять created_at в JSON.
    }

    /**
     * @param  array<string, mixed>  $instruction
     * @return array<string, mixed>
     */
    private function normalizeInstruction(
        array $instruction,
        string $transcriptionCreatedAt,
        string $analysisCreatedAt,
    ): array {
        // Сохраняем возможные дополнительные ключи и нормализуем только целевые этапы.
        $normalizedInstruction = $instruction;

        $normalizedInstruction['transcription'] = $this->normalizeInstructionItem(
            $instruction['transcription'] ?? null,
            $transcriptionCreatedAt
        );

        $normalizedInstruction['analysis'] = $this->normalizeInstructionItem(
            $instruction['analysis'] ?? null,
            $analysisCreatedAt
        );

        return $normalizedInstruction;
    }

    /**
     * @return array{text: string|null, created_at: string|null}
     */
    private function normalizeInstructionItem(mixed $value, string $defaultCreatedAt): array
    {
        if (is_string($value)) {
            return [
                'text' => $value,
                'created_at' => $defaultCreatedAt,
            ];
        }

        if (is_array($value)) {
            $text = isset($value['text']) && is_string($value['text']) ? $value['text'] : null;
            $createdAt = isset($value['created_at']) && is_string($value['created_at']) ? $value['created_at'] : null;

            if ($text !== null && $createdAt === null) {
                $createdAt = $defaultCreatedAt;
            }

            return [
                'text' => $text,
                'created_at' => $createdAt,
            ];
        }

        return [
            'text' => null,
            'created_at' => null,
        ];
    }
};
