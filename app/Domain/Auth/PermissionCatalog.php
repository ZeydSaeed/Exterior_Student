<?php

namespace App\Domain\Auth;

/**
 * كتالوج الصلاحيات الثابت للنظام (بدون اعتماد على الإطار).
 */
final class PermissionCatalog
{
    public const NAV_DASHBOARD = 'nav.dashboard';

    public const NAV_STUDENTS = 'nav.students';

    public const NAV_DOCUMENTS_BULK = 'nav.documents_bulk';

    public const NAV_IMPORT_STUDENTS = 'nav.import_students';

    public const NAV_IMPORT_RESULTS = 'nav.import_results';

    public const NAV_FAILURES = 'nav.failures';

    public const NAV_REPEATERS = 'nav.repeaters';

    public const NAV_STATISTICS = 'nav.statistics';

    public const TOOLBAR_EMPLOYEES = 'toolbar.employees';

    public const TOOLBAR_ADD_STUDENT = 'toolbar.add_student';

    public const TOOLBAR_BACKUP = 'toolbar.backup';

    public const TOOLBAR_ACCOUNTS = 'toolbar.accounts';

    public const STUDENTS_CREATE = 'students.create';

    public const STUDENTS_EDIT = 'students.edit';

    public const STUDENTS_DELETE = 'students.delete';

    public const STUDENTS_GRADES_VIEW = 'students.grades.view';

    public const STUDENTS_GRADES_EDIT = 'students.grades.edit';

    public const STUDENTS_CERTIFICATE_VIEW = 'students.certificate.view';

    public const STUDENTS_CERTIFICATE_PRINT = 'students.certificate.print';

    public const STUDENTS_CERTIFICATE_GRADES_VIEW = 'students.certificate_grades.view';

    public const STUDENTS_CERTIFICATE_GRADES_PRINT = 'students.certificate_grades.print';

    public const STUDENTS_DOCUMENT_VIEW = 'students.document.view';

    public const STUDENTS_DOCUMENT_EDIT = 'students.document.edit';

    public const STUDENTS_DOCUMENT_PRINT = 'students.document.print';

    public const STUDENTS_DOCUMENTS_VIEW = 'students.documents.view';

    public const STUDENTS_DOCUMENTS_CREATE = 'students.documents.create';

    public const STUDENTS_DOCUMENTS_EDIT = 'students.documents.edit';

    public const STUDENTS_DOCUMENTS_DELETE = 'students.documents.delete';

    public const STUDENTS_PROFILE_VIEW = 'students.profile.view';

    public const STUDENTS_PROFILE_ATTESTATION_CREATE = 'students.profile.attestation.create';

    public const STUDENTS_PROFILE_ATTESTATION_EDIT = 'students.profile.attestation.edit';

    public const STUDENTS_PROFILE_ATTESTATION_DELETE = 'students.profile.attestation.delete';

    public const STUDENTS_PROFILE_NOTE_CREATE = 'students.profile.note.create';

    public const STUDENTS_PROFILE_NOTE_EDIT = 'students.profile.note.edit';

    public const STUDENTS_PROFILE_NOTE_DELETE = 'students.profile.note.delete';

    public const EMPLOYEES_MANAGE = 'employees.manage';

    public const USERS_MANAGE = 'users.manage';

    public const BACKUP_CREATE = 'backup.create';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * مجموعات الصلاحيات للعرض في واجهة الإدارة.
     *
     * @return array<string, array{label: string, permissions: array<string, string>}>
     */
    public static function groups(): array
    {
        return [
            'navigation' => [
                'label' => 'أيقونات القائمة الجانبية',
                'permissions' => [
                    self::NAV_DASHBOARD => 'الصفحة الرئيسية',
                    self::NAV_STUDENTS => 'بيانات الطلبة',
                    self::NAV_DOCUMENTS_BULK => 'القيود (طباعة جماعية)',
                    self::NAV_IMPORT_STUDENTS => 'استيراد بيانات الطلبة من اكسل',
                    self::NAV_IMPORT_RESULTS => 'استيراد النتائج من اكسل',
                    self::NAV_FAILURES => 'الراسبين',
                    self::NAV_REPEATERS => 'المعيدين',
                    self::NAV_STATISTICS => 'الاحصائيات',
                ],
            ],
            'toolbar' => [
                'label' => 'أيقونات شريط الأدوات',
                'permissions' => [
                    self::TOOLBAR_ACCOUNTS => 'الحسابات والصلاحيات',
                    self::TOOLBAR_EMPLOYEES => 'الموظفون (تواقيع التأييد)',
                    self::TOOLBAR_ADD_STUDENT => 'إضافة طالب (شريط الأدوات)',
                    self::TOOLBAR_BACKUP => 'النسخ الاحتياطي',
                ],
            ],
            'students' => [
                'label' => 'الطلبة',
                'permissions' => [
                    self::STUDENTS_CREATE => 'إضافة طالب جديد',
                    self::STUDENTS_EDIT => 'التعديل على بيانات الطلبة',
                    self::STUDENTS_DELETE => 'حذف طالب',
                ],
            ],
            'grades' => [
                'label' => 'الدرجات',
                'permissions' => [
                    self::STUDENTS_GRADES_VIEW => 'عرض الدرجات (قراءة فقط — زر الدرجات)',
                    self::STUDENTS_GRADES_EDIT => 'التعديل على الدرجات (زر تعديل في الجدول وصفحة الدرجات)',
                ],
            ],
            'certificates' => [
                'label' => 'التأييدات',
                'permissions' => [
                    self::STUDENTS_CERTIFICATE_VIEW => 'التأييدات بدون درجات — عرض',
                    self::STUDENTS_CERTIFICATE_PRINT => 'التأييدات بدون درجات — طباعة',
                    self::STUDENTS_CERTIFICATE_GRADES_VIEW => 'التأييدات بالدرجات — عرض',
                    self::STUDENTS_CERTIFICATE_GRADES_PRINT => 'التأييدات بالدرجات — طباعة',
                ],
            ],
            'document' => [
                'label' => 'القيد',
                'permissions' => [
                    self::STUDENTS_DOCUMENT_VIEW => 'عرض القيد',
                    self::STUDENTS_DOCUMENT_EDIT => 'تعديل بيانات القيد',
                    self::STUDENTS_DOCUMENT_PRINT => 'طباعة القيد',
                ],
            ],
            'documents' => [
                'label' => 'الوثائق',
                'permissions' => [
                    self::STUDENTS_DOCUMENTS_VIEW => 'عرض الوثائق',
                    self::STUDENTS_DOCUMENTS_CREATE => 'إضافة وثيقة',
                    self::STUDENTS_DOCUMENTS_EDIT => 'تعديل وثيقة',
                    self::STUDENTS_DOCUMENTS_DELETE => 'حذف وثيقة',
                ],
            ],
            'profile' => [
                'label' => 'السجل (ملف الطالب)',
                'permissions' => [
                    self::STUDENTS_PROFILE_VIEW => 'عرض السجل',
                    self::STUDENTS_PROFILE_ATTESTATION_CREATE => 'إضافة تأييد في السجل',
                    self::STUDENTS_PROFILE_ATTESTATION_EDIT => 'تعديل تأييد في السجل',
                    self::STUDENTS_PROFILE_ATTESTATION_DELETE => 'حذف تأييد من السجل',
                    self::STUDENTS_PROFILE_NOTE_CREATE => 'إضافة ملاحظة',
                    self::STUDENTS_PROFILE_NOTE_EDIT => 'تعديل ملاحظة',
                    self::STUDENTS_PROFILE_NOTE_DELETE => 'حذف ملاحظة',
                ],
            ],
            'admin' => [
                'label' => 'الإدارة',
                'permissions' => [
                    self::EMPLOYEES_MANAGE => 'التحكم في صفحة الموظفون',
                    self::USERS_MANAGE => 'إدارة الحسابات وكلمات المرور والصلاحيات',
                    self::BACKUP_CREATE => 'إنشاء نسخة احتياطية',
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function definitions(): array
    {
        $all = [];
        foreach (self::groups() as $group) {
            foreach ($group['permissions'] as $key => $label) {
                $all[$key] = $label;
            }
        }

        return $all;
    }

    public static function isValid(string $permission): bool
    {
        return array_key_exists($permission, self::definitions());
    }

    /**
     * @param  list<string>  $permissions
     * @return list<string>
     */
    public static function filterValid(array $permissions): array
    {
        $valid = [];
        foreach ($permissions as $permission) {
            $permission = trim((string) $permission);
            if ($permission !== '' && self::isValid($permission)) {
                $valid[] = $permission;
            }
        }

        return array_values(array_unique($valid));
    }
}
