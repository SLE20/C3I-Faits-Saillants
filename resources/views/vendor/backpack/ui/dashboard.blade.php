@extends(backpack_view('blank'))

@php
    $dashboard = app(\App\Services\DashboardService::class)->data();

    $stats = $dashboard['stats'];
    $categories = $dashboard['categories'];
    $recentIncidents = $dashboard['recentIncidents'];

    $importanceLabels = \App\Models\Incident::IMPORTANCE_OPTIONS;
    $verificationLabels = \App\Models\Incident::VERIFICATION_OPTIONS;

    $importanceColors = [
        'low' => 'secondary',
        'medium' => 'primary',
        'high' => 'warning',
        'critical' => 'danger',
    ];

    $verificationColors = [
        'to_verify' => 'warning',
        'confirmed' => 'success',
        'disproved' => 'secondary',
    ];

    $cards = [
        [
            'label' => 'Total des faits',
            'value' => $stats['total'],
            'icon' => 'la la-file-text',
            'color' => 'primary',
            'hint' => 'Toutes les périodes et tous les statuts.',
        ],
        [
            'label' => 'Faits du jour',
            'value' => $stats['today'],
            'icon' => 'la la-calendar',
            'color' => 'success',
            'hint' => 'Selon la date du fait.',
        ],
        [
            'label' => 'Faits du mois',
            'value' => $stats['month'],
            'icon' => 'la la-calendar-check-o',
            'color' => 'info',
            'hint' => 'Du début du mois à aujourd’hui.',
        ],
        [
            'label' => 'À vérifier',
            'value' => $stats['to_verify'],
            'icon' => 'la la-search',
            'color' => 'warning',
            'hint' => 'Toutes les périodes.',
        ],
        [
            'label' => 'Faits critiques',
            'value' => $stats['critical'],
            'icon' => 'la la-exclamation-triangle',
            'color' => 'danger',
            'hint' => 'Confirmés ou à vérifier, toutes les périodes.',
        ],
        [
            'label' => 'Faits confirmés',
            'value' => $stats['confirmed'],
            'icon' => 'la la-check-circle',
            'color' => 'success',
            'hint' => 'Toutes les périodes.',
        ],
    ];
@endphp

@section('content')
    <div class="container-fluid">

        {{-- En-tête --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">
                            POLICE NATIONALE D’HAÏTI
                        </div>

                        <h2 class="mb-1">C3I — Faits saillants</h2>

                        <p class="text-muted mb-0">
                            Suivi et analyse des faits policiers et sécuritaires.
                        </p>
                    </div>

                    <div class="mb-3">
                        <a
                            href="{{ backpack_url('incident/create') }}"
                            class="btn btn-primary"
                        >
                            <i class="la la-plus"></i>
                            Nouveau fait
                        </a>

                        <a
                            href="{{ backpack_url('incident') }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="la la-list"></i>
                            Tous les faits
                        </a>
                    </div>
                </div>

                <div class="small text-muted">
                    Situation au {{ now()->format('d/m/Y à H:i') }}
                    — heure d’Haïti.
                </div>
            </div>
        </div>

        {{-- Compteurs --}}
        <div class="row">
            @foreach ($cards as $card)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted mb-2">
                                        {{ $card['label'] }}
                                    </div>

                                    <div class="h1 mb-0">
                                        {{ number_format($card['value'], 0, ',', ' ') }}
                                    </div>
                                </div>

                                <i
                                    class="{{ $card['icon'] }} text-{{ $card['color'] }}"
                                    style="font-size: 2.5rem;"
                                    aria-hidden="true"
                                ></i>
                            </div>

                            <div class="small text-muted mt-3">
                                {{ $card['hint'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">

            {{-- Répartition par catégorie --}}
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            Répartition par catégorie
                        </h3>
                    </div>

                    <div class="card-body">
                        <p class="small text-muted">
                            Toutes les périodes et tous les statuts,
                            y compris les faits infirmés.
                        </p>

                        @forelse ($categories as $category)
                            @php
                                $percentage = $stats['total'] > 0
                                    ? ($category->incidents_count / $stats['total']) * 100
                                    : 0;
                            @endphp

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $category->name }}</span>

                                    <span>
                                        <strong>{{ $category->incidents_count }}</strong>
                                        <span class="text-muted">
                                            ({{ number_format($percentage, 1, ',', ' ') }} %)
                                        </span>
                                    </span>
                                </div>

                                <div
                                    class="progress"
                                    role="progressbar"
                                    aria-label="{{ $category->name }}"
                                    aria-valuenow="{{ $category->incidents_count }}"
                                    aria-valuemin="0"
                                    aria-valuemax="{{ max($stats['total'], 1) }}"
                                >
                                    <div
                                        class="progress-bar bg-primary"
                                        style="width: {{ $percentage }}%;"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">
                                Aucune catégorie enregistrée.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- État de vérification --}}
            <div class="col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            État de vérification
                        </h3>
                    </div>

                    <div class="card-body">
                        <p class="small text-muted">
                            Toutes les périodes.
                        </p>

                        <div class="d-flex justify-content-between border-bottom py-3">
                            <span>À vérifier</span>
                            <strong class="text-warning">
                                {{ $stats['to_verify'] }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between border-bottom py-3">
                            <span>Confirmés</span>
                            <strong class="text-success">
                                {{ $stats['confirmed'] }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between py-3">
                            <span>Infirmés</span>
                            <strong class="text-muted">
                                {{ $stats['disproved'] }}
                            </strong>
                        </div>

                        <p class="small text-muted mt-3 mb-0">
                            L’importance du fait et sa confirmation
                            sont deux évaluations distinctes.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Derniers faits --}}
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    Les 10 derniers faits par date d’événement
                </h3>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Référence / titre</th>
                            <th>Catégorie</th>
                            <th>Lieu</th>
                            <th>Importance</th>
                            <th>Vérification</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recentIncidents as $incident)
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

                                    <div>{{ $incident->title }}</div>
                                </td>

                                <td>
                                    {{ $incident->category?->name ?? 'Non renseignée' }}
                                </td>

                                <td>
                                    {{ $incident->location?->name ?? 'Non renseigné' }}
                                </td>

                                <td>
                                    <span class="badge bg-{{ $importanceColors[$incident->importance] ?? 'secondary' }} text-white">
                                        {{ $importanceLabels[$incident->importance] ?? $incident->importance }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $verificationColors[$incident->verification_status] ?? 'secondary' }} text-white">
                                        {{ $verificationLabels[$incident->verification_status] ?? $incident->verification_status }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ backpack_url('incident/' . $incident->id . '/edit') }}"
                                        class="btn btn-sm btn-outline-primary"
                                        aria-label="Modifier le fait {{ $incident->reference }}"
                                    >
                                        <i class="la la-edit"></i>
                                        Modifier
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aucun fait saillant enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection