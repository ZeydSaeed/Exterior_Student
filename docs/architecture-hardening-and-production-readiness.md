# تقرير تشديد المعمارية وجاهزية الإنتاج

مراجعة شاملة للمشروع بناءً على ملاحظات معمارية (Architecture hardening) وربطها بالكود مع خطة تنفيذ.

---

## الملخص التنفيذي

| المجال | التقييم الحالي | الهدف |
|--------|-----------------|--------|
| Architecture thinking | ممتاز (Clean Architecture + CQRS) | الحفاظ + إكمال Read models |
| Performance awareness | جيد (كاش فلترة، فهارس) | فهارس مركبة، FULLTEXT، منع N+1 |
| DB design | متوسط (جدول god، FK نصي) | تطبيع، student_id، فصل الدرجات |
| Production readiness | متوسط | Query profiling، قيود، تحسين استعلامات |
| Optimization depth | جيد | طبقة استعلامات منفصلة، تحسين بحث |

**أكبر خطأ حالي:** تصميم أعمدة الدرجات (God table + عشرات الأعمدة).  
**أكبر bottleneck مستقبلي:** بحث LIKE على الاسم والرقم الامتحاني.

---

## 1. المشكلة 1: استخدام الرقم الامتحاني كـ FK (Exam number as FK)

### الوضع الحالي في الكود

- **جدول `records` (وثائق الطلاب)**  
  - **Migration:** يعرّف `student_id` (FK → main_table) وأعمدة إنجليزية.  
  - **الكود الفعلي:**  
    - `MySQLRecordQueryRepository::listByStudentId()`: يستخرج `الرقم الامتحاني` من `main_table` ثم يفلتر `records` بـ `where('الرقم الامتحاني', $examNumber)` — أي الربط في الاستعلام يتم عبر **نص** وليس `student_id`.  
    - `MySQLRecordCommandRepository::create()`: يدرج في `records` عمود `الرقم الامتحاني` (نص) ولا يستخدم `student_id`.  
  - **الملفات:**  
    - `app/Infrastructure/Persistence/MySQLRecordQueryRepository.php` (حوالي 17–26)  
    - `app/Infrastructure/Persistence/MySQLRecordCommandRepository.php` (حوالي 21–36)

- **جدول `certificate` (التأييدات)**  
  - **Migration:** عمود `exam_number` (string) مع index، بدون `student_id`.  
  - **الكود:**  
    - `MySQLAttestationQueryRepository::listByStudentId()`: يستخرج `الرقم الامتحاني` من `main_table` ثم `where('exam_number', $examNumber)`.  
    - `MySQLAttestationCommandRepository::create()`: يدرج `exam_number` فقط.  
  - **الملفات:**  
    - `app/Infrastructure/Persistence/MySQLAttestationQueryRepository.php` (حوالي 16–37)  
    - `app/Infrastructure/Persistence/MySQLAttestationCommandRepository.php` (حوالي 27)  
  - **Migration:** `database/migrations/2025_03_07_120000_create_certificate_table.php`

### لماذا هذا ممارسة سيئة؟

- **String FK:** أبطأ من ربط عدد صحيح، وأكبر حجماً في الفهارس والـ JOIN.  
- **غير ثابت:** إذا تغيّر الرقم الامتحاني للطالب تنكسر العلاقة مع `records` و`certificate` أو تحتاج تحديثاً يدوياً في جداول متعددة.  
- **خطر على النزاهة:** لا توجد FK من قاعدة البيانات تربط `records`/`certificate` بـ `main_table`، فلا ضمان referential integrity.

### الحل المطلوب

1. **جدول `records`**  
   - توحيد البنية مع الـ migration الحالي: أعمدة إنجليزية + **`student_id` فقط** كرابط.  
   - إزالة الاعتماد على `الرقم الامتحاني` من الـ INSERT والـ SELECT.  
   - التأكد من وجود FK: `student_id` → `main_table.id` مع `onDelete('cascade')` أو السياسة المناسبة.

2. **جدول `certificate`**  
   - إضافة عمود `student_id` (unsignedBigInteger, FK → main_table).  
   - الاستعلامات والأوامر: الربط والفلترة بـ `student_id` فقط.  
   - الإبقاء على `exam_number` كـ **عمود عرض/توثيق** مع **UNIQUE** إن لزم، وليس كرابط علائقي.

