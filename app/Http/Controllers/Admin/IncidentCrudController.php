<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\IncidentRequest;
use App\Models\Category;
use App\Models\Incident;
use App\Models\Location;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class IncidentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(Incident::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/incident');
        CRUD::setEntityNameStrings('fait saillant', 'faits saillants');
    }

    protected function setupListOperation()
    {
        CRUD::addClause('with', ['category', 'location']);

        if (!request()->has('order')) {
            CRUD::addClause('orderBy', 'event_date', 'desc');
            CRUD::addClause('orderBy', 'id', 'desc');
        }

        CRUD::addColumns([
            [
                'name' => 'reference',
                'label' => 'Référence',
                'type' => 'text',
            ],
            [
                'name' => 'event_date',
                'label' => 'Date du fait',
                'type' => 'date',
                'format' => 'DD/MM/YYYY',
            ],
            [
                'name' => 'title',
                'label' => 'Titre',
                'type' => 'text',
                'limit' => 100,
            ],
            [
                'name' => 'category_id',
                'label' => 'Catégorie',
                'type' => 'select',
                'entity' => 'category',
                'model' => Category::class,
                'attribute' => 'name',
            ],
            [
                'name' => 'location_id',
                'label' => 'Lieu',
                'type' => 'select',
                'entity' => 'location',
                'model' => Location::class,
                'attribute' => 'name',
            ],
            [
                'name' => 'importance',
                'label' => 'Importance',
                'type' => 'select_from_array',
                'options' => Incident::IMPORTANCE_OPTIONS,
            ],
            [
                'name' => 'verification_status',
                'label' => 'Vérification',
                'type' => 'select_from_array',
                'options' => Incident::VERIFICATION_OPTIONS,
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(IncidentRequest::class);

        CRUD::addField([
            'name' => 'title',
            'label' => 'Titre du fait',
            'type' => 'text',
            'tab' => 'Identification',
            'attributes' => ['maxlength' => 255],
            'hint' => 'Un résumé court et précis de l’événement.',
        ]);

        CRUD::addField([
            'name' => 'category_id',
            'label' => 'Catégorie',
            'type' => 'select',
            'entity' => 'category',
            'model' => Category::class,
            'attribute' => 'name',
            'allows_null' => true,
            'options' => function ($query) {
                $entry = $this->crud->getCurrentEntry();
$currentId = $entry instanceof Incident ? $entry->category_id : null;

                return $query
                    ->where(function ($query) use ($currentId) {
                        $query->where('is_active', true);

                        if ($currentId) {
                            $query->orWhere('id', $currentId);
                        }
                    })
                    ->orderBy('name')
                    ->get();
            },
            'tab' => 'Identification',
        ]);

        CRUD::addField([
            'name' => 'event_date',
            'label' => 'Date du fait',
            'type' => 'date',
            'default' => now()->toDateString(),
            'tab' => 'Identification',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'event_time',
            'label' => 'Heure du fait',
            'type' => 'time',
            'attributes' => ['step' => 60],
            'hint' => 'Laisser vide si l’heure est inconnue.',
            'tab' => 'Identification',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'location_id',
            'label' => 'Localisation',
            'type' => 'select',
            'entity' => 'location',
            'model' => Location::class,
            'attribute' => 'name',
            'allows_null' => true,
            'options' => function ($query) {
                $entry = $this->crud->getCurrentEntry();
$currentId = $entry instanceof Incident ? $entry->location_id : null;

                return $query
                    ->where(function ($query) use ($currentId) {
                        $query->where('is_active', true);

                        if ($currentId) {
                            $query->orWhere('id', $currentId);
                        }
                    })
                    ->orderBy('name')
                    ->get();
            },
            'tab' => 'Localisation',
        ]);

        $this->addTextArea(
            'location_details',
            'Précisions sur le lieu',
            'Localisation'
        );

        $this->addTextArea(
            'description',
            'Description du fait',
            'Description',
            8
        );

        $this->addTextArea(
            'involved_parties',
            'Personnes ou groupes concernés',
            'Description'
        );

        foreach ([
            'deaths_count' => 'Nombre de morts',
            'injuries_count' => 'Nombre de blessés',
            'kidnapped_count' => 'Personnes enlevées',
            'arrests_count' => 'Personnes interpellées',
        ] as $name => $label) {
            CRUD::addField([
                'name' => $name,
                'label' => $label,
                'type' => 'number',
                'attributes' => [
                    'min' => 0,
                    'max' => 4294967295,
                    'step' => 1,
                ],
                'hint' => 'Vide = inconnu ; 0 = aucun cas signalé.',
                'tab' => 'Bilans',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ]);
        }

        $this->addTextArea(
            'material_damage',
            'Dégâts matériels et biens volés',
            'Bilans'
        );

        $this->addTextArea(
            'seizures',
            'Saisies et biens récupérés',
            'Bilans'
        );

        $this->addChoice(
            'source_type',
            'Type de source',
            Incident::SOURCE_TYPE_OPTIONS,
            'Source',
            null,
            true
        );

        $this->addTextArea(
            'source_details',
            'Informations sur la source',
            'Source'
        );

        CRUD::addField([
            'name' => 'source_reference',
            'label' => 'Référence de la source',
            'type' => 'text',
            'tab' => 'Source',
            'hint' => 'Numéro du rapport, référence du document ou lien.',
        ]);

        $this->addChoice(
            'reliability',
            'Fiabilité de la source',
            Incident::RELIABILITY_OPTIONS,
            'Source',
            'not_assessed'
        );

        $this->addChoice(
            'importance',
            'Importance du fait',
            Incident::IMPORTANCE_OPTIONS,
            'Évaluation et suivi',
            'medium'
        );

        $this->addChoice(
            'confidentiality',
            'Confidentialité',
            Incident::CONFIDENTIALITY_OPTIONS,
            'Évaluation et suivi',
            'internal'
        );

        $this->addChoice(
            'verification_status',
            'Statut de vérification',
            Incident::VERIFICATION_OPTIONS,
            'Évaluation et suivi',
            'to_verify'
        );

        $this->addTextArea(
            'follow_up',
            'Suites données',
            'Évaluation et suivi',
            5
        );

        $this->addTextArea(
            'analyst_notes',
            'Observations de l’analyste',
            'Évaluation et suivi',
            5
        );
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    private function addTextArea(
        string $name,
        string $label,
        string $tab,
        int $rows = 4
    ): void {
        CRUD::addField([
            'name' => $name,
            'label' => $label,
            'type' => 'textarea',
            'tab' => $tab,
            'attributes' => ['rows' => $rows],
        ]);
    }

    private function addChoice(
        string $name,
        string $label,
        array $options,
        string $tab,
        ?string $default = null,
        bool $allowsNull = false
    ): void {
        CRUD::addField([
            'name' => $name,
            'label' => $label,
            'type' => 'select_from_array',
            'options' => $options,
            'allows_null' => $allowsNull,
            'default' => $default,
            'tab' => $tab,
        ]);
    }
}