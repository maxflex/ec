<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reports', 'ai_instruction')) {
            return;
        }

        DB::table('reports')
            ->select(['id', 'ai_instruction'])
            ->whereNotNull('ai_instruction')
            ->orderBy('id')
            ->chunkById(200, function ($items): void {
                foreach ($items as $item) {
                    $normalized = $this->toNewFormat((string) $item->ai_instruction);

                    if ($normalized !== $item->ai_instruction) {
                        DB::table('reports')
                            ->where('id', $item->id)
                            ->update(['ai_instruction' => $normalized]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('reports', 'ai_instruction')) {
            return;
        }

        DB::table('reports')
            ->select(['id', 'ai_instruction'])
            ->whereNotNull('ai_instruction')
            ->orderBy('id')
            ->chunkById(200, function ($items): void {
                foreach ($items as $item) {
                    $legacy = $this->toLegacyFormat((string) $item->ai_instruction);

                    if ($legacy !== $item->ai_instruction) {
                        DB::table('reports')
                            ->where('id', $item->id)
                            ->update(['ai_instruction' => $legacy]);
                    }
                }
            });
    }

    private function toNewFormat(string $value): string
    {
        $delimiter = '<USER_PROMPT>';

        // Если запись уже в новом формате, нормализуем отступы и переносы.
        if (str_contains($value, $delimiter)) {
            [$instruction, $prompt] = array_pad(explode($delimiter, $value, 2), 2, '');

            return trim($instruction)."\n\n{$delimiter}\n\n".trim($prompt);
        }

        $systemMarker = '[SYSTEM INSTRUCTION]';
        $promptMarker = '[USER PROMPT]';

        $systemPos = strpos($value, $systemMarker);
        $promptPos = strpos($value, $promptMarker);

        // Старый формат: [SYSTEM INSTRUCTION] ... [USER PROMPT] ...
        if ($systemPos !== false || $promptPos !== false) {
            $instruction = $systemPos !== false
                ? substr($value, $systemPos + strlen($systemMarker), $promptPos === false ? null : $promptPos - ($systemPos + strlen($systemMarker)))
                : '';

            $prompt = $promptPos !== false
                ? substr($value, $promptPos + strlen($promptMarker))
                : '';

            return trim((string) $instruction)."\n\n{$delimiter}\n\n".trim((string) $prompt);
        }

        // Если формат неизвестен — считаем это instruction без prompt.
        return trim($value)."\n\n{$delimiter}\n\n";
    }

    private function toLegacyFormat(string $value): string
    {
        $delimiter = '<USER_PROMPT>';

        if (! str_contains($value, $delimiter)) {
            return $value;
        }

        [$instruction, $prompt] = array_pad(explode($delimiter, $value, 2), 2, '');

        return "[SYSTEM INSTRUCTION]\n\n".trim($instruction)."\n\n[USER PROMPT]\n\n".trim($prompt);
    }
};