3. **ترحيل البيانات**  
   - migration يملأ `student_id` في `records` و`certificate` من خلال الربط الحالي بـ `الرقم الامتحاني` / `exam_number` مع `main_table`، ثم تعديل الكود لاستخدام `student_id` فقط.

**الملفات المتأثرة:**  
- `MySQLRecordQueryRepository`, `MySQLRecordCommandRepository`  
- `MySQLAttestationQueryRepository`, `MySQLAttestationCommandRepository`  
- Domain interfaces إن كانت تتحدث عن `examNumber` في سياق الربط (يمكن الإبقاء على exam_number في DTO للعرض فقط).  
- migrations جديدة لـ `certificate` (إضافة student_id) ومواءمة `records` مع الكود إن لزم.

---

## 2. المشكلة 3: تصميم main_table (God table / Anti-pattern)

### الوضع الحالي

- جدول واحد **main_table** يجمع:  
  - هوية ورقم امتحان  
  - اسم (4 أجزاء)  
  - بيانات شخصية (جنس، تاريخ ميلاد، محل ولادة، اسم الأم)  
  - بيانات أكاديمية (فرع، اختصاص، عام دراسي، نتيجة، مجموع، معدل، دور)  
  - بيانات وثيقة متوسطة (آخر مدرسة، رقم الوثيقة، تاريخها، جهة الإصدار)  
  - **أكثر من 35 عمود درجات** (كل مادة عمود منفصل) من `config/grades_catalog.php`.

- **الاستخدام في الكود:**  
  - `MySQLStudentQueryRepository`: قوائم، فلترة، درجات، ملف تعريفي.  
  - `MySQLStudentCommandRepository`: إنشاء طالب، تحديث درجات، حذف.  
  - كل عمليات القراءة/الكتابة تذهب إلى نفس الجدول الضخم.

### لماذا هذا مشكلة؟

- **God table:** يصعب الصيانة، التوسع، والفهرسة الفعّالة.  
- **إضافة مادة جديدة:** تتطلب تغيير بنية الجدول (عمود جديد).  
- **Domain separation غائب:** لا فصل واضح بين الهوية، الشخصي، الأكاديمي، والدرجات.

### الحل المقترح (فصل حسب النطاق)

| الجدول المقترح | المحتوى | ملاحظات |
|-----------------|---------|----------|
| **students** | id, exam_number (UNIQUE), أجزاء الاسم، ربما created_at/updated_at | الهوية والرقم الامتحاني فقط |
| **student_personal** | student_id (FK), التولد، محل الولادة، اسم الام، الجنس | بيانات شخصية |
| **student_academic** | student_id (FK), الفرع، الاختصاص، العام الدراسي، النتيجة، المجموع، المعدل، الدور، آخر مدرسة، رقم/تاريخ الوثيقة المتوسطة، جهة الإصدار | كل ما هو أكاديمي/إداري |
| **student_grades** | student_id (FK), subject_id (FK), score أو grade | مادة واحدة لكل صف؛ الربط بجدول مواد |
| **subjects** | id, name_ar (أو مفتاح من الكتالوج) | من `grades_catalog.grade_columns` |

- **student_documents** يمكن أن يكون هو جدول **records** الحالي بعد توحيده مع `student_id` (لا حاجة لجدول منفصل إضافي إذا كان المقصود "وثائق الطالب" = records).

الفوائد: استعلامات أخف، فهارس أفضل، إضافة مواد بدون تغيير بنية الجدول، ووضوح النطاق (Domain separation).

**ترتيب التنفيذ المقترح:**  
1) إنشاء `subjects` و `student_grades` وترحيل الدرجات.  
2) ثم فصل `students` / `student_personal` / `student_academic` من `main_table` مع ترحيل تدريجي أو view مؤقت للتوافق مع الكود الحالي.

---

## 3. المشكلة 5: بحث LIKE والأداء (LIKE search)

### الوضع الحالي في الكود

- **الموقع الوحيد:**  
  `app/Infrastructure/Persistence/MySQLStudentQueryRepository.php`، الدالة `applyListFilters()` (حوالي 142–152).

- **المنطق:**  
  عند وجود `$filters['search']`:  
  - بناء نمط `%...%` للبحث في الرقم الامتحاني والاسم.  
  - تطبيع أحرف عربية (أ، إ، آ → ا؛ ى → ي) داخل الاستعلام عبر `REPLACE` و `LOWER` و `TRIM`.  
  - استخدام `whereRaw("{$normNum} LIKE ?", [$pattern])` و `whereRaw("{$normName} LIKE ?", [$pattern])`.

### لماذا يقتل الأداء؟

- **LIKE '%...%'** يمنع استخدام الفهارس العادية.  
- التعبيرات المعقدة (REPLACE, CONCAT_WS, LOWER, TRIM) على أعمدة كاملة تمنع استخدام الفهارس وتزيد حمل الـ CPU على قاعدة البيانات.

### الحلول الممكنة

1. **عمود بحث مُنظّف (Search column)**  
   - إضافة عمود مثل `search_normalized` (أو عمود مُولّد GENERATED) يخزن نسخة مُطبّعة من الاسم + الرقم الامتحاني.  
   - تحديثه عند الإدخال/التعديل (من طبقة التطبيق أو عبر trigger).  
   - الفلترة: `WHERE search_normalized LIKE ?` مع فهرس مناسب إن أمكن (مثلاً prefix فقط بدون % في البداية).

2. **فهرس FULLTEXT**  
   - إذا كان المحرك InnoDB ونسخة MySQL تدعم FULLTEXT على النص:  
     `FULLTEXT(name_normalized)` أو عمود مخصّص للبحث.  
   - استبدال LIKE بـ `MATCH(search_column) AGAINST(? IN BOOLEAN MODE)` أو ما يناسب اللغة العربية إن دُعم.

3. **تقليل نطاق البحث**  
   - بحث الرقم الامتحاني: مساواة أو بداية (prefix) فقط بدلاً من `%x%` لتمكين استخدام الفهرس.  
   - البحث في الاسم: إما عبر عمود بحث مُنظّف أو FULLTEXT بدلاً من REPLACE/LOWER على عدة أعمدة في كل طلب.

**الملفات المتأثرة:**  
- `MySQLStudentQueryRepository::applyListFilters()`  
- migration لإضافة عمود بحث و/أو FULLTEXT  
- تحديث كتابة الطالب (Command) لملء العمود المُنظّف إن وُجد.

---

## 4. أهم 5 تحسينات بالتسلسل المقترح

### 1) Composite indexes (فهارس مركبة)

- **الوضع:** يوجد migration لفهارس مفردة على `الرقم الامتحاني`, `العام الدراسي`, `النتيجة` (`2026_03_07_100000_add_indexes_main_table.php`).  
- **المطلوب:** إضافة فهارس مركبة تناسب أنماط الاستعلام الأكثر استخداماً، مثلاً:  
  - قائمة الطلاب مع فلترة: `(العام الدراسي, الفرع, الاختصاص)` أو `(العام الدراسي, النتيجة)`.  
  - حذف الراسبين/المعيدين: `(النتيجة, العام الدراسي)` إن كان الاستعلام يفلتر بهما معاً.

يُنفّذ في migration جديد دون تغيير منطق التطبيق.

---

### 2) جدول student_grades (وفصل المواد)

- **الخطوات:**  
  - إنشاء جدول `subjects` (من الكتالوج).  
  - إنشاء جدول `student_grades` (student_id, subject_id, score).  
  - ترحيل بيانات الدرجات من أعمدة main_table إلى `student_grades`.  
  - تعديل `MySQLStudentQueryRepository::getGradesById()` و `MySQLStudentCommandRepository::updateGrades()` و `create()` لقراءة/كتابة من `student_grades` بدلاً من أعمدة المواد في main_table.  
- **النتيجة:** تقليل أعمدة main_table (أو جدول الطلاب لاحقاً) وإمكانية إضافة مواد دون تغيير البنية.

---

### 3) استبدال exam_number كـ FK (انظر القسم 1)

- **records:** استخدام `student_id` فقط في الكود والـ schema.  
- **certificate:** إضافة `student_id` والربط به، والإبقاء على `exam_number` كـ UNIQUE للعرض.

---

### 4) Generated columns / عمود بحث

- عمود (أو GENERATED column) للبحث المُنظّف عن الاسم والرقم الامتحاني لاستخدامه في الفلترة والـ FULLTEXT إن أمكن، وتقليل استخدام LIKE و REPLACE في الـ WHERE.

---

### 5) Pagination في كل مكان

- **الوضع:** قائمة الطلاب مُقسمة صفحات (`PER_PAGE = 20`).  
- **المطلوب:** التأكد من أن أي قائمة أخرى (وثائق، تأييدات، إلخ) إما مُقسمة صفحات أو محدودة بعدد معقول (limit) حتى مع نمو البيانات.

