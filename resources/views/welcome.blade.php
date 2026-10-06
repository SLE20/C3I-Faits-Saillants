<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>C3I — Gestion des faits saillants</title>

    <style>
        :root {
            --navy: #10243a;
            --navy-light: #1c3a58;
            --gold: #d7b56d;
            --background: #f3f6fa;
            --text: #25374a;
            --muted: #627489;
            --border: #dce4ee;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background: var(--background);
            line-height: 1.6;
        }

        a {
            color: inherit;
        }

        .container {
            width: min(1120px, 90%);
            margin: 0 auto;
        }

        .header {
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 64px;
            height: 64px;
            flex-shrink: 0;
            border-radius: 14px;
            background: var(--navy);
            color: var(--gold);
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand-title {
            font-size: 14px;
            font-weight: 700;
        }

        .brand-subtitle {
            margin-top: 3px;
            font-size: 12px;
            color: var(--muted);
        }

        .header-link {
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .header-link:hover {
            text-decoration: underline;
        }

        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(
                125deg,
                var(--navy) 0%,
                var(--navy-light) 100%
            );
            color: white;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 430px;
            height: 430px;
            right: -170px;
            top: -190px;
            border: 75px solid rgba(215, 181, 109, 0.07);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 60px;
            align-items: center;
            padding: 76px 0;
        }

        .eyebrow {
            margin-bottom: 20px;
            color: var(--gold);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 22px;
            font-size: clamp(34px, 4.5vw, 54px);
            line-height: 1.12;
            letter-spacing: -1.5px;
        }

        .hero-description {
            max-width: 570px;
            margin: 0;
            color: #d4deea;
            font-size: 17px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 32px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 23px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button-primary {
            background: var(--gold);
            color: var(--navy);
        }

        .button-primary:hover {
            background: #e4c689;
        }

        .button-secondary {
            border-color: #6f8298;
            color: white;
            background: transparent;
        }

        .button-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        a:focus-visible {
            outline: 3px solid var(--gold);
            outline-offset: 5px;
        }

        .hero-note {
            margin-top: 20px;
            font-size: 12px;
            color: #b8c8d9;
        }

        .overview {
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.06);
        }

        .overview-label {
            color: var(--gold);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .overview h2 {
            margin: 10px 0 24px;
            font-size: 24px;
            line-height: 1.3;
        }

        .process-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 17px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.13);
        }

        .process-number {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: 8px;
            background: rgba(215, 181, 109, 0.15);
            color: var(--gold);
            font-weight: 700;
        }

        .process-item strong {
            display: block;
            font-size: 15px;
        }

        .process-item p {
            margin: 4px 0 0;
            color: #c4d1df;
            font-size: 13px;
        }

        .features {
            padding: 60px 0;
        }

        .section-heading {
            max-width: 650px;
            margin-bottom: 30px;
        }

        .section-heading h2 {
            margin: 0 0 10px;
            font-size: 28px;
            color: var(--navy);
        }

        .section-heading p {
            margin: 0;
            color: var(--muted);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature-card {
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: white;
        }

        .feature-label {
            margin-bottom: 16px;
            color: #8a6423;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .feature-card h3 {
            margin: 0 0 10px;
            color: var(--navy);
            font-size: 19px;
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .access-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            margin-top: 30px;
            padding: 28px;
            border-left: 4px solid var(--gold);
            border-radius: 10px;
            background: #e7edf4;
        }

        .access-panel h3 {
            margin: 0 0 6px;
            color: var(--navy);
            font-size: 18px;
        }

        .access-panel p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .access-tag {
            flex-shrink: 0;
            padding: 7px 12px;
            border-radius: 6px;
            background: white;
            color: var(--navy);
            font-size: 12px;
            font-weight: 700;
        }

        footer {
            padding: 24px 0;
            border-top: 1px solid var(--border);
            background: white;
            color: var(--muted);
            font-size: 12px;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        @media (max-width: 850px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 35px;
                padding: 50px 0;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {
            .header-inner {
                align-items: flex-start;
            }

            .brand-mark {
                width: 48px;
                height: 48px;
                font-size: 16px;
            }

            .brand-title {
                font-size: 12px;
            }

            .brand-subtitle {
                font-size: 11px;
            }

            .header-link {
                font-size: 12px;
            }

            .overview,
            .feature-card {
                padding: 22px;
            }

            .access-panel {
                align-items: flex-start;
                flex-direction: column;
            }

            .actions .button {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .button {
                transition: none;
            }

            .button:hover {
                transform: none;
            }
        }

        .hero::before {
    content: "";
    position: absolute;
    width: min(70vw, 580px);
    aspect-ratio: 1;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    background: url("/images/c3i-logo.jpeg") center / contain no-repeat;
    border-radius: 50%;
    opacity: 0.045;
    pointer-events: none;
}
    </style>
</head>

<body>
    @php
        $isConnected = backpack_auth()->check();

        $accessUrl = $isConnected
            ? backpack_url('dashboard')
            : backpack_url('login');
    @endphp

    <header class="header">
        <div class="container header-inner">
            <div class="brand">
                <div class="brand-mark">C3I</div>

                <div>
                    <div class="brand-title">
                        POLICE NATIONALE D’HAÏTI
                    </div>

                    <div class="brand-subtitle">
                        Gestion des faits saillants
                    </div>
                </div>
            </div>

            <a href="{{ $accessUrl }}" class="header-link">
                {{ $isConnected ? 'Tableau de bord' : 'Connexion' }}
            </a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <div class="eyebrow">
                        Commandement · Communication · Contrôle · Intelligence
                    </div>

                    <h1>
                        Une vision structurée<br>
                        des faits saillants.
                    </h1>

                    <p class="hero-description">
                        Centralisez les faits policiers et sécuritaires,
                        suivez leur vérification et retrouvez les informations
                        utiles à l’analyse de la situation.
                    </p>

                    <div class="actions">
                        <a href="{{ $accessUrl }}" class="button button-primary">
                            {{ $isConnected ? 'Ouvrir le tableau de bord' : 'Accéder au système' }}
                        </a>

                        <a href="#fonctionnalites" class="button button-secondary">
                            Découvrir les fonctionnalités
                        </a>
                    </div>

                    <div class="hero-note">
                        Espace de travail à usage interne — accès authentifié.
                    </div>
                </div>

                <aside class="overview" aria-label="Étapes de traitement des faits">
                    <div class="overview-label">
                        De l’information à l’analyse
                    </div>

                    <h2>Documenter. Vérifier. Analyser.</h2>

                    <div class="process-item">
                        <div class="process-number">01</div>

                        <div>
                            <strong>Enregistrer les faits</strong>

                            <p>
                                Date, lieu, description, bilans et origine
                                de l’information.
                            </p>
                        </div>
                    </div>

                    <div class="process-item">
                        <div class="process-number">02</div>

                        <div>
                            <strong>Évaluer et suivre</strong>

                            <p>
                                Importance, fiabilité, statut de vérification
                                et suites données.
                            </p>
                        </div>
                    </div>

                    <div class="process-item">
                        <div class="process-number">03</div>

                        <div>
                            <strong>Exploiter les informations</strong>

                            <p>
                                Indicateurs, recherche par critères
                                et export des résultats.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section id="fonctionnalites" class="features">
            <div class="container">
                <div class="section-heading">
                    <h2>Un espace dédié au travail de l’analyste</h2>

                    <p>
                        Des fiches structurées et des outils de consultation
                        pour suivre les événements dans le temps.
                    </p>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <div class="feature-label">Enregistrement</div>

                        <h3>Faits policiers et sécuritaires</h3>

                        <p>
                            Consignez les événements dans une fiche unique,
                            avec leur localisation, leur bilan
                            et les observations utiles.
                        </p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-label">Recherche</div>

                        <h3>Retrouver l’information</h3>

                        <p>
                            Combinez les critères de période, catégorie,
                            département, commune, importance
                            et vérification.
                        </p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-label">Analyse</div>

                        <h3>Suivre la situation</h3>

                        <p>
                            Consultez les indicateurs du tableau de bord
                            et exportez les résultats de recherche
                            au format CSV.
                        </p>
                    </article>
                </div>

                <div class="access-panel">
                    <div>
                        <h3>Informations réservées à l’usage du service</h3>

                        <p>
                            La consultation des fiches et des résultats
                            nécessite une connexion au système.
                        </p>
                    </div>

                    <span class="access-tag">C3I · Usage interne</span>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-inner">
            <span>
                © {{ date('Y') }} C3I — Police Nationale d’Haïti
            </span>

            <span>
                Système de gestion des faits saillants
            </span>
        </div>
    </footer>
</body>
</html>