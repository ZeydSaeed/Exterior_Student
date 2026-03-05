# تتبع «تأييد بدون درجات» من جدول الطلاب — من البداية للنهاية

## 1. المسار الكامل (من النقر حتى عرض الصفحة)

### الواجهة (Frontend)
- **الجدول:** `resources/views/students/partials/table.blade.php`
  - الرابط: `<a href="{{ route('students.certificate', ['id' => $student->id]) }}" class="btn-primary btn-confirm-without-grades" title="تأييد بدون درجات">`
  - عند النقر يتم **انتقال كامل** (Full Page Navigation) إلى `/students/{id}/certificate` — لا يوجد طلب AJAX لفتح المودال.
- **لا سكربت خاص في صفحة الجدول:** فتح صفحة التأييد يعتمد على الراوت فقط؛ لا يُستخدم `index.js` ولا مودال للتأييد — الرابط ينقل إلى صفحة التأييد مباشرة.

### الراوت (Route)
```php
// routes/web.php
Route::get('/students/{id}/certificate', [StudentCertificateController::class, 'show'])
    ->name('students.certificate');
```

### الكونترولر (Controller)
- **الملف:** `app/Http/Controllers/StudentCertificateController.php`
- **الدالة:** `show(int $id, Request $request)`
  1. يقرأ الموظفين من الجلسة: `$employees = session('selected_employees', [])`.
  2. يستدعي `$this->handler->handle($id, $employees)`.
  3. في حال `RuntimeException` مع الكود 404:
     - إن الطلب يطلب JSON → `response()->json(['error' => 'not_found'], 404)`.
     - وإلا → `abort(404, 'لم يتم العثور على الطالب.')`.
  4. إن الطلب يطلب JSON → `response()->json($dto->toArray())`.
  5. وإلا → `return view('students.certificate', compact('dto'))`.

الكونترولر لا يلمس قاعدة البيانات؛ يستدعي الـ Handler فقط ويقرأ الجلسة لتزويد الموظفين.

### الـ Handler (طبقة التطبيق — Query)
- **الملف:** `app/Application/Student/Query/GetStudentCertificateQueryHandler.php`
- **الاعتماديات:** `StudentReadRepository` (حقن من الحاوية).
- **الدالة:** `handle(int $id, array $employees): StudentCertificateDTO`
  1. `$student = $this->repository->findById($id)`.
  2. إن `$student === null` → `throw new \RuntimeException('...', 404)`.
  3. يُنشئ ويُرجع `StudentCertificateDTO` من بيانات `$student` + مصفوفة `$employees` (الموظفون من الجلسة).

لا يوجد منطق أعمال معقد؛ التحويل من Domain Model إلى DTO فقط.

### الدومين (Domain)
- **StudentCertificate** (`app/Domain/Student/StudentCertificate.php`):
  - نموذج قراءة (Read Model) يحتوي حقول الطالب (الاسم، الرقم الامتحاني، تاريخ الولادة، الفرع، الاختصاص، …).
  - يعرّف دوال وصول فقط: `fullName()`, `examNumber()`, `birthDate()`, `branch()`, `specialization()`, ….
  - **لا يعتمد على Laravel ولا على أسماء أعمدة DB.**
- **StudentReadRepository** (`app/Domain/Student/StudentReadRepository.php`):
  - واجهة تحتوي: `findById(int $id): ?StudentCertificate`.

### البنية التحتية (Infrastructure)
- **MySQLStudentReadRepository** (`app/Infrastructure/Persistence/MySQLStudentReadRepository.php`):
  - ينفّذ `StudentReadRepository`.
  - يقرأ من **`DB::table('main_table')`** بواسطة `where('id', $id)->first()`.
  - يحوّل الصف إلى `StudentCertificate` (ربط أسماء الأعمدة العربية هنا فقط: اسم الطالب، اسم الاب، التولد، الفرع، …).

### قاعدة البيانات
- **الجدول:** `main_table`.
- أعمدة مستخدمة في التأييد: `id`, `الرقم الامتحاني`, `اسم الطالب`, `اسم الاب`, `اسم الجد`, `اللقب`, `التولد`, `الفرع`, `الاختصاص`, `العام الدراسي`, `النتيجة`, `الدور`, `الجنس`.

### الربط (Service Container)
- `app/Providers/AppServiceProvider.php`: ربط `StudentReadRepository` → `MySQLStudentReadRepository`.

