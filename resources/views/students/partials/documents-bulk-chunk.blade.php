@foreach($dtos as $dto)
    <div class="doc-bulk-page-break doc-bulk-loaded" data-student-id="{{ $dto->studentId }}">
        @include('students.partials.document-paper', ['dto' => $dto, 'editable' => false])
        <div class="doc-bulk-view-footer no-print" aria-label="رقم الصفحة"></div>
    </div>
@endforeach
