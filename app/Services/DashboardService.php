<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Incident;

class DashboardService
{
    public function data(): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        return [
            'stats' => [
                'total' => Incident::count(),

                'today' => Incident::where('event_date', $today)->count(),

                'month' => Incident::whereBetween('event_date', [
                    $monthStart,
                    $today,
                ])->count(),

                'to_verify' => Incident::where(
                    'verification_status',
                    'to_verify'
                )->count(),

                'critical' => Incident::where('importance', 'critical')
                    ->whereIn('verification_status', [
                        'to_verify',
                        'confirmed',
                    ])
                    ->count(),

                'confirmed' => Incident::where(
                    'verification_status',
                    'confirmed'
                )->count(),

                'disproved' => Incident::where(
                    'verification_status',
                    'disproved'
                )->count(),
            ],

            'categories' => Category::withCount('incidents')
                ->orderByDesc('incidents_count')
                ->orderBy('name')
                ->get(),

            'recentIncidents' => Incident::with(['category', 'location'])
                ->orderByDesc('event_date')
                ->orderByDesc('event_time')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
        ];
    }
}