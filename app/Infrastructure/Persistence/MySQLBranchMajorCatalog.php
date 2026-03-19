<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Student\BranchMajorCatalogInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MySQLBranchMajorCatalog implements BranchMajorCatalogInterface
{
    public function majorBelongsToBranch(string $majorNameAr, string $branchNameAr): bool
    {
        $majorNameAr = trim($majorNameAr);
        $branchNameAr = trim($branchNameAr);
        if ($majorNameAr === '' || $branchNameAr === '') {
            return false;
        }
        if (! Schema::hasTable('branches') || ! Schema::hasTable('majors')) {
            return false;
        }
        $branchId = DB::table('branches')->where('name_ar', $branchNameAr)->value('id');
        if ($branchId === null) {
            return false;
        }

        return DB::table('majors')
            ->where('name_ar', $majorNameAr)
            ->where('branch_id', $branchId)
            ->exists();
    }
}
