<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Models\ClassLineageMapping;
use Illuminate\Support\Carbon;

class ClassLineageResolver
{
    public function resolve(string $sourceReference, string $context, Carbon $at): ?ClassLineageMapping
    {
        $matches = ClassLineageMapping::query()
            ->where('source_class_reference', $sourceReference)
            ->where('mapping_context', $context)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $at->toDateString())
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>', $at->toDateString()))
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }
}
