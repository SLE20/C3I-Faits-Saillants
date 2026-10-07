<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IncidentChartsController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'start_date' => $request->input(
                'start_date',
                now()->subDays(29)->toDateString()
            ),
            'end_date' => $request->input(
                'end_date',
                now()->toDateString()
            ),
            'verification_status' => $request->input(
                'verification_status',
                ''
            ),
        ];

        $validator = Validator::make($filters, [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'verification_status' => [
                'nullable',
                Rule::in(array_keys(Incident::VERIFICATION_OPTIONS)),
            ],
        ], [
            'start_date.required' => 'La date de début est obligatoire.',
            'end_date.required' => 'La date de fin est obligatoire.',
            'start_date.date_format' => 'La date de début est invalide.',
            'end_date.date_format' => 'La date de fin est invalide.',
            'end_date.after_or_equal' =>
                'La date de fin doit être égale ou postérieure à la date de début.',
            'verification_status.in' =>
                'Le statut de vérification est invalide.',
        ]);

        $validator->after(function ($validator) use ($filters) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $start = CarbonImmutable::parse($filters['start_date']);
            $end = CarbonImmutable::parse($filters['end_date']);

            if ($start->diffInDays($end) > 365) {
                $validator->errors()->add(
                    'end_date',
                    'Sélectionnez une période de 366 jours maximum.'
                );
            }
        });

        $chartData = [
            'daily' => ['labels' => [], 'values' => []],
            'categories' => ['labels' => [], 'values' => []],
            'departments' => ['labels' => [], 'values' => []],
        ];

        $total = 0;

        if ($validator->passes()) {
            $filters = $validator->validated();

            $query = Incident::query()->whereBetween('event_date', [
                $filters['start_date'],
                $filters['end_date'],
            ]);

            if (!empty($filters['verification_status'])) {
                $query->where(
                    'verification_status',
                    $filters['verification_status']
                );
            }

            $total = (clone $query)->count();

            // Évolution quotidienne.
            $dailyCounts = (clone $query)
                ->select('event_date')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('event_date')
                ->orderBy('event_date')
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->event_date->format('Y-m-d') => (int) $row->total,
                ]);

            foreach (
                CarbonPeriod::create(
                    $filters['start_date'],
                    $filters['end_date']
                ) as $day
            ) {
                $chartData['daily']['labels'][] = $day->format('d/m/Y');

                $chartData['daily']['values'][] =
                    $dailyCounts->get($day->format('Y-m-d'), 0);
            }

            // Répartition par catégorie.
            $categoryCounts = (clone $query)
                ->leftJoin(
                    'categories',
                    'incidents.category_id',
                    '=',
                    'categories.id'
                )
                ->selectRaw(
                    'COALESCE(categories.name, ?) as label, COUNT(*) as total',
                    ['Non renseignée']
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total')
                ->get();

            $chartData['categories'] = [
                'labels' => $categoryCounts->pluck('label')->all(),
                'values' => $categoryCounts
                    ->map(fn ($row) => (int) $row->total)
                    ->all(),
            ];

            // Répartition par département.
            $departmentCounts = (clone $query)
    ->leftJoin(
        'locations',
        'incidents.location_id',
        '=',
        'locations.id'
    )
    ->selectRaw(
        "COALESCE(NULLIF(TRIM(locations.department), ''), ?) as label,
        COUNT(*) as total",
        ['Non renseigné']
    )
    ->groupBy('label')
    ->orderByDesc('total')
    ->get();
        }

        return view('admin.incidents.charts', [
            'title' => 'Graphiques',
            'filters' => $filters,
            'searchErrors' => $validator->errors(),
            'chartData' => $chartData,
            'total' => $total,
        ]);
    }
}