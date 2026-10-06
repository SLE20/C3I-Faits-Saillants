<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Incident;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IncidentSearchController extends Controller
{
    private const FILTER_KEYS = [
        'q',
        'start_date',
        'end_date',
        'category_id',
        'department',
        'commune',
        'importance',
        'verification_status',
    ];

    public function index(Request $request)
    {
        $validator = $this->filterValidator($request);

        $filters = $request->only(self::FILTER_KEYS);
        $incidents = null;

        if ($validator->passes()) {
            $filters = $validator->validated();

            $incidents = $this->filteredQuery($filters)
                ->paginate(20)
                ->appends($filters);
        }

        return view('admin.incidents.search', [
            'title' => 'Recherche avancée',
            'filters' => $filters,
            'searchErrors' => $validator->errors(),
            'incidents' => $incidents,

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

    public function exportCsv(Request $request)
    {
        $validator = $this->filterValidator($request);

        if ($validator->fails()) {
            return redirect()->to(
                backpack_url('recherche')
                . '?'
                . http_build_query($request->only(self::FILTER_KEYS))
            );
        }

        $query = $this->filteredQuery($validator->validated());

        $filename = 'C3I_faits_saillants_'
            . now()->format('Ymd_His')
            . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');

            // Marqueur UTF-8 pour la reconnaissance des accents dans Excel.
            fwrite($output, "\xEF\xBB\xBF");

            $headers = [
                'Référence',
                'Date du fait',
                'Heure',
                'Titre',
                'Catégorie',
                'Département',
                'Commune',
                'Lieu',
                'Description',
                'Morts',
                'Blessés',
                'Personnes enlevées',
                'Personnes interpellées',
                'Dégâts matériels',
                'Saisies et biens récupérés',
                'Importance',
                'Vérification',
                'Fiabilité',
                'Confidentialité',
                'Suites données',
            ];

            fputcsv($output, $headers, ';', '"', '');

            // Charge les résultats par lots, sans se limiter
            // aux 20 lignes de la page de recherche.
            foreach ($query->lazy(200) as $incident) {
                $row = [
                    $incident->reference,
                    $incident->event_date->format('d/m/Y'),
                    $incident->event_time
                        ? substr($incident->event_time, 0, 5)
                        : 'Inconnue',

                    $incident->title,
                    $incident->category?->name ?? '',
                    $incident->location?->department ?? '',
                    $incident->location?->commune ?? '',
                    $incident->location?->name ?? '',
                    $incident->description,

                    $incident->deaths_count ?? 'Inconnu',
                    $incident->injuries_count ?? 'Inconnu',
                    $incident->kidnapped_count ?? 'Inconnu',
                    $incident->arrests_count ?? 'Inconnu',

                    $incident->material_damage ?? '',
                    $incident->seizures ?? '',

                    Incident::IMPORTANCE_OPTIONS[$incident->importance]
                        ?? $incident->importance,

                    Incident::VERIFICATION_OPTIONS[$incident->verification_status]
                        ?? $incident->verification_status,

                    Incident::RELIABILITY_OPTIONS[$incident->reliability]
                        ?? $incident->reliability,

                    Incident::CONFIDENTIALITY_OPTIONS[$incident->confidentiality]
                        ?? $incident->confidentiality,

                    $incident->follow_up ?? '',
                ];

                $row = array_map(
                    fn ($value) => $this->csvCell($value),
                    $row
                );

                fputcsv($output, $row, ';', '"', '');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    protected function filterValidator(Request $request)
    {
        $rules = [
            'q' => ['nullable', 'string', 'max:200'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'commune' => ['nullable', 'string', 'max:150'],

            'importance' => [
                'nullable',
                Rule::in(array_keys(Incident::IMPORTANCE_OPTIONS)),
            ],

            'verification_status' => [
                'nullable',
                Rule::in(array_keys(Incident::VERIFICATION_OPTIONS)),
            ],
        ];

        if ($request->filled('start_date')) {
            $rules['end_date'][] = 'after_or_equal:start_date';
        }

        return Validator::make(
            $request->only(self::FILTER_KEYS),
            $rules,
            [
                'end_date.after_or_equal' =>
                    'La date de fin doit être égale ou postérieure à la date de début.',
            ],
            [
                'q' => 'mot-clé',
                'start_date' => 'date de début',
                'end_date' => 'date de fin',
                'category_id' => 'catégorie',
                'department' => 'département',
                'commune' => 'commune',
                'importance' => 'importance',
                'verification_status' => 'statut de vérification',
            ]
        );
    }

    protected function filteredQuery(array $filters): Builder
    {
        $query = Incident::query()
            ->with(['category', 'location']);

        $keyword = trim($filters['q'] ?? '');

        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword) {
                $value = '%' . $keyword . '%';

                $query->where('reference', 'like', $value)
                    ->orWhere('title', 'like', $value)
                    ->orWhere('description', 'like', $value)
                    ->orWhere('involved_parties', 'like', $value);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('event_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('event_date', '<=', $filters['end_date']);
        }

        foreach ([
            'category_id',
            'importance',
            'verification_status',
        ] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $query->where($field, $filters[$field]);
            }
        }

        if (
            !empty($filters['department'])
            || !empty($filters['commune'])
        ) {
            $query->whereHas('location', function ($query) use ($filters) {
                if (!empty($filters['department'])) {
                    $query->where('department', $filters['department']);
                }

                if (!empty($filters['commune'])) {
                    $query->where('commune', $filters['commune']);
                }
            });
        }

        return $query
            ->orderByDesc('event_date')
            ->orderByDesc('event_time')
            ->orderByDesc('id');
    }

    private function csvCell(mixed $value): string
    {
        $text = (string) $value;

        // Empêche les textes saisis d'être interprétés comme
        // des formules lors de l'ouverture dans un tableur.
        if (preg_match('/^[\s\x{FEFF}]*[=+\-@]/u', $text)) {
            return "'" . $text;
        }

        return $text;
    }
}