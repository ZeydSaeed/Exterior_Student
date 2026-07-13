@foreach($dtos as $dto)
    <div class="doc-bulk-page-break doc-bulk-loaded" data-student-id="{{ $dto->studentId }}">
        @include('students.partials.document-paper', ['dto' => $dto, 'editable' => false])
    </div>
@endforeach
