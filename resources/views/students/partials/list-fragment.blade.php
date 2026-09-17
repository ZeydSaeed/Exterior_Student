<div data-students-fragment="table">
    @include('students.partials.table')
    @include('students.partials.pagination')
</div>
<div data-students-fragment="summary">
    @include('students.partials.toolbar-filter-summary', ['useStudentListSessionMerge' => true])
</div>
