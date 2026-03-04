@if(isset($students) && $students->hasPages())
    <div class="pagination">
        <div class="pagination-info">
            عرض <strong>{{ $students->firstItem() }}</strong> إلى <strong>{{ $students->lastItem() }}</strong>
            من <strong>{{ $students->total() }}</strong> سجل
        </div>
        <div class="pagination-buttons">
            {{ $students->links() }}
        </div>
    </div>
@endif