### العرض (View)
- **الملف:** `resources/views/students/certificate.blade.php`
- الصفحة تُعرض **داخل الداشبورد** (extends `layouts.dashboard`)؛ السايدبار والتولبار يبقى ظاهرين ولا يتأثران.
- المحتوى داخل `@section('content')` فقط (منطقة المحتوى)، بتخطيط **بالعرض** (عرض كامل، تكيف بالعرض عبر `certificate.js`).
- يستقبل `$dto` ويعرض ورقة التأييد (الجمهورية، الوزارة، بيانات الطالب من `$dto`، منظم التأييد ومسؤول الشعبة، التوقيعات).
- **الأصول المستخدمة فقط لصفحة التأييد (لا مودال ولا index.js):**
  - `public/css/certificate.css` — تنسيقات ورقة التأييد والطباعة.
  - `public/js/students/certificate.js` — زر الطباعة + تكيف حجم الورقة مع الصفحة (`fitCertificate`، `runFit`).
  - `public/js/arabic-date.js` — إن وُجد، لتحويل التواريخ في الصفحة.

---

## 2. تدفق البيانات (ملخص)

| المرحلة              | المكون الرئيسي                    | الملف / الموقع                |
|----------------------|------------------------------------|-------------------------------|
| النقر على التأييد   | رابط في الجدول                    | `partials/table.blade.php`    |
| الطلب                | GET /students/{id}/certificate     | المتصفح                       |
| الراوت               | StudentCertificateController@show  | `web.php`                     |
| الكونترولر           | قراءة الجلسة + استدعاء Handler    | `StudentCertificateController`|
| الـ Handler          | GetStudentCertificateQueryHandler  | `Application/Student/Query/`   |
| واجهة القراءة       | StudentReadRepository              | `Domain/Student/`             |
| تنفيذ القراءة       | MySQLStudentReadRepository         | `Infrastructure/Persistence/` |
| قاعدة البيانات      | main_table                         | MySQL                         |
| الاستجابة            | view أو JSON حسب الطلب             | `students.certificate`        |
| أصول الصفحة          | CSS + JS للصفحة فقط               | `certificate.css`, `certificate.js` |

---

## 3. مطابقة معمارية Clean Code / Clean Architecture

### ما هو مطابق
- **الفصل بين الطبقات:** Presentation (Controller, View) → Application (Query Handler) → Domain (StudentCertificate, StudentReadRepository) → Infrastructure (MySQLStudentReadRepository).
- **اتجاه التبعيات:** من الخارج للداخل؛ الكونترولر يعتمد على الـ Handler، والـ Handler يعتمد على واجهة الدومين، والبنية التحتية تنفّذ الواجهة.
- **الدومين مستقل عن الإطار:** `StudentCertificate` لا يستخدم Eloquent ولا Config؛ تحويل الصف إلى الكيان يحدث في Infrastructure فقط.
- **استخدام DTO:** `StudentCertificateDTO` للاتصال بين الـ Handler والعرض (أو JSON).
- **حقن الاعتماديات:** الـ Handler يُحقَن بـ `StudentReadRepository` من الحاوية.
- **CQRS (قراءة فقط):** مسار التأييد استعلام فقط؛ لا يوجد Command أو تعديل على البيانات في هذا المسار.

### نقاط ضعف أو خروج بسيط
1. **قراءة الجلسة في الكونترولر:** `session('selected_employees')` في الكونترولر يعني أن طبقة العرض تلمس الجلسة وتزوّد الـ Handler ببيانات من البيئة. من منظور Clean يمكن أن تكون الموظفين جزءاً من طلب (Request Object) أو يُجلبون عبر Port آخر؛ لكن التأثير عملياً محدود.
2. **أسماء الأعمدة العربية في Infrastructure:** مثل مسار الدرجات؛ أي تغيير في أسماء أعمدة `main_table` يمسّ `MySQLStudentReadRepository` فقط، لكن يبقى الاقتران قوياً بين أسماء الأعمدة والكود.
3. **استثناء بمفتاح رقمي (404):** استخدام `\RuntimeException(..., 404)` يعمل لكنه غير معيار في كل المشاريع؛ يمكن استخدام استثناء دومين مخصص أو قيمة مُرجعة (مثلاً null) مع تحويل إلى 404 في الكونترولر ليكون أوضح.

### ما لا يُعتبر خرقاً
- استخدام `Request` في الكونترولر للتحقق من `expectsJson()` و `abort(404)` مقبول في طبقة العرض.
- عرض الـ View مع `compact('dto')` يبقى في نطاق العرض دون منطق أعمال.

---

## 4. الإيجابيات

