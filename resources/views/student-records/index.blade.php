@extends('layouts.dashboard')

@section('title', 'وثائق الطالب')
@section('icon', asset('favicon-students.svg'))
@section('body_class', 'page-student-documents')

@section('content')
    <div class="documents-page-wrap">
        <div class="documents-page-actions">
            @if($dto->nextStudentId)
                <a href="{{ route('students.documents.index', ['id' => $dto->nextStudentId]) }}" class="btn-primary documents-next-btn" title="الطالب التالي حسب ترتيب جدول الطلاب">التالي</a>
            @endif
            <a href="{{ $students_index_url ?? route('students.index') }}" class="btn-primary documents-close-btn" title="إغلاق">إغلاق</a>
        </div>

    <div class="employees-page-header documents-page-header">
        <h1>وثائق الطالب</h1>
    </div>

    @if(session('success'))
        <p class="employees-success">{{ session('success') }}</p>
    @endif

    <div class="students-layout">
        <section class="students-table-area" aria-label="وثائق الطالب">
            <div class="documents-student-info">
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>الرقم الامتحاني:</strong> {{ $dto->examNumber ?: '—' }}</p>
                </div>
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>اسم الطالب:</strong> {{ $dto->studentName ?: '—' }}</p>
                </div>
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>الفرع:</strong> {{ $dto->branch ?: '—' }}</p>
                    <p class="documents-student-line"><strong>الاختصاص:</strong> {{ $dto->major ?: '—' }}</p>
                </div>
                <div class="documents-student-info-row">
                    <p class="documents-student-line"><strong>العام الدراسي:</strong> {{ $dto->academicYear ?: '—' }}</p>
                    <p class="documents-student-line"><strong>الدور:</strong> {{ $dto->round ?: '—' }}</p>
                    <p class="documents-student-line"><strong>الجنس:</strong> {{ $dto->gender ?: '—' }}</p>
                </div>
            </div>

            <div class="employees-card employees-card-documents">
                <div class="employees-card-add">
                    <h3 class="employees-add-title">إضافة وثيقة جديدة</h3>
                    <form method="POST" action="{{ route('students.documents.store', $dto->studentId) }}" class="students-search-form document-add-form">
                        @csrf
                        <div class="document-add-fields">
                            <input type="text" name="document_number" value="{{ old('document_number') }}" placeholder="رقم الوثيقة..." />
                            <input type="date" name="document_date" value="{{ old('document_date') }}" placeholder="التاريخ..." />
                            <input type="text" name="addressee" value="{{ old('addressee') }}" placeholder="الجهة المعنونة إليها..." />
                            <input type="text" name="purpose" value="{{ old('purpose') }}" placeholder="الغرض من الوثيقة..." />
                            <button type="submit" class="btn-primary">إضافة وثيقة</button>
                        </div>
                    </form>
                </div>

                <h2 class="employees-card-title">جدول الوثائق</h2>
                <div class="students-table-wrapper">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>رقم الوثيقة</th>
                                <th>تاريخها</th>
                                <th>الجهة المعنونة إليها</th>
                                <th>الغرض من الوثيقة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dto->records as $record)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('students.documents.update', [$dto->studentId, $record->id]) }}" class="inline-form">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="document_number" value="{{ $record->documentNumber ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="date" name="document_date" value="{{ $record->documentDate ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="text" name="addressee" value="{{ $record->addressee ?? '' }}" />
                                    </td>
                                    <td>
                                            <input type="text" name="purpose" value="{{ $record->purpose ?? '' }}" />
                                    </td>
                                    <td class="students-table-actions">
                                        <div class="students-table-actions-inner">
                                            <button type="submit" class="btn-primary btn-edit-row">حفظ</button>
                                        </form>
                                        <form method="POST" action="{{ route('students.documents.destroy', [$dto->studentId, $record->id]) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-primary btn-delete-row">حذف</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">لا توجد وثائق مسجلة لهذا الطالب.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    </div>
@endsection
