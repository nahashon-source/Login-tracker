<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Carbon\Carbon;

class LoginFilterService
{
    /**
     * Parse the filter parameters from the request (range, system, search)
     */
    public function parseFilters(Request $request): array
    {
        $range = $request->input('range', 'this_month');
        $system = $request->input('system', 'SCM');
        $search = $request->input('search', null);

        $endDate = Carbon::create(2025, 7, 31, 23, 59, 59);
        $startDate = match ($range) {
            'last_month'     => Carbon::create(2025, 6, 1, 0, 0, 0),
            'last_3_months'  => Carbon::create(2025, 5, 1, 0, 0, 0),
            default          => Carbon::create(2025, 7, 1, 0, 0, 0),
        };

        $rangeLabel = match ($range) {
            'last_month'     => 'Last Month',
            'last_3_months'  => 'Last 3 Months',
            default          => 'This Month',
        };

        return [
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'range'       => $range,
            'rangeLabel'  => $rangeLabel,
            'system'      => $system,
            'search'      => $search,
        ];
    }

    /**
     * Apply date and system filters to a query (works with Builder or HasMany)
     */
    public function applyDateAndSystemFilters(Builder|Relation $query, array $filters): Builder
    {
        // Convert relation to builder if needed (e.g., HasMany)
        if ($query instanceof Relation) {
            $query = $query->getQuery();
        }

        // Apply date range filters
        if (!empty($filters['startDate'])) {
            $query->where('date_utc', '>=', $filters['startDate']);
        }

        if (!empty($filters['endDate'])) {
            $query->where('date_utc', '<=', $filters['endDate']);
        }

        // Apply system filter (⚠️ adapt field name to your DB: 'application' or 'resource')
        if (!empty($filters['system'])) {
            $query->where('application', $filters['system']);  // <- adjust this if your DB uses 'resource'
        }

        return $query;
    }

    /**
     * Get the list of supported systems (for dropdown filtering)
     */
    public function getSystemList(): array
    {
        return [
            'SCM',
            'Odoo',
            'D365 Live',
            'Fit Express',
            'FIT ERP',
            'Fit Express UAT',
            'FITerp UAT',
            'OPS',
            'OPS UAT',
        ];
    }
}
