<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Record\RecordCommandRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ كتابة وثائق الطلاب على MySQL (CQRS — Command side).
 * الجدول records يستخدم أعمدة عربية والربط بالطالب عبر الرقم الامتحاني.
 */
final class MySQLRecordCommandRepository implements RecordCommandRepository
{
    public function create(
        int $studentId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose
    ): void {
        $examNumber = DB::table('main_table')
            ->where('id', $studentId)
            ->value('الرقم الامتحاني');

        if ($examNumber === null) {
            throw new \RuntimeException('Student not found for record.', 404);
        }

        $docNum = $this->normalizeDocumentNumber($documentNumber);

        DB::transaction(function () use ($examNumber, $docNum, $documentDate, $addressee, $purpose): void {
            $nextId = (int) DB::table('records')->max('id') + 1;

            DB::table('records')->insert([
                'id' => $nextId,
                'الرقم الامتحاني' => $examNumber,
                'رقم الوثيقة' => $docNum,
                'تاريخها' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                'الجهه المعنونه اليها' => $addressee,
                'الغرض من الوثيقة' => $purpose,
            ]);
        });
    }

    /**
     * العمود رقم الوثيقة في DB من نوع integer — نُرجع null أو عدداً صحيحاً فقط.
     */
    private function normalizeDocumentNumber(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '' || !is_numeric($trimmed)) {
            return null;
        }
        return (int) $trimmed;
    }

    public function update(
        int $recordId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose
    ): void {
        $docNum = $this->normalizeDocumentNumber($documentNumber);

        DB::transaction(function () use ($recordId, $docNum, $documentDate, $addressee, $purpose): void {
            DB::table('records')
                ->where('id', $recordId)
                ->update([
                    'رقم الوثيقة' => $docNum,
                    'تاريخها' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'الجهه المعنونه اليها' => $addressee,
                    'الغرض من الوثيقة' => $purpose,
                ]);
        });
    }

    public function delete(int $recordId): void
    {
        DB::transaction(function () use ($recordId): void {
            DB::table('records')->where('id', $recordId)->delete();
        });
    }
}
