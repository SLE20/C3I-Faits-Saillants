@extends(backpack_view('blank'))

@section('content')
    <style>
        .c3i-chart-container {
            position: relative;
            height: 330px;
            width: 100%;
        }

        .c3i-chart-summary {
            padding: 12px 18px;
            background: #eaf0f6;
            color: #10243a;
            border-radius: 8px;
        }
    </style>

    <div class="container-fluid">
        <div class="mb-4">
            <h2 class="mb-1">
                <i class="la la-bar-chart"></i>
                Graphiques des faits saillants
            </h2>

            <p class="text-muted mb-0">
                Évolution et répartition des faits enregistrés.
            </p>
        </div>

        @if ($searchErrors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($searchErrors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ backpack_url('graphiques') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">
                                Date de début
                            </label>

                            <input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="form-control"
                                value="{{ $filters['start_date'] ?? '' }}"
                                required
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">
                                Date de fin
                            </label>

                            <input
                                id="end_date"
                                name="end_date"
                                type="date"
                                class="form-control"
                                value="{{ $filters['end_date'] ?? '' }}"
                                required
                            >
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="verification_status" class="form-label">
                                Vérification
                            </label>

                            <select
                                id="verification_status"
                                name="verification_status"
                                class="form-control"
                            >
                                <option value="">Tous les statuts</option>

                                @foreach (
                                    \App\Models\Incident::VERIFICATION_OPTIONS
                                    as $value => $label
                                )
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            ($filters['verification_status'] ?? '')
                                            === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <button type="submit" class="btn btn-primary">
                                Appliquer
                            </button>

                            <a
                                href="{{ backpack_url('graphiques') }}"
                                class="btn btn-outline-secondary"
                            >
                                Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if (!$searchErrors->any())
            <div class="c3i-chart-summary mb-4">
                <strong>{{ $total }} fait(s)</strong>
                sur la période sélectionnée.
                Les calculs utilisent la date du fait.

                <a
                    href="{{ route('c3i.incidents.search', $filters) }}"
                    class="ms-2"
                >
                    Consulter les faits
                </a>
            </div>

            @if ($total === 0)
                <div class="alert alert-info">
                    Aucun fait pour cette période et ce statut.
                </div>
            @else
                <div
                    id="chart-loading-error"
                    class="alert alert-danger d-none"
                ></div>

                <div class="row">
                    <div class="col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title mb-0">
                                    Évolution quotidienne
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="c3i-chart-container">
                                    <canvas
                                        id="daily-chart"
                                        role="img"
                                        aria-label="Nombre de faits par jour"
                                    >
                                        Nombre de faits par jour.
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title mb-0">
                                    Répartition par catégorie
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="c3i-chart-container">
                                    <canvas
                                        id="category-chart"
                                        role="img"
                                        aria-label="Nombre de faits par catégorie"
                                    >
                                        Nombre de faits par catégorie.
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title mb-0">
                                    Répartition par département
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="c3i-chart-container">
                                    <canvas
                                        id="department-chart"
                                        role="img"
                                        aria-label="Nombre de faits par département"
                                    >
                                        Nombre de faits par département.
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="{{ asset('js/chart.umd.js') }}"></script>

                <script>
                    (() => {
                        if (!window.Chart) {
                            const error = document.getElementById(
                                'chart-loading-error'
                            );

                            error.textContent =
                                'Chart.js n’a pas été chargé. Vérifiez le fichier public/js/chart.umd.js.';

                            error.classList.remove('d-none');
                            return;
                        }

                        const data = {{
                            \Illuminate\Support\Js::from($chartData)
                        }};

                        Chart.defaults.font.family = '"Segoe UI", Arial, sans-serif';
                        Chart.defaults.color = '#627489';

                        const reducedMotion = window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches;

                        function options(horizontal = false) {
                            return {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: reducedMotion ? false : undefined,
                                indexAxis: horizontal ? 'y' : 'x',

                                plugins: {
                                    legend: { display: false },
                                },

                                scales: {
                                    [horizontal ? 'x' : 'y']: {
                                        beginAtZero: true,
                                        ticks: { precision: 0 },
                                        title: {
                                            display: true,
                                            text: 'Nombre de faits',
                                        },
                                    },
                                },
                            };
                        }

                        new Chart(
                            document.getElementById('daily-chart'),
                            {
                                type: 'line',

                                data: {
                                    labels: data.daily.labels,
                                    datasets: [{
                                        label: 'Faits',
                                        data: data.daily.values,
                                        borderColor: '#10243a',
                                        backgroundColor: 'rgba(16,36,58,0.08)',
                                        pointBackgroundColor: '#a17b32',
                                        pointRadius: 3,
                                        borderWidth: 2,
                                        tension: 0,
                                        fill: true,
                                    }],
                                },

                                options: options(),
                            }
                        );

                        function createBar(id, series, color) {
                            const container = document
                                .getElementById(id)
                                .parentElement;

                            container.style.height =
                                Math.max(330, series.labels.length * 32) + 'px';

                            new Chart(document.getElementById(id), {
                                type: 'bar',

                                data: {
                                    labels: series.labels,
                                    datasets: [{
                                        label: 'Faits',
                                        data: series.values,
                                        backgroundColor: color,
                                        borderRadius: 4,
                                    }],
                                },

                                options: options(true),
                            });
                        }

                        createBar(
                            'category-chart',
                            data.categories,
                            '#10243a'
                        );

                        createBar(
                            'department-chart',
                            data.departments,
                            '#a17b32'
                        );
                    })();
                </script>
            @endif
        @endif
    </div>
@endsection