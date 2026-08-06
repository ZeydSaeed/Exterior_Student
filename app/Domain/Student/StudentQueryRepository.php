<?php

namespace App\Domain\Student;

/**
 * واجهة قراءة الطلاب (CQRS — Query side)
 */
interface StudentQueryRepository
{
    /**
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     */
    public function listWithFilters(array $filters): StudentListProjection;

    /**
     * معرفات الطلاب المطابقة للفلاتر (لطباعة القيود دفعة واحدة، بدون تقسيم صفحات).
     *
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     * @return list<int>
     */
    public function listIdsWithFilters(array $filters): array;

    /**
     * عدد الطلاب المطابقين للفلاتر (للإحصائيات).
     *
     * @param  array{branch?: string|null, major?: string|null, gender?: string|null, year?: string|null, round?: string|null, result?: string|null, search?: string|null}  $filters
     */
    public function countWithFilters(array $filters): int;

    /**
     * عدد الذكور والإناث ضمن الطلاب المطابقين لنفس الفلاتر.
     *
     * @param  array{branch?: string|null, major?: string|null, gender?: string|null, year?: string|null, round?: string|null, result?: string|null, search?: string|null}  $filters
     * @return array{male:int,female:int}
     */
    public function countGendersWithFilters(array $filters): array;

    /**
     * قوائم خيارات الفلترة للواجهة.
     *
     * @return array{
     *   academicYears:\Illuminate\Support\Collection,
     *   branches:\Illuminate\Support\Collection,
     *   majors:\Illuminate\Support\Collection,
     *   genders:\Illuminate\Support\Collection,
     *   resultOptions:\Illuminate\Support\Collection,
     *   roundOptions:\Illuminate\Support\Collection
     * }
     */
    public function getFilterLists(): array;

    /**
     * معرفات الطلاب الراسبين/المعيدين المطابقة للفلاتر (لحذف جماعي).
     *
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     * @return list<int>
     */
    public function listFailedIdsWithFilters(array $filters): array;

    /**
     * تقرير الطلبة المعيدين حسب الفلاتر.
     *
     * @param  array{branch?: string, major?: string, gender?: string, year?: string, search?: string}  $filters
     * @return array{
     *   groups:list<array{
     *     branch:string,
     *     major:string,
     *     students:list<array{
     *       id:int,
     *       exam_number:string,
     *       full_name:string,
     *       subjects:list<array{subject:string, score:string}>,
     *       total:string,
     *       average:string,
     *       result:string
     *     }>,
     *     count:int
     *   }>,
     *   stats:array{total_repeaters:int},
     *   filters:array{academicYears:\Illuminate\Support\Collection, branches:\Illuminate\Support\Collection, majors:\Illuminate\Support\Collection, genders:\Illuminate\Support\Collection}
     * }
     */
    public function listRepeatersReport(array $filters): array;

    /** بيانات درجات طالب واحد (للمودال) — يُرجع null إن لم يُوجَد */
    public function getGradesById(int $id): ?StudentGradesView;

    /** جلب كيان طالب واحد (لاستخدام قواعد الدومين مثل الحذف) */
    public function getStudentById(int $id): ?Student;

    /**
     * معرف الطالب التالي في قائمة الطلاب المفلترة بنفس ترتيب الجدول.
     * يعيد null إن لم يوجد طالب لاحق أو الطالب الحالي غير موجود في القائمة.
     *
     * @param  array{branch?: string|null, major?: string|null, gender?: string|null, year?: string|null, round?: string|null, search?: string|null}  $filters
     */
    public function findNextStudentIdInList(int $currentStudentId, array $filters): ?int;

    /**
     * معرف الطالب السابق في قائمة الطلاب المفلترة بنفس ترتيب الجدول.
     * يعيد null إن لم يوجد طالب سابق أو الطالب الحالي غير موجود في القائمة.
     *
     * @param  array{branch?: string|null, major?: string|null, gender?: string|null, year?: string|null, round?: string|null, search?: string|null}  $filters
     */
    public function findPreviousStudentIdInList(int $currentStudentId, array $filters): ?int;

    /**
     * معرف الطالب التالي حسب ترتيب الرقم الامتحاني تصاعدياً (بدون فلاتر القائمة).
     * يعيد null إن لم يوجد طالب لاحق.
     */
    public function findNextStudentIdByExamNumber(string $examNumber): ?int;

    /** هل يوجد طالب بالرقم الامتحاني (لاستخدامه في التحقق من التكرار عند الاستيراد) */
    public function existsExamNumber(string $examNumber): bool;

    /**
     * جلب بيانات الطالب الأساسية من الرقم الامتحاني (لاستيراد النتائج).
     * يعيد null إذا لم يوجد.
     *
     * @return object{id:int, exam_number:string, full_name:string, branch:string, major:string, academic_year:string}|null
     */
    public function findByExamNumber(string $examNumber): ?object;

    /** بيانات طالب كاملة لصفحة سجل القيد (قيد الطالب) */
    public function getStudentDocumentInfo(int $id): ?StudentDocumentInfo;

    /**
     * قائمة الأعوام الدراسية لاستخدامها في نموذج إضافة طالب (من main_table + السنة الحالية).
     *
     * @return list<string>
     */
    public function getAcademicYearsForForm(): array;
}
