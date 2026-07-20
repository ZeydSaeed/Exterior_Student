<?php

use App\Infrastructure\Persistence\MySQLRecordCommandRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('saves a document when document number is entered with arabic digits', function () {
    if (! Schema::hasTable('records') || ! Schema::hasTable('students')) {
        $this->markTestSkipped('records/students tables are required.');
    }

    $studentId = DB::table('students')->orderByDesc('id')->value('id');
    if ($studentId === null) {
        $this->markTestSkipped('No students available for document create test.');
    }

    $repo = app(MySQLRecordCommandRepository::class);
    $repo->create(
        studentId: (int) $studentId,
        documentNumber: '١٨٨١٨',
        documentDate: '٢٠٢٦-٠٧-٢٠',
        addressee: 'جهة اختبار',
        purpose: 'غرض اختبار',
    );

    if (Schema::hasColumn('records', 'document_number')) {
        $row = DB::table('records')
            ->where('student_id', $studentId)
            ->where('document_number', '18818')
            ->orderByDesc('id')
            ->first();
    } else {
        $examNumber = DB::table('students')->where('id', $studentId)->value('exam_number');
        $row = DB::table('records')
            ->where('الرقم الامتحاني', $examNumber)
            ->where('رقم الوثيقة', 18818)
            ->orderByDesc('id')
            ->first();
    }

    expect($row)->not->toBeNull();

    DB::table('records')->where('id', $row->id)->delete();
});