---

## 5. جاهزية الإنتاج (Production readiness)

| البند | الوضع الحالي | الإجراء المقترح |
|-------|---------------|------------------|
| **Query profiling** | غير مفعّل | تفعيل `DB::listen()` في `AppServiceProvider` (أو بيئة محددّة) لتسجيل زمن الاستعلامات والاستعلامات البطيئة. |
| **Connection pooling** | إعداد Laravel الافتراضي | مراجعة إعدادات MySQL وعدد الاتصالات؛ استخدام queue للعمليات الثقيلة إن لزم. |
| **Lazy loading / N+1** | لا استخدام لـ Eloquent relations في الكود الحالي؛ الاستعلامات عبر Query Builder وواجهات الـ Repository | مراجعة أي مكان يُجلب فيه قائمة ثم يُجلب لكل عنصر بيانات إضافية (مثلاً profile: طالب + تأييدات + وثائق = 3 استعلامات منفصلة). يمكن دمجها في استعلام واحد أو استخدام Read model واحد يجمع البيانات. |
| **Batch queries** | حذف الراسبين يُنفّذ في حلقة (لكل id استعلام get ثم delete) | تحسين `DeleteFailedStudentsByFiltersCommandHandler`: جلب كل الـ id المطابقة ثم حذف دفعة واحدة (مثلاً `whereIn('id', $ids)->delete()`) بدلاً من حذف سجل بسجل. |

---

## 6. تحسينات بمستوى Senior

| البند | الوضع الحالي | الإجراء |
|-------|---------------|---------|
| **Query profiling** | غير موجود | إضافة `DB::listen()` لتسجيل الاستعلامات والوقت (واختيارياً التصعيد عند تجاوز حد زمني). |
| **منع N+1** | Profile: 3 استعلامات (طالب، تأييدات، وثائق) | إما Read model واحد (استعلام واحد أو JOIN) أو الحفاظ على 3 استعلامات مع التأكد من عدم وجود حلقة استعلامات إضافية. |
| **DTO layer** | موجود (مثلاً StudentProfileDTO, RecordDTO, AttestationDTO) | الاستمرار في إرجاع DTO من طبقة التطبيق وعدم إرجاع Models. |
| **Read models / استعلامات منفصلة** | CQRS موجود؛ Query Handlers تعتمد على نفس الـ Repositories | توضيح Read models: مثلاً StudentListQuery (قائمة + فلاتر)، StudentProfileQuery (صفحة الملف)، StudentCertificateQuery (شهادة) مع إمكانية استعلامات مُحسّنة لكل حالة. |
| **Materialized views** | غير مستخدمة | اختياري لاحقاً لتجميعات ثقيلة (مثلاً إحصائيات سنوية) إذا دعت الحاجة. |
| **Controllers بدون logic** | Controllers رفيعة وتُفوّض إلى Handlers | الحفاظ على ذلك وعدم نقل منطق أعمال إلى Controllers. |
| **Validation layer** | Form Requests موجودة (مثلاً StoreStudentRequest, UpdateStudentGradesRequest) | إضافة تحقق على مستوى DTO إن لزم (مثلاً عند بناء DTO من مدخلات غير موثوقة). |
| **Separate query models** | نفس الـ Repository يُستخدم للقائمة والملف والشهادة | فصل استعلامات القائمة عن استعلامات الملف عن استعلامات الشهادة (كل واحد يمكن أن يُحسّن بشكل مستقل). |
| **Query caching** | كاش قوائم الفلترة (student_filters.lists) موجود | توسيع الكاش لقراءات أخرى ثقيلة وقليلة التغيير (مثلاً قائمة المواد، إعدادات التواقيع). |
| **Database constraints** | بعض الـ FK موجودة؛ NOT NULL و CHECK غير شاملة | إضافة NOT NULL حيث يلزم، وCHECK (أو تطبيق في التطبيق) للقيم المحددة (مثلاً النتيجة: ناجح/راسب/معيد). |

---

## 7. خريطة الملفات المتأثرة حسب التحسين

