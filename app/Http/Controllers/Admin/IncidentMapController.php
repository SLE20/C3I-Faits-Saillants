<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Incident;
use App\Models\Location;
use Illuminate\Http\Request;

class IncidentMapController extends IncidentSearchController
{
    public function index(Request $request)
    {
        $validator = $this->filterValidator($request);

        $filters = $request->only([
            'q',
            'start_date',
            'end_date',
            'category_id',
            'department',
            'commune',
            'importance',
            'verification_status',
        ]);

        $points = [];
        $total = 0;
        $mapped = 0;

        if ($validator->passes()) {
            $filters = $validator->validated();

            $query = $this->filteredQuery($filters);

            $total = (clone $query)->count();

            $incidents = $query
                ->whereHas('location', function ($query) {
                    $query->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereBetween('latitude', [-90, 90])
                        ->whereBetween('longitude', [-180, 180]);
                })
                ->get();

            $mapped = $incidents->count();

            $points = $incidents->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'reference' => $incident->reference,
                    'title' => $incident->title,
                    'date' => $incident->event_date->format('d/m/Y'),
                    'time' => $incident->event_time
                        ? substr($incident->event_time, 0, 5)
                        : null,

                    'category' => $incident->category?->name
                        ?? 'Non renseignée',

                    'location' => $incident->location->name,
                    'commune' => $incident->location->commune,

                    'latitude' => (float) $incident->location->latitude,
                    'longitude' => (float) $incident->location->longitude,

                    'importance' => $incident->importance,
                    'importance_label' =>
                        Incident::IMPORTANCE_OPTIONS[$incident->importance]
                        ?? $incident->importance,

                    'verification_label' =>
                        Incident::VERIFICATION_OPTIONS[$incident->verification_status]
                        ?? $incident->verification_status,

                    'url' => backpack_url(
                        'incident/' . $incident->id . '/edit'
                    ),
                ];
            })->values()->all();
        }

        return view('admin.incidents.map', [
            'title' => 'Carte des faits',
            'filters' => $filters,
            'searchErrors' => $validator->errors(),
            'points' => $points,
            'total' => $total,
            'mapped' => $mapped,
            'unmapped' => $total - $mapped,

            'categories' => Category::orderBy('name')->get(),

            'departments' => Location::select('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department'),

            'communes' => Location::select('commune')
                ->distinct()
                ->orderBy('commune')
                ->pluck('commune'),
        ]);
    }
}