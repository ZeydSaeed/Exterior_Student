<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * قراءة ملف Excel للطلاب.
 * الصف الأول = عناوين الأعمدة، الباقي = بيانات.
 * العناوين المتوقعة: الرقم الامتحاني، اسم الطالب، اسم الاب، اسم الجد، اللقب، الجنس، التولد، محل الولادة، اسم الام الكامل، الفرع، الاختصاص، العام الدراسي، اخر مدرسة، رقم الوثيقة، تاريخها، جهة الاصدار
 */
final class StudentsExcelImport implements ToArray
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(array $array): array
    {
        return $array;
    }
}
