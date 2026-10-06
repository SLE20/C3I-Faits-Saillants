@extends(backpack_view('blank'))

@section('content')
    @php
        $importanceOptions = \App\Models\Incident::IMPORTANCE_OPTIONS;
        $verificationOptions = \App\Models\Incident::VERIFICATION_OPTIONS;

        $selectFields = [
            'category_id' => [
                'label' => 'Catégorie',
                'options' => $categories->pluck('name', 'id')->all(),
            ],
            'department' => [
                'label' => 'Département',
                'options' => $departments->mapWithKeys(
                    fn ($value) => [$value => $value]
                )->all(),
            ],
            'commune' => [
                'label' => 'Commune',
                'options' => $communes->mapWithKeys(
                    fn ($value) => [$value => $value]
                )->all(),
            ],
            'importance' => [
                'label' => 'Importance',
                'options' => $importanceOptions,
            ],
            'verification_status' => [
                'label' => 'Vérification',
                'options' => $verificationOptions,
            ],
        ];
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="la la-search"></i>
                    Recherche avancée
                </h2>

                <p class="text-muted mb-0">
                    Recherche des faits policiers et sécuritaires.
                </p>
            </div>

            <a
                href="{{ backpack_url('incident/create') }}"
                class="btn btn-primary mt-2"
            >
                <i class="la la-plus"></i>
                Nouveau fait
            </a>
        </div>

        @if ($searchErrors->any())
            <div class="alert alert-danger">
                <strong>Veuillez corriger les critères :</strong>

                <ul class="mb-0 mt-2">
                    @foreach ($searchErrors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ backpack_url('recherche') }}">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label for="q" class="form-label">
                                Mot-clé
                            </label>

                            <input
                                type="text"
                                id="q"
                                name="q"
                                class="form-control"
                                maxlength="200"
                                value="{{ $filters['q'] ?? '' }}"
                                placeholder="Référence, titre, description, personne ou groupe"
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">
                                Date de début
                            </label>

                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                class="form-control"
                                value="{{ $filters['start_date'] ?? '' }}"
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">
                                Date de fin
                            </label>

                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                class="form-control"
                                value="{{ $filters['end_date'] ?? '' }}"
                            >
                        </div>

                        @foreach ($selectFields as $name => $field)
                            <div class="col-md-4 mb-3">
                                <label for="{{ $name }}" class="form-label">
                                    {{ $field['label'] }}
                                </label>

                                <select
                                    id="{{ $name }}"
                                    name="{{ $name }}"
                                    class="form-control"
                                >
                                    <option value="">Tous</option>

                                    @foreach ($field['options'] as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                (string) ($filters[$name] ?? '')
                                                === (string) $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach

                    </div>

                    <p class="small text-muted">
                        Les dates portent sur la date du fait.
                        Tous les critères renseignés sont combinés.
                        Sans critère, tous les faits sont affichés.
                    </p>

                    <button type="submit" class="btn btn-primary">
                        <i class="la la-search"></i>
                        Rechercher
                    </button>

                    <a
                        href="{{ backpack_url('recherche') }}"
                        class="btn btn-outline-secondary"
                    >
                        Réinitialiser
                    </a>
                </form>
            </div>
        </div>

        @if ($incidents !== null)
            <div class="card">
               <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
    <h3 class="card-title mb-0">
        {{ number_format($incidents->total(), 0, ',', ' ') }}
        résultat(s)
    </h3>

    @if ($incidents->total() > 0)
        <a
            href="{{ route('c3i.incidents.export.csv', $filters) }}"
            class="btn btn-success btn-sm"
        >
            <i class="la la-download"></i>
            Exporter en CSV
        </a>
    @endif
</div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Référence / titre</th>
                                <th>Catégorie</th>
                                <th>Localisation</th>
                                <th>Importance</th>
                                <th>Vérification</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($incidents as $incident)
                                <tr>
                                    <td class="text-nowrap">
                                        {{ $incident->event_date->format('d/m/Y') }}

                                        @if ($incident->event_time)
                                            <div class="small text-muted">
                                                {{ substr($incident->event_time, 0, 5) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small text-muted">
                                            {{ $incident->reference }}
                                        </div>

                                        {{ $incident->title }}
                                    </td>

                                    <td>
                                        {{ $incident->category?->name ?? 'Non renseignée' }}
                                    </td>

                                    <td>
                                        {{ $incident->location?->name ?? 'Non renseigné' }}

                                        @if ($incident->location)
                                            <div class="small text-muted">
                                                {{ $incident->location->commune }}
                                                — {{ $incident->location->department }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $importanceOptions[$incident->importance] ?? $incident->importance }}
                                    </td>

                                    <td>
                                        {{ $verificationOptions[$incident->verification_status] ?? $incident->verification_status }}
                                    </td>

                                    <td>
                                        <a
                                            href="{{ backpack_url('incident/' . $incident->id . '/edit') }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i class="la la-edit"></i>
                                            Modifier
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Aucun fait ne correspond aux critères.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($incidents->hasPages())
                    <div class="card-footer">
                        {{ $incidents->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection