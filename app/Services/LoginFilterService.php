<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;

class LoginFilterService
{
    public function parseFilters(Request $request): array
    {
        $range = $request->input('range');
        $system = $request->input('system');
        $search = $request->input('search');

        switch ($range) {
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                $label     = 'This Month';
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate   = Carbon::now()->subMonth()->endOfMonth();
                $label     = 'Last Month';
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                $label     = 'Last 3 Months';
                break;
            default:
                $startDate = Carbon::now()->subDays(30);
                $endDate   = Carbon::now();
                $label     = 'Last 30 Days';
        }

        return [
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'range'       => $range ?? 'last_30_days',
            'rangeLabel'  => $label,
            'system'      => $system,
            'search'      => $search,
        ];
    }

    public function getSystemList(): array
    {
        return [
            'D365 LIVE', 'FIT ERP', 'FIT EXPRESS', 'FIT EXPRESS UAT',
            'FIT ERP UAT', 'ODOO', 'OPS', 'OPS UAT'
        ];
    }
}
