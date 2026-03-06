<form method="GET" action="{{ route('students.index') }}" class="students-search-form" id="students-search-form">
    @if(request('branch'))<input type="hidden" name="branch" value="{{ request('branch') }}">@endif
    @if(request('major'))<input type="hidden" name="major" value="{{ request('major') }}">@endif
    @if(request('gender'))<input type="hidden" name="gender" value="{{ request('gender') }}">@endif
    @if(request('year'))<input type="hidden" name="year" value="{{ request('year') }}">@endif
    <div class="students-search students-search-full">
        <input type="text" name="search" id="students-search-input" value="{{ request('search') }}" placeholder="البحث عن اسم الطالب او الرقم الامتحاني ثم اضغط على Enter ..." autocomplete="off" />
    </div>
</form>
<div class="students-table-wrapper">
    <table class="students-table">
        <thead>
            <tr>
                <th>#</th>
                <th>الرقم الامتحاني</th>
                <th>اسم الطالب </th>
                <th>الدرجات</th>
                <th>تأييد بدون درجات</th>
                <th>تأييد بالدرجات</th>
                <th>قيد الطالب</th>
                <th>الوثائق التي زود بها</th>
                <th>السجل الشخصي</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody id="students-table-body">
            @forelse($students as $student)
                @php $examNum = (string)($student->exam_number ?? ''); @endphp
                <tr class="students-data-row" data-exam="{{ e($examNum) }}" data-name="{{ e($student->full_name ?? '') }}">
                    <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                    <td>{!! \App\Support\Highlight::render($examNum, $searchPattern ?? null) !!}</td>
                    <td>{!! \App\Support\Highlight::render($student->full_name ?? '', $searchPattern ?? null) !!}</td>
                    <td>
                        <button type="button" class="btn-primary btn-grades-open btn-grades" title="عرض الدرجات"
                            data-student-id="{{ $student->id }}"
                            data-exam-number="{{ e($student->exam_number ?? '') }}"
                            data-name="{{ e($student->full_name ?? '') }}"
                            data-gender="{{ e($student->gender ?? '') }}"
                            data-branch="{{ e($student->branch ?? '') }}"
                            data-major="{{ e($student->major ?? '') }}"
                            data-year="{{ e($student->academic_year ?? '') }}"
                            data-result="{{ e($student->result ?? '') }}">
                            <span class="btn-label">الدرجات</span>
                        </button>
                    </td>
                    <td>
                        <a href="{{ route('students.certificate', ['id' => $student->id]) }}"
                           class="btn-primary btn-confirm-without-grades"
                           title="تأييد بدون درجات">
                            <span class="btn-label" style="font-family: 'Times New Roman', Times, serif;">تأييد بدون درجات</span>
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('students.certificate-with-grades', ['id' => $student->id]) }}"
                           class="btn-primary btn-confirm-with-grades"
                           title="تأييد بالدرجات">
                            <span class="btn-label" style="font-family: 'Times New Roman', Times, serif;">تأييد بالدرجات</span>
                        </a>
                    </td>
                    <td>
                        <button type="button" class="btn-primary btn-enroll" title="قيد الطالب">
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn-primary btn-docs" title="الوثائق التي زود بها">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 4h5l2 3h9v11H4z"/>
                            </svg>
                            <span class="btn-label">وثائق</span>
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn-primary btn-profile" title="السجل الشخصي">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="8" r="3"/>
                                <path d="M6 20c0-3 2.5-5 6-5s6 2 6 5"/>
                            </svg>
                            <span class="btn-label">سجل</span>
                        </button>
                    </td>
                    <td class="students-table-actions">
                        <div class="students-table-actions-inner">
                            <button type="button" class="btn-primary btn-edit-row" title="تعديل" aria-label="تعديل">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('students.destroy', $student->id) }}" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-primary btn-delete-row" title="حذف" aria-label="حذف">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M10 11v6"/>
                                        <path d="M14 11v6"/>
                                        <path d="M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
                                        <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="students-table-empty-row">
                    <td colspan="10">لا توجد بيانات طلبة حالياً.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
