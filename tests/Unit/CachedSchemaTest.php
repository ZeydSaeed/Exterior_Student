<?php

use App\Infrastructure\Persistence\CachedSchema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

it('caches schema lookups until reset', function () {
    Schema::dropIfExists('perf_schema_probe');
    Schema::create('perf_schema_probe', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
    });

    CachedSchema::reset();

    expect(CachedSchema::hasTable('perf_schema_probe'))->toBeTrue()
        ->and(CachedSchema::hasColumn('perf_schema_probe', 'name'))->toBeTrue();

    Schema::drop('perf_schema_probe');

    expect(CachedSchema::hasTable('perf_schema_probe'))->toBeTrue()
        ->and(CachedSchema::hasColumn('perf_schema_probe', 'name'))->toBeTrue();

    CachedSchema::reset();

    expect(CachedSchema::hasTable('perf_schema_probe'))->toBeFalse();
});
