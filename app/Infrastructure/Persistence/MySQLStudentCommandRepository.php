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
        'gender' => 'الجنس',
        'branch' => 'الفرع',
        'major' => 'الاختصاص',
        'academic_year' => 'العام الدراسي',
        'result' => 'النتيجة',
        'total' => 'المجموع',
        'average' => 'المعدل',
        'round' => 'الدور',
    ];

    private const CREATE_FIELDS_MAP = [
        'exam_number' => 'الرقم الامتحاني',
        'name_student' => 'اسم الطالب',
        'name_father' => 'اسم الاب',
        'name_grandfather' => 'اسم الجد',
        'name_surname' => 'اللقب',
        'birth_date' => 'التولد',
        'birth_place' => 'محل الولادة',
        'mother_full_name' => 'اسم الام الكامل',
        'gender' => 'الجنس',
        'branch' => 'الفرع',
        'major' => 'الاختصاص',
        'academic_year' => 'العام الدراسي',
        'last_school' => 'اخر مدرسة كان فيها الطالب',
        'middle_doc_number' => 'رقم الوثيقة المتوسطة',
        'middle_doc_date' => 'تاريخها',
        'issuing_authority' => 'جهة الاصدار',
    ];

    public function create(array $data): int
    {
        return DB::transaction(function () use ($data): int {
            $row = [];
            foreach (self::CREATE_FIELDS_MAP as $key => $column) {
                $value = isset($data[$key]) ? trim((string) $data[$key]) : null;
                // الرقم الامتحاني: إذا تُرك فارغاً نضعه 0
                if ($key === 'exam_number') {
                    $row[$column] = $value !== null && $value !== '' ? $value : '0';
                    continue;
                }

                // حقل التولد وحقل تاريخ الوثيقة: إذا تُركا فارغين نضع لهما تاريخاً افتراضياً
                // بدلاً من NULL أو فراغ لتفادي مشاكل تواريخ غير صالحة مع MySQL STRICT.
                if ($key === 'birth_date' || $key === 'middle_doc_date') {
                    $row[$column] = $value !== null && $value !== '' ? $value : '1000-01-01';
                    continue;
                }

                // باقي الحقول: إذا لم تُرسل القيمة أو كانت فارغة، نخزّن فراغاً بدلاً من NULL
                $row[$column] = $value !== null ? $value : '';
            }
            if (empty($row)) {
                throw new \InvalidArgumentException('لا توجد بيانات طالب للإدراج.');
            }

            // تعبئة جميع أعمدة الدرجات بقيم عددية 0 بشكل افتراضي عند إنشاء الطالب
            // لأن أعمدة المواد معرّفة كأعمدة عددية في قاعدة البيانات، و MySQL لا يقبل
            // إدخال فراغ '' في أعمدة من نوع INT عند تفعيل STRICT.
            $gradeColumns = Config::get('grades_catalog.grade_columns', []);
            foreach ($gradeColumns as $subjectColumn) {
                if (!array_key_exists($subjectColumn, $row)) {
                    $row[$subjectColumn] = 0;
                }
            }

            // الحقول المشتقة / الإدارية التي لا تُملأ من نموذج إضافة الطالب
            // نضبط الحقول الرقمية منها إلى 0 والنصية إلى فراغ افتراضياً عند الإنشاء.
            $numericMetaColumns = [
                'المجموع',
                'المعدل',
            ];
            foreach ($numericMetaColumns as $metaColumn) {
                if (!array_key_exists($metaColumn, $row)) {
                    $row[$metaColumn] = 0;
                }
            }

            $stringMetaColumns = [
                'النتيجة',
                'الدور',
                'الجهة المعنون اليها التاييد',
                'منظم التاييد',
                'مدير القسم او الشعبة',
                'المنصب',
            ];
            foreach ($stringMetaColumns as $metaColumn) {
                if (!array_key_exists($metaColumn, $row)) {
                    $row[$metaColumn] = '';
                }
            }

            $nextId = (int) DB::table('main_table')->max('id') + 1;
            $row['id'] = $nextId;
            DB::table('main_table')->insert($row);
            return $nextId;
        });
    }

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
