<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Record\Record;
use App\Domain\Record\RecordQueryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تنفيذ قراءة وثائق الطلاب على MySQL (CQRS — Query side).
 * يدعم البنية الحالية: الربط بالطالب عبر الرقم الامتحاني، أعمدة عربية (رقم الوثيقة، تاريخها، الجهه المعنونه اليها، الغرض من الوثيقة).
 * إن وُجد عمود student_id في الجدول يُستخدم للفلترة؛ وإلا يُستخرج الرقم الامتحاني من main_table.
 */
final class MySQLRecordQueryRepository implements RecordQueryRepository
{
    private const MAX_RECORDS_PER_STUDENT = 500;

    public function listByStudentId(int $studentId): array
    {
        if ($this->recordsHasStudentId()) {
            $rows = DB::table('records')
                ->where('student_id', $studentId)
                ->orderByRaw($this->documentDateOrderColumn().' DESC')
                ->orderBy('id', 'desc')
                ->limit(self::MAX_RECORDS_PER_STUDENT)
                ->get();
        } else {
            $examNumber = Schema::hasTable('students')
                ? DB::table('students')->where('id', $studentId)->value('exam_number')
                : DB::table('main_table')->where('id', $studentId)->value('الرقم الامتحاني');

            if ($examNumber === null || $examNumber === '') {
                return [];
            }

            $rows = DB::table('records')
                ->where('الرقم الامتحاني', $examNumber)
                ->orderByRaw('`تاريخها` DESC')
                ->orderBy('id', 'desc')
                ->limit(self::MAX_RECORDS_PER_STUDENT)
                ->get();
        }

        $list = [];
        foreach ($rows as $row) {
            $list[] = Record::fromRow($this->mapRowToStdClass($row, $studentId));
        }

        return $list;
    }

    private function recordsHasStudentId(): bool
    {
        return Schema::hasColumn('records', 'student_id');
    }

    private function documentDateOrderColumn(): string
    {
        return Schema::hasColumn('records', 'document_date') ? 'document_date' : '`تاريخها`';
    }

    private function mapRowToStdClass(object $row, int $studentId): object
    {
        $date = $row->document_date ?? $row->{'تاريخها'} ?? null;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        } elseif ($date !== null && $date !== '') {
            $date = \App\Support\ImportDateNormalizer::toYmd($date) ?? (string) $date;
        } else {
            $date = null;
        }

        $docNumber = $row->document_number ?? $row->{'رقم الوثيقة'} ?? null;
        $addressee = $row->addressee ?? $row->{'الجهه المعنونه اليها'} ?? null;
        $purpose = $row->purpose ?? $row->{'الغرض من الوثيقة'} ?? null;
        $notes = $row->notes ?? $row->{'الملاحظات'} ?? null;

        return (object) [
            'id' => $row->id ?? 0,
            'student_id' => (int) ($row->student_id ?? $studentId),
            'document_number' => isset($docNumber) ? (string) $docNumber : null,
            'document_date' => $date,
            'addressee' => isset($addressee) ? (string) $addressee : null,
            'purpose' => isset($purpose) ? (string) $purpose : null,
            'notes' => isset($notes) ? (string) $notes : null,
        ];
    }
}