| التحسين | الملفات / الطبقات المتأثرة |
|---------|-----------------------------|
| استبدال exam_number بـ student_id | MySQLRecordQueryRepository, MySQLRecordCommandRepository; MySQLAttestationQueryRepository, MySQLAttestationCommandRepository; migrations لـ records و certificate؛ واجهات Domain إن لزم. |
| جدول student_grades + subjects | config/grades_catalog؛ MySQLStudentQueryRepository (getGradesById)، MySQLStudentCommandRepository (create, updateGrades)； migrations جديدة؛ Domain/Application إن كانت تتوقع بنية درجات من مصدر واحد. |
| بحث (FULLTEXT / عمود بحث) | MySQLStudentQueryRepository::applyListFilters؛ MySQLStudentCommandRepository (عند إنشاء/تحديث الطالب لملء عمود البحث)； migration لعمود بحث و/أو FULLTEXT. |
| فهارس مركبة | migration جديد فقط. |
| Query profiling | AppServiceProvider أو middleware مخصّص. |
| منع N+1 / Batch delete | DeleteFailedStudentsByFiltersCommandHandler؛ واختيارياً GetStudentProfileQueryHandler إذا أُدخل Read model موحّد. |
| Pagination / Limit | أي مسار يعيد قوائم كبيرة (وثائق، تأييدات)؛ Controllers أو Query Handlers. |

---

## 8. خطة تنفيذ مقترحة (بالترتيب)

1. **مرحلة فورية (أقل مخاطرة)**  
   - إضافة فهارس مركبة (migration).  
   - تفعيل Query profiling (DB::listen).  
   - تحسين حذف الراسبين (batch delete بدلاً من حلقة).

2. **مرحلة قصيرة المدى**  
   - توحيد جدول `records` مع الـ migration (student_id + أعمدة إنجليزية) وتعديل الـ Repositories.  
   - إضافة `student_id` لجدول `certificate` وترحيل البيانات وتعديل الاستعلامات والأوامر.

3. **مرحلة متوسطة**  
   - إنشاء `subjects` و `student_grades` وترحيل الدرجات وتعديل قراءة/كتابة الدرجات.  
   - إضافة عمود بحث (أو GENERATED) وتحسين شرط البحث في `applyListFilters`.

4. **مرحلة طويلة (إعادة هيكلة)**  
   - فصل main_table إلى students / student_personal / student_academic مع ترحيل تدريجي أو views للتوافق.

هذا التقرير يربط كل مشكلة بالكود الحالي ويحدد الملفات والخطوات اللازمة لتطبيق الحلول وجعل النظام أقرب إلى جاهزية إنتاج بمستوى Principal.

---

## 9. ما تم تنفيذه (محدث)

| البند | الحالة | الملفات / الملاحظات |
|-------|--------|---------------------|
| **فهارس مركبة** | مُنفَّذ | `2026_03_15_100000_add_composite_indexes_main_table.php`: (العام الدراسي، الفرع)، (العام الدراسي، النتيجة)، (النتيجة، العام الدراسي) |
| **Query profiling** | مُنفَّذ | `AppServiceProvider::boot()`: `DB::listen()` عند `config('app.debug')`، تسجيل استعلامات أبطأ من 1000ms في الـ Log |
| **حذف جماعي (Batch delete)** | مُنفَّذ | `StudentQueryRepository::listFailedIdsWithFilters()`، `StudentCommandRepository::deleteStudentsByIds()`، `DeleteFailedStudentsByFiltersCommandHandler` يستدعيهما ثم حذف دفعة واحدة |
| **Records: student_id + أعمدة إنجليزية** | مُنفَّذ | `MySQLRecordQueryRepository` و `MySQLRecordCommandRepository` يستخدمان `student_id` و document_number, document_date, addressee, purpose؛ حد أقصى 500 وثيقة لكل طالب |
| **Certificate: student_id** | مُنفَّذ | Migration `2026_03_15_200000_add_student_id_to_certificate_table`: إضافة عمود، backfill من main_table، فهرس؛ Repositories و CreateAttestationCommandHandler و StudentProfileController يمرّرون/يستخدمون student_id؛ حد أقصى 500 تأييد لكل طالب |
| **تشغيل الـ migrations** | مُنفَّذ | `composer run setup` يشمل `php artisan migrate --force`؛ يمكن تشغيل `php artisan migrate --path=database/migrations/2026_03_15_100000_add_composite_indexes_main_table.php` و `--path=.../2026_03_15_200000_add_student_id_to_certificate_table.php` لتنفيذ تحسينات المعمارية فقط |

**ملاحظة:** جدول `records` يفترض أن يكون مطابقاً لـ migration `create_records_table` (أعمدة إنجليزية و student_id). إن كان لديك جدول records بأعمدة عربية فيجب ترحيل البيانات أو تعديل الـ migration ثم تشغيله.
