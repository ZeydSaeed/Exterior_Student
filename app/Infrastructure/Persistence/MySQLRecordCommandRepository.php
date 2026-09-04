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
        ?string $purpose,
        ?string $notes = null
    ): void {
        $examNumber = Schema::hasTable('students')
            ? DB::table('students')->where('id', $studentId)->value('exam_number')
            : DB::table('main_table')->where('id', $studentId)->value('الرقم الامتحاني');
        if ($examNumber === null) {
            throw new \RuntimeException('Student not found for record.', 404);
        }

        $docNum = $this->normalizeDocumentNumber($documentNumber);

        if (Schema::hasColumn('records', 'student_id')) {
            DB::transaction(function () use ($studentId, $docNum, $documentDate, $addressee, $purpose, $notes): void {
                $row = [
                    'student_id' => $studentId,
                    'document_number' => $docNum,
                    'document_date' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'addressee' => $addressee ?? '',
                    'purpose' => $purpose ?? '',
                ];
                if (Schema::hasColumn('records', 'notes')) {
                    $row['notes'] = $notes ?? '';
                }
                DB::table('records')->insert($row);
            });
        } else {
            DB::transaction(function () use ($examNumber, $docNum, $documentDate, $addressee, $purpose, $notes): void {
                $nextId = (int) DB::table('records')->max('id') + 1;
                $row = [
                    'id' => $nextId,
                    'الرقم الامتحاني' => $examNumber,
                    'رقم الوثيقة' => $docNum,
                    'تاريخها' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'الجهه المعنونه اليها' => $addressee ?? '',
                    'الغرض من الوثيقة' => $purpose ?? '',
                ];
                if (Schema::hasColumn('records', 'الملاحظات')) {
                    $row['الملاحظات'] = $notes ?? '';
                }
                DB::table('records')->insert($row);
            });
        }
    }

    /**
     * العمود رقم الوثيقة قد يكون نصاً أو عدداً صحيحاً حسب بنية قاعدة البيانات.
     */
    private function normalizeDocumentNumber(?string $value): int|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        $trimmed = trim(ArabicDigits::toWestern($value));
        if ($trimmed === '') {
            return null;
        }

        if (Schema::hasColumn('records', 'document_number')) {
            return $trimmed;
        }

        if (! is_numeric($trimmed)) {
            return null;
        }

        return (int) $trimmed;
    }

    public function update(
        int $recordId,
        ?string $documentNumber,
        ?string $documentDate,
        ?string $addressee,
        ?string $purpose,
        ?string $notes = null
    ): void {
        $docNum = $this->normalizeDocumentNumber($documentNumber);

        if (Schema::hasColumn('records', 'document_number')) {
            DB::transaction(function () use ($recordId, $docNum, $documentDate, $addressee, $purpose, $notes): void {
                $row = [
                    'document_number' => $docNum,
                    'document_date' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'addressee' => $addressee ?? '',
                    'purpose' => $purpose ?? '',
                ];
                if (Schema::hasColumn('records', 'notes')) {
                    $row['notes'] = $notes ?? '';
                }
                DB::table('records')
                    ->where('id', $recordId)
                    ->update($row);
            });
        } else {
            DB::transaction(function () use ($recordId, $docNum, $documentDate, $addressee, $purpose, $notes): void {
                $row = [
                    'رقم الوثيقة' => $docNum,
                    'تاريخها' => $documentDate !== null && $documentDate !== '' ? $documentDate : null,
                    'الجهه المعنونه اليها' => $addressee ?? '',
                    'الغرض من الوثيقة' => $purpose ?? '',
                ];
                if (Schema::hasColumn('records', 'الملاحظات')) {
                    $row['الملاحظات'] = $notes ?? '';
                }
                DB::table('records')
                    ->where('id', $recordId)
                    ->update($row);
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
