<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\StudentCommandRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * تنفيذ كتابة الطلاب على MySQL (CQRS — Command side)
 */
final class MySQLStudentCommandRepository implements StudentCommandRepository
{
    private const BASIC_FIELDS = [
        'name_student' => 'اسم الطالب',
        'name_father' => 'اسم الاب',
        'name_grandfather' => 'اسم الجد',
        'name_surname' => 'اللقب',
        'exam_number' => 'الرقم الامتحاني',
        'branch' => 'الفرع',
        'major' => 'الاختصاص',
        'academic_year' => 'العام الدراسي',
        'result' => 'النتيجة',
        'total' => 'المجموع',
        'average' => 'المعدل',
        'round' => 'الدور',
    ];

    public function updateGrades(int $id, array $payload): bool
    {
        return DB::transaction(function () use ($id, $payload): bool {
            $data = [];

            // الحقول الأساسية (الاسم، الرقم، الفرع، ... إلخ)
            foreach (self::BASIC_FIELDS as $key => $column) {
                if (array_key_exists($key, $payload)) {
                    $value = trim((string) $payload[$key]);
                    $data[$column] = $value;
                }
            }

            // أعمدة الدرجات (المواد)
            $gradeColumns = Config::get('grades_catalog.grade_columns', []);
            $allowedGrades = array_fill_keys($gradeColumns, true);
            $grades = $payload['grades'] ?? [];
            if (is_array($grades)) {
                foreach ($grades as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $subject = trim((string) ($row['subject'] ?? ''));
                    $score = trim((string) ($row['score'] ?? ''));
                    if ($subject !== '' && isset($allowedGrades[$subject])) {
                        // اسم العمود هو اسم المادة من الكتالوج
                        $data[$subject] = $score;
                    }
                }
            }

            if (empty($data)) {
                // لا يوجد شيء لتحديثه
                return false;
            }

            $affected = DB::table('main_table')
                ->where('id', $id)
                ->update($data);

            return $affected > 0;
        });
    }

    public function deleteStudent(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $affected = DB::table('main_table')
                ->where('id', $id)
                ->delete();

            return $affected > 0;
        });
    }
}
