<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Record\Record;
use App\Domain\Record\RecordQueryRepository;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ قراءة وثائق الطلاب على MySQL (CQRS — Query side).
 * الجدول records يستخدم أعمدة عربية والربط بالطالب عبر الرقم الامتحاني.
 */
final class MySQLRecordQueryRepository implements RecordQueryRepository
{
    public function listByStudentId(int $studentId): array
    {
        $examNumber = DB::table('main_table')
            ->where('id', $studentId)
            ->value('الرقم الامتحاني');

        if ($examNumber === null || $examNumber === '') {
            return [];
        }

        $rows = DB::table('records')
            ->where('الرقم الامتحاني', $examNumber)
            ->orderByRaw('`تاريخها` DESC')
            ->orderBy('id', 'desc')
            ->get();

        $list = [];
        foreach ($rows as $row) {
            $list[] = Record::fromRow($this->mapRowToStdClass($row, $studentId));
        }
        return $list;
    }

    private function mapRowToStdClass(object $row, int $studentId): object
    {
        $date = $row->{'تاريخها'} ?? null;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }

        return (object) [
            'id' => $row->id ?? 0,
            'student_id' => $studentId,
            'document_number' => $row->{'رقم الوثيقة'} ?? null,
            'document_date' => $date,
            'addressee' => $row->{'الجهه المعنونه اليها'} ?? null,
            'purpose' => $row->{'الغرض من الوثيقة'} ?? null,
        ];
    }
}
