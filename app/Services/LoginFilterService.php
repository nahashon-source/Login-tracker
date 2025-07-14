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

        // Calculate date ranges based on selection
        $now = Carbon::now();
        $endDate = $now->clone()->endOfMonth();
        $startDate = match ($range) {
            'last_month'     => $now->clone()->subMonth()->startOfMonth(),
            'last_3_months'  => $now->clone()->subMonths(3)->startOfMonth(),
            default          => $now->clone()->startOfMonth(),
        };

        // For last_month, end date should be end of last month
        if ($range === 'last_month') {
            $endDate = $now->clone()->subMonth()->endOfMonth();
        } elseif ($range === 'last_3_months') {
            $endDate = $now->clone()->endOfMonth();
        }

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

        // Apply system filter using system mapping
        if (!empty($filters['system'])) {
            $mappedSystems = collect(config('systemmap'))
                ->filter(function ($system) use ($filters) {
                    return $system === $filters['system'];
                })
                ->keys()
                ->toArray();
            if (!empty($mappedSystems)) {
                $query->where(function ($q) use ($mappedSystems) {
                    foreach ($mappedSystems as $mappedSystem) {
                        $q->orWhere('application', 'LIKE', "%$mappedSystem%");
                    }
                });
            }
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
