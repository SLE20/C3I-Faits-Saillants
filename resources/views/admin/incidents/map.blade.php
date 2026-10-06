@extends(backpack_view('blank'))

@section('content')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >

    <style>
        #incident-map {
            height: 650px;
            height: clamp(420px, 70vh, 800px);
            width: 100%;
            border-radius: 0 0 12px 12px;
            z-index: 0;
        }

        .map-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13px;
        }

        .map-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .map-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .c3i-map-popup {
            max-height: 320px;
            overflow-y: auto;
            min-width: 230px;
        }

        .c3i-map-popup article {
            padding: 12px 0;
            border-bottom: 1px solid #dce4ee;
        }

        .c3i-map-popup article:last-child {
            border-bottom: none;
        }

        .c3i-map-popup h4 {
            margin: 5px 0;
            font-size: 14px;
            color: #10243a;
        }

        .c3i-map-popup p {
            margin: 5px 0;
            font-size: 12px;
        }

        .c3i-map-popup a {
            display: inline-block;
            margin-top: 6px;
            font-weight: 700;
            color: #10243a;
        }
    </style>

    @php
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
                'options' => \App\Models\Incident::IMPORTANCE_OPTIONS,
            ],
            'verification_status' => [
                'label' => 'Vérification',
                'options' => \App\Models\Incident::VERIFICATION_OPTIONS,
            ],
        ];
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="la la-map"></i>
                    Carte des faits
                </h2>

                <p class="text-muted mb-0">
                    Répartition des faits par localisation enregistrée.
                </p>
            </div>

            <a
                href="{{ backpack_url('location') }}"
                class="btn btn-outline-primary mt-2"
            >
                Gérer les localisations
            </a>
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
                <form method="GET" action="{{ backpack_url('carte') }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="q" class="form-label">Mot-clé</label>

                            <input
                                id="q"
                                name="q"
                                type="text"
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
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="form-control"
                                value="{{ $filters['start_date'] ?? '' }}"
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

                    <button type="submit" class="btn btn-primary">
                        <i class="la la-filter"></i>
                        Appliquer les filtres
                    </button>

                    <a
                        href="{{ backpack_url('carte') }}"
                        class="btn btn-outline-secondary"
                    >
                        Réinitialiser
                    </a>

                    <a
                        href="{{ route('c3i.incidents.search', $filters) }}"
                        class="btn btn-outline-primary"
                    >
                        Voir les résultats en liste
                    </a>
                </form>
            </div>
        </div>

        @if (!$searchErrors->any())
            <div class="row mb-3">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Faits correspondant aux filtres</div>
                            <strong class="h2">{{ $total }}</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Faits cartographiés</div>
                            <strong class="h2">{{ $mapped }}</strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Sans coordonnées valides</div>
                            <strong class="h2">{{ $unmapped }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            @if ($unmapped > 0)
                <div class="alert alert-warning">
                    {{ $unmapped }} fait(s) ne peuvent pas être placés.
                    Renseignez la latitude et la longitude du lieu associé,
                    ou associez une localisation à ces faits.
                </div>
            @endif

            @if ($mapped === 0)
                <div class="alert alert-info">
                    Aucun fait cartographiable pour cette sélection.
                    La carte affiche la vue générale d’Haïti.
                </div>
            @endif
        @endif

        <div id="map-loading-error" class="alert alert-warning d-none"></div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="map-legend">
                    <span>
                        <i class="map-dot" style="background:#64748b;"></i>
                        Faible
                    </span>

                    <span>
                        <i class="map-dot" style="background:#2563eb;"></i>
                        Moyenne
                    </span>

                    <span>
                        <i class="map-dot" style="background:#b77900;"></i>
                        Élevée
                    </span>

                    <span>
                        <i class="map-dot" style="background:#dc2626;"></i>
                        Critique
                    </span>
                </div>

                <button
                    type="button"
                    id="recenter-map"
                    class="btn btn-sm btn-outline-primary"
                    disabled
                >
                    Recentrer sur les résultats
                </button>
            </div>

            <div
                id="incident-map"
                aria-label="Carte interactive des faits saillants"
            ></div>

            <div class="card-footer small text-muted">
                Les faits aux mêmes coordonnées sont regroupés.
                Le point prend la couleur de l’importance la plus élevée
                du groupe, tous statuts confondus.
                Cliquez sur un point pour consulter les faits.
            </div>
        </div>
    </div>

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
        (() => {
            const errorBox = document.getElementById('map-loading-error');

            if (!window.L) {
                errorBox.textContent =
                    'La bibliothèque de carte n’a pas pu être chargée. Vérifiez la connexion Internet.';
                errorBox.classList.remove('d-none');
                return;
            }

            const points = {{ \Illuminate\Support\Js::from($points) }};

            const colors = {
                low: '#64748b',
                medium: '#2563eb',
                high: '#b77900',
                critical: '#dc2626',
            };

            const ranks = {
                low: 1,
                medium: 2,
                high: 3,
                critical: 4,
            };

            const map = L.map('incident-map', {
                preferCanvas: true,
                scrollWheelZoom: false,
            }).setView([18.97, -72.29], 8);

            const tiles = L.tileLayer(
                'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution:
                        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                }
            ).addTo(map);

            tiles.on('tileerror', () => {
                errorBox.textContent =
                    'Certaines parties du fond de carte n’ont pas pu être chargées. Vérifiez la connexion Internet.';
                errorBox.classList.remove('d-none');
            });

            const groups = new Map();
            const bounds = [];

            points.forEach(point => {
                const key = `${point.latitude},${point.longitude}`;

                if (!groups.has(key)) {
                    groups.set(key, []);
                }

                groups.get(key).push(point);
            });

            // Les données sont ajoutées comme texte, sans interprétation HTML.
            function addText(parent, tag, text) {
                const element = document.createElement(tag);
                element.textContent = text;
                parent.appendChild(element);
                return element;
            }

            groups.forEach(group => {
                const first = group[0];

                const highest = group.reduce((current, point) =>
                    (ranks[point.importance] || 0)
                        > (ranks[current.importance] || 0)
                        ? point
                        : current
                );

                const popup = document.createElement('div');
                popup.className = 'c3i-map-popup';

                addText(
                    popup,
                    'strong',
                    `${group.length} fait(s) à ces coordonnées`
                );

                group.forEach(point => {
                    const article = document.createElement('article');

                    addText(article, 'small', point.reference);
                    addText(article, 'h4', point.title);

                    addText(
                        article,
                        'p',
                        `${point.date}${point.time ? ' à ' + point.time : ''}`
                    );

                    addText(article, 'p', point.category);

                    addText(
                        article,
                        'p',
                        `${point.location} — ${point.commune}`
                    );

                    addText(
                        article,
                        'p',
                        `${point.importance_label} · ${point.verification_label}`
                    );

                    const link = addText(article, 'a', 'Ouvrir la fiche');
                    link.href = point.url;

                    popup.appendChild(article);
                });

                const tooltip = document.createElement('span');
                tooltip.textContent =
                    `${first.location} — ${group.length} fait(s)`;

                L.circleMarker(
                    [first.latitude, first.longitude],
                    {
                        radius: Math.min(9 + Math.log2(group.length) * 3, 22),
                        color: '#ffffff',
                        weight: 2,
                        fillColor: colors[highest.importance] || colors.low,
                        fillOpacity: 0.9,
                    }
                )
                    .addTo(map)
                    .bindTooltip(tooltip)
                    .bindPopup(popup, { maxWidth: 360 });

                bounds.push([first.latitude, first.longitude]);
            });

            function recenter() {
                if (bounds.length) {
                    map.fitBounds(bounds, {
                        padding: [40, 40],
                        maxZoom: 14,
                    });
                } else {
                    map.setView([18.97, -72.29], 8);
                }
            }

            recenter();

            const recenterButton = document.getElementById('recenter-map');
            recenterButton.disabled = false;
            recenterButton.addEventListener('click', recenter);

            const observer = new ResizeObserver(() => map.invalidateSize());
            observer.observe(document.getElementById('incident-map'));
        })();
    </script>
@endsection