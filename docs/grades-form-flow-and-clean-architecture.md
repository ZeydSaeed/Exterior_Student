# تتبع فورم الدرجات من جدول الطلاب — من البداية للنهاية

## 1. المسار الكامل (فتح الفورم وعرض البيانات)

### الواجهة (Frontend)
- **الجدول:** `resources/views/students/partials/table.blade.php` — زر "الدرجات" له الصنف `btn-grades-open` والخاصية `data-student-id="{{ $student->id }}"`.
- **السكربت:** `public/js/students/index.js`:
  - عند النقر على `.btn-grades-open` يُستدعى `openGradesFor(id, btn, false)`.
  - `openModal()` يفتح المودال ويضيف `grades-modal-open` على `body`.
  - طلب **GET** إلى: `window.STUDENTS_GRADES_URL_TEMPLATE` بعد استبدال `__ID__` بالمعرّف، أي `/students/{id}/grades`.
  - الهيدر: `Accept: application/json`, `X-Requested-With: XMLHttpRequest`.
  - عند الاستجابة: `fillForm(data)` يملأ الفورم، وعند الخطأ يُملأ من بيانات الزر كاحتياط.

### الراوت (Route)
```php
// routes/web.php
Route::get('/students/{id}/grades', [StudentController::class, 'grades'])
    ->name('students.grades');
```

### الكونترولر (Controller)
- **الملف:** `app/Http/Controllers/StudentController.php`
- **الدالة:** `grades(int $id, GetStudentGradesQueryHandler $handler)`
  - تستقبل `id` من الراوت.
  - تستدعي `$handler->handle($id)`.
  - إن النتيجة `null` → `response()->json(['error' => 'not_found'], 404)`.
  - وإلا → `response()->json($dto->toArray())`.

الكونترولر لا يلمس قاعدة البيانات ولا منطق الأعمال، فقط يستدعي الـ Handler ويعيد JSON.

### الـ Handler (طبقة التطبيق — Query)
- **الملف:** `app/Application/Student/Query/GetStudentGradesQueryHandler.php`
- **الاعتماديات:** `StudentQueryRepository`, `SubjectCatalogInterface` (حقن من الحاوية).
- **الدالة:** `handle(int $id): ?StudentGradesDTO`
  1. `$view = $this->repository->getGradesById($id)` — جلب بيانات القراءة من الريبو.
  2. إن `$view === null` → إرجاع `null`.
  3. `$grades = $this->mergeGradesWithCatalog($view)` — دمج قائمة المواد من الكتالوج (حسب الفرع/الاختصاص) مع الدرجات من DB.
  4. `return $this->toDTO($view, $grades)` — تحويل إلى DTO وإرجاعه.

المنطق هنا: دمج كتالوج المواد مع الدرجات ومطابقة الأسماء (مع تطبيع عربي بسيط).

### الدومين (Domain)
- **StudentGradesView** (`app/Domain/Student/StudentGradesView.php`): كائن قراءة فقط يحتوي حقول الطالب + مصفوفة `grades` (subject, score). لا يعتمد على أسماء أعمدة DB.
- **StudentQueryRepository** (`app/Domain/Student/StudentQueryRepository.php`): واجهة فيها `getGradesById(int $id): ?StudentGradesView`.
- **SubjectCatalogInterface** (`app/Domain/Student/SubjectCatalogInterface.php`): واجهة فيها `getSubjectsFor(string $branch, string $major): array`.

### البنية التحتية (Infrastructure)
- **MySQLStudentQueryRepository** (`app/Infrastructure/Persistence/MySQLStudentQueryRepository.php`):
  - ينفّذ `getGradesById(int $id)`.
  - يقرأ من `DB::table('main_table')` مع أعمدة ثابتة عربية (الرقم الامتحاني، اسم الطالب، …) وأعمدة الدرجات من `Config::get('grades_catalog.grade_columns')`.
  - يحوّل الصف إلى `StudentGradesView` (ربط أسماء الأعمدة هنا فقط).
- **ConfigSubjectCatalog** (`app/Infrastructure/Grades/ConfigSubjectCatalog.php`): ينفّذ `SubjectCatalogInterface` ويعتمد على `config/grades_catalog.php` (فرع/اختصاص → قائمة مواد).

### قاعدة البيانات
- **الجدول:** `main_table`.
- أعمدة ثابتة: `id`, `الرقم الامتحاني`, `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`, `الفرع`, `الاختصاص`, `العام الدراسي`, `النتيجة`, `المجموع`, `المعدل`, `الدور`.
- أعمدة الدرجات: أسماء المواد من `config/grades_catalog.php` → `grade_columns` (كل عمود = مادة، القيمة = الدرجة).

### الربط (Service Container)
- `app/Providers/AppServiceProvider.php`:
  - `StudentQueryRepository` → `MySQLStudentQueryRepository`
  - `SubjectCatalogInterface` → `ConfigSubjectCatalog`

---

## 2. مسار الحفظ (تحديث الدرجات)

### الواجهة
- زر "حفظ" في المودال يجمّع بيانات الفورم (الحقول + صفوف الدرجات) ويرسل **PUT** إلى `STUDENTS_GRADES_UPDATE_URL_TEMPLATE` مع `X-CSRF-TOKEN` و body JSON.

### الراوت
```php
Route::put('/students/{id}/grades', [StudentController::class, 'updateGrades'])
    ->name('students.grades.update');
```

### الكونترولر
- **الدالة:** `updateGrades(int $id, UpdateStudentGradesRequest $request, UpdateStudentGradesCommandHandler $handler)`
  - التحقق من المدخلات عبر `UpdateStudentGradesRequest`.
  - `$payload = $request->normalizedPayload()`.
  - `$ok = $handler->handle($id, $payload)`.
  - إن `!$ok` → 404، وإلا → `response()->json(['success' => true])`.
  - في حال استثناء → `report()` و 500 مع رسالة آمنة.

### Form Request
- **UpdateStudentGradesRequest:** يتحقق من الحقول و`grades.*.subject`, `grades.*.score` ويُرجِع `normalizedPayload()` للـ Handler.

### الـ Handler (Command)
- **UpdateStudentGradesCommandHandler:** يطبّع البايلود ثم يستدعي `$this->commandRepository->updateGrades($id, $normalized)`.

### الدومين
- **StudentCommandRepository:** واجهة فيها `updateGrades(int $id, array $payload): bool`.

### البنية التحتية
- **MySQLStudentCommandRepository:** داخل ترانزاكشن يحدّث `main_table` (حقول أساسية + أعمدة الدرجات المسموحة من `grades_catalog.grade_columns`).

---

## 3. مطابقة معمارية Clean Code / Clean Architecture

### ما هو مطابق
- **الفصل بين الطبقات:** Presentation (Controller, Request, View) → Application (Query/Command Handlers) → Domain (Entities, View objects, Repository interfaces) → Infrastructure (Repositories, Config catalog).
- **اتجاه التبعيات:** من الخارج للداخل؛ الكونترولر يعتمد على الـ Handler، والـ Handler يعتمد على واجهات الدومين، والبنية التحتية تنفّذ الواجهات.
- **CQRS:** فصل واضح بين القراءة (Query + StudentGradesView + StudentQueryRepository) والكتابة (Command + StudentCommandRepository).
- **الدومين خالٍ من الإطار:** `StudentGradesView` وواجهات الريبو لا تستخدم Eloquent ولا أسماء أعمدة؛ تحويل DB ↔ Domain يحدث في Infrastructure فقط.
- **استخدام DTO:** `StudentGradesDTO` للاتصال بين الـ Handler والـ API.
- **حقن الاعتماديات:** الـ Handlers والريبو تُحقَن من الحاوية دون استخدام Facades داخل الدومين/التطبيق.

### سلبيات وتحسينات محتملة
1. **أسماء الأعمدة العربية في Infrastructure:** `MySQLStudentQueryRepository` و `MySQLStudentCommandRepository` يعتمدان على أسماء أعمدة عربية ثابتة و`grades_catalog.grade_columns`. أي تغيير في بنية الجدول يتطلب تعديل هنا؛ يمكن تخفيف ذلك بطبقة mapping أو config لأسماء الأعمدة.
2. **منطق المطابقة في الـ Handler:** تطبيع أسماء المواد ومطابقتها موجود في `GetStudentGradesQueryHandler`. يمكن نقله إلى Domain Service أو Value Object إذا كبرت قواعد المطابقة.
3. **عدم وجود كيان Student للقراءة في هذا المسار:** الاستعلام يعيد `StudentGradesView` فقط؛ لا يوجد استخدام لكيان `Student` في مسار الدرجات، وهذا مقبول في CQRS للقراءة.
4. **التحقق في Form Request فقط:** لا توجد قواعد دومين صريحة (مثلاً: عدم السماح بتعديل الطالب إن كان مغلقاً). يمكن إضافة تحقق في الدومين أو في الـ Command Handler لاحقاً.

### إيجابيات البناء
- وضوح المسار: من الراوت → Controller → Handler → Repository → DB.
- سهولة الاختبار: يمكن استبدال الريبو وكتالوج المواد بـ mocks.
- سهولة استبدال مصدر البيانات أو كتالوج المواد بتغيير الربط في الـ Provider.
- فصل واضح بين عرض الدرجات (Query) وتحديثها (Command) يقلل التعقيد ويحسّن القراءة.

---

## 4. ملخص التدفق

| المرحلة            | المكون الرئيسي                          | الملف / الموقع                |
|--------------------|-----------------------------------------|-------------------------------|
| فتح المودال       | JS                                      | `public/js/students/index.js` |
| GET درجات طالب    | Route → Controller → QueryHandler       | `web.php`, `StudentController`, `GetStudentGradesQueryHandler` |
| قراءة من DB       | StudentQueryRepository (Interface)      | `Domain/Student/StudentQueryRepository.php` |
| تنفيذ القراءة     | MySQLStudentQueryRepository             | `Infrastructure/Persistence/MySQLStudentQueryRepository.php` |
| كتالوج المواد     | SubjectCatalogInterface → Config        | `Domain`, `ConfigSubjectCatalog`, `config/grades_catalog.php` |
| PUT حفظ الدرجات   | Route → Controller → Request → CommandHandler | `web.php`, `StudentController`, `UpdateStudentGradesRequest`, `UpdateStudentGradesCommandHandler` |
| كتابة إلى DB      | StudentCommandRepository → MySQL        | `MySQLStudentCommandRepository` |
| الجدول            | main_table                              | MySQL                         |

الخلاصة: فورم الدرجات مبني وفق طبقات Clean Architecture مع CQRS؛ التبعيات من الخارج للداخل، والدومين منفصل عن الإطار وقاعدة البيانات. التحسينات المقترحة تركّز على إخفاء تفاصيل أعمدة DB وتقوية قواعد الدومين عند الحاجة.
