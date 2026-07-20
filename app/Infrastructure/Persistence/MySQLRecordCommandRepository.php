<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Record\RecordCommandRepository;
use App\Support\ArabicDigits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تنفيذ كتابة وثائق الطلاب على MySQL (CQRS — Command side).
 * يدعم البنية الحالية: إن وُجد student_id يُستخدم؛ وإلا الرقم الامتحاني مع أعمدة عربية.
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
        $examNumber = Schema::hasTable('students')
            ? DB::table('students')->where('id', $studentId)->value('exam_number')
            : DB::table('main_table')->where('id', $studentId)->value('الرقم الامتحاني');
        if ($examNumber === null) {
            throw new \RuntimeException('Student not found for record.', 404);
        }

        $docNum = $this->normalizeDocumentNumber($documentNumber);

        if (Schema::hasColumn('records', 'student_id')) {
            DB::transaction(function () use ($studentId, $docNum, $documentDate, $addressee, $purpose): void {
                DB::table('records')->insert([
                    'student_id' => $studentId,
                    'document_number' => $docNum,
                    'document_date' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'addressee' => $addressee ?? '',
                    'purpose' => $purpose ?? '',
                ]);
            });
        } else {
            DB::transaction(function () use ($examNumber, $docNum, $documentDate, $addressee, $purpose): void {
                $nextId = (int) DB::table('records')->max('id') + 1;
                DB::table('records')->insert([
                    'id' => $nextId,
                    'الرقم الامتحاني' => $examNumber,
                    'رقم الوثيقة' => $docNum,
                    'تاريخها' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'الجهه المعنونه اليها' => $addressee ?? '',
                    'الغرض من الوثيقة' => $purpose ?? '',
                ]);
            });
        }
    }

    /**
     * العمود رقم الوثيقة في بعض قواعد البيانات من نوع integer —
     * نحوّل الأرقام العربية إلى لاتينية ثم نُرجع null أو عدداً صحيحاً.
     */
    private function normalizeDocumentNumber(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $trimmed = trim(ArabicDigits::toWestern($value));
        if ($trimmed === '' || ! is_numeric($trimmed)) {
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

        if (Schema::hasColumn('records', 'document_number')) {
            DB::transaction(function () use ($recordId, $docNum, $documentDate, $addressee, $purpose): void {
                DB::table('records')
                    ->where('id', $recordId)
                    ->update([
                        'document_number' => $docNum,
                        'document_date' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                        'addressee' => $addressee ?? '',
                        'purpose' => $purpose ?? '',
                    ]);
            });
        } else {
            DB::transaction(function () use ($recordId, $docNum, $documentDate, $addressee, $purpose): void {
                DB::table('records')
                    ->where('id', $recordId)
                    ->update([
                        'رقم الوثيقة' => $docNum,
                        'تاريخها' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                        'الجهه المعنونه اليها' => $addressee ?? '',
                        'الغرض من الوثيقة' => $purpose ?? '',
                    ]);
            });
        }
    }

    public function delete(int $recordId): void
    {
        DB::transaction(function () use ($recordId): void {
            DB::table('records')->where('id', $recordId)->delete();
        });
    }
}