- مسار بسيط وواضح: رابط → راوت → كونترولر → Handler → Repository → DB → View.
- الدومين خالٍ من الإطار وقابل لإعادة الاستخدام في سياق آخر.
- فصل واضح بين القراءة (Query) والكتابة (Command في مسارات أخرى).
- سهولة الاختبار: يمكن استبدال `StudentReadRepository` بـ mock.
- دعم وضعين للاستجابة (HTML و JSON) من نفس الـ Use Case دون تكرار منطق.
- نموذج الدومين `StudentCertificate` يعبّر عن البيانات المطلوبة للتأييد فقط (انحياز لقراءة محددة).

---

## 5. السلبيات والتحسينات المقترحة

1. **اعتماد الموظفين على الجلسة في الكونترولر:** يمكن تمريرهم عبر Request/Value Object أو جلبهم عبر خدمة (مثلاً SessionEmployeesProvider) تُحقَن في الكونترولر أو الـ Handler ليكون المصدر واضحاً وقابل للاختبار.
2. **ربط قوي بأسماء أعمدة عربية في Infrastructure:** يمكن استخدام config أو mapping لأسماء الأعمدة لتقليل التعديل المباشر في الكود عند تغيير الجدول.
3. **عدم وجود Form Request للتأييد:** الطلب بسيط (معرّف فقط)؛ إضافة Request للتحقق من `id` اختيارية لكنها توحّد النمط مع باقي المسارات.
4. **استثناء بمفتاح 404:** يمكن استبداله بإرجاع null من الـ Handler ومعالجة 404 في الكونترولر فقط، أو استثناء دومين مثل `StudentNotFound` يُترجم إلى 404 في طبقة العرض.

---

## 6. نسبة التقييم (Clean Architecture)

| المعيار                    | الوزن التقريبي | التقييم (من 10) | الملاحظة |
|---------------------------|----------------|-----------------|----------|
| فصل الطبقات              | 25%            | 9               | واضح مع هيمنة بسيطة للعرض (الجلسة) |
| اتجاه التبعيات           | 20%            | 9               | من الخارج للداخل، دون خرق واضح |
| استقلال الدومين          | 20%            | 9               | StudentCertificate وواجهة الريبو بدون إطار |
| CQRS / Query فقط         | 15%            | 10              | مسار قراءة فقط، بدون كتابة |
| قابلية الاختبار          | 10%            | 8               | الريبو قابلة للاستبدال؛ الجلسة تحتاج mock |
| وضوح المسار وسهولة القراءة | 10%            | 9               | مسار قصير ومباشر |

**التقييم المرجّح:**  
(9×0.25 + 9×0.20 + 9×0.20 + 10×0.15 + 8×0.10 + 9×0.10) ≈ **8.95 / 10**

**نسبة التقييم النهائية:** حوالي **90%** — متوافق بقوة مع معمارية Clean مع تحسينات بسيطة ممكنة (الجلسة، أسماء الأعمدة، معالجة 404).

---

## 7. ملفات غير مستخدمة في هذا المسار (تم إزالة الاعتماد عليها)

- **مودال التأييد:** لا يُستخدم؛ مسار التأييد = صفحة كاملة فقط. تم إزالة كود المودال من `public/js/students/index.js` وتبسيط `public/js/students/certificate.js` ليكون خاصاً بصفحة التأييد فقط (طباعة + تكيف الحجم).
- **قسم `.certificate-modal` في `certificate.css`:** تنسيقات قديمة لمودال غير مستخدم؛ يمكن حذفها لاحقاً لتنظيف الملف.

---

## 8. الخلاصة

- مسار «تأييد بدون درجات» من جدول الطلاب: **رابط → GET /students/{id}/certificate → StudentCertificateController → GetStudentCertificateQueryHandler → StudentReadRepository → main_table → View (أو JSON).**
- البناء **يخضع لمعمارية Clean** بدرجة عالية: طبقات واضحة، تبعيات للداخل، دومين مستقل، Query فقط، وواجهات قابلة للاستبدال.
- **الإيجابيات:** وضوح المسار، دومين نظيف، دعم HTML و JSON، وسهولة الاختبار.
- **السلبيات:** اعتماد الموظفين على الجلسة في الكونترولر، وربط قوي بأسماء الأعمدة في Infrastructure، ومعالجة 404 عبر استثناء برقم كود.
- **نسبة التقييم:** حوالي **90%** من حيث الامتثال لمعمارية Clean مع إمكانية رفع النسبة قليلاً بتحسينات موضعية (الجلسة، التعامل مع 404، وتخفيف اقتران أسماء الأعمدة).

تم تحديث الكود ليتوافق مع هذا المستند: إزالة كود مودال التأييد من `index.js`، واقتصار `certificate.js` على صفحة التأييد فقط.
