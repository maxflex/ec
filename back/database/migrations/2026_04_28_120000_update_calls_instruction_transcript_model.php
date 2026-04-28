<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string MODEL_FLASH = 'gemini-3-flash-preview';
    private const string MODEL_TRANSCRIPT_NEW = 'gemini-3.1-pro-preview';
    private const string TRANSCRIPT_MODEL_SWITCH_AT = '2026-04-27 15:00:00';

    public function up(): void
    {
        if (! Schema::hasColumn('calls', 'instruction')) {
            return;
        }

        DB::table('calls')
            ->select(['id', 'instruction'])
            ->whereNotNull('instruction')
            ->orderBy('id')
            ->chunkById(200, function ($items): void {
                foreach ($items as $item) {
                    if (! is_string($item->instruction) || trim($item->instruction) === '') {
                        continue;
                    }

                    $decodedInstruction = json_decode($item->instruction, true);
                    if (! is_array($decodedInstruction)) {
                        continue;
                    }

                    $normalizedInstruction = $this->normalizeInstruction($decodedInstruction);
                    if ($normalizedInstruction === $decodedInstruction) {
                        continue;
                    }

                    $encodedInstruction = json_encode($normalizedInstruction, JSON_UNESCAPED_UNICODE);
                    if (! is_string($encodedInstruction)) {
                        throw new RuntimeException("Не удалось закодировать instruction для calls.id={$item->id}");
                    }

                    DB::table('calls')
                        ->where('id', $item->id)
                        ->update(['instruction' => $encodedInstruction]);
                }
            });
    }

    public function down(): void
    {
        // Data-migration: откат не делаем, чтобы не терять model и новый ключ transcript.
    }

    /**
     * @param  array<string, mixed>  $instruction
     * @return array<string, mixed>
     */
    private function normalizeInstruction(array $instruction): array
    {
        $normalizedInstruction = $instruction;

        // Переезжаем на новое имя ключа и удаляем legacy-ключ transcription.
        $normalizedInstruction['transcript'] = $this->normalizeTranscriptItem(
            $instruction['transcript'] ?? $instruction['transcription'] ?? null,
        );

        $normalizedInstruction['analysis'] = $this->normalizeAnalysisItem(
            $instruction['analysis'] ?? null,
        );

        unset($normalizedInstruction['transcription']);

        return $normalizedInstruction;
    }

    /**
     * @return array{text: string, model: string, created_at: string}|null
     */
    private function normalizeTranscriptItem(mixed $value): ?array
    {
        if (is_array($value)) {
            $text = isset($value['text']) && is_string($value['text']) ? $value['text'] : null;
            $createdAt = isset($value['created_at']) && is_string($value['created_at']) ? $value['created_at'] : null;

            if ($text === null || $createdAt === null) {
                return null;
            }

            $model = isset($value['model']) && is_string($value['model'])
                ? $value['model']
                : ($createdAt >= self::TRANSCRIPT_MODEL_SWITCH_AT ? self::MODEL_TRANSCRIPT_NEW : self::MODEL_FLASH);

            return [
                'text' => $text,
                'model' => $model,
                'created_at' => $createdAt,
            ];
        }

        return null;
    }

    /**
     * @return array{text: string, model: string, created_at: string}|null
     */
    private function normalizeAnalysisItem(mixed $value): ?array
    {
        if (is_array($value)) {
            $text = isset($value['text']) && is_string($value['text']) ? $value['text'] : null;
            $createdAt = isset($value['created_at']) && is_string($value['created_at']) ? $value['created_at'] : null;

            if ($text === null || $createdAt === null) {
                return null;
            }

            $model = isset($value['model']) && is_string($value['model'])
                ? $value['model']
                : self::MODEL_FLASH;

            return [
                'text' => $text,
                'model' => $model,
                'created_at' => $createdAt,
            ];
        }

        return null;
    }
};
