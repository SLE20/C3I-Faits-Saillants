<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LocationRequest;
use App\Models\Location;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class LocationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(Location::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/location');
        CRUD::setEntityNameStrings('localisation', 'localisations');
    }

    protected function setupListOperation()
    {
        foreach ([
            'name' => 'Lieu',
            'department' => 'Département',
            'commune' => 'Commune',
            'communal_section' => 'Section communale',
            'neighborhood' => 'Quartier',
        ] as $name => $label) {
            CRUD::addColumn([
                'name' => $name,
                'label' => $label,
                'type' => 'text',
            ]);
        }

        CRUD::addColumn([
            'name' => 'is_active',
            'label' => 'Active',
            'type' => 'boolean',
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(LocationRequest::class);

        CRUD::addField([
            'name' => 'name',
            'label' => 'Nom du lieu',
            'type' => 'text',
            'hint' => 'Exemple : Carrefour de l’aéroport, Delmas.',
        ]);

        CRUD::addField([
            'name' => 'department',
            'label' => 'Département',
            'type' => 'select_from_array',
            'options' => [
                'Artibonite' => 'Artibonite',
                'Centre' => 'Centre',
                'Grand’Anse' => 'Grand’Anse',
                'Nippes' => 'Nippes',
                'Nord' => 'Nord',
                'Nord-Est' => 'Nord-Est',
                'Nord-Ouest' => 'Nord-Ouest',
                'Ouest' => 'Ouest',
                'Sud' => 'Sud',
                'Sud-Est' => 'Sud-Est',
            ],
            'allows_null' => true,
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        foreach ([
            'commune' => 'Commune',
            'communal_section' => 'Section communale',
            'neighborhood' => 'Quartier',
        ] as $name => $label) {
            CRUD::addField([
                'name' => $name,
                'label' => $label,
                'type' => 'text',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ]);
        }

        CRUD::addField([
            'name' => 'address',
            'label' => 'Adresse ou indications d’accès',
            'type' => 'textarea',
        ]);

        foreach ([
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
        ] as $name => $label) {
            CRUD::addField([
                'name' => $name,
                'label' => $label,
                'type' => 'number',
                'attributes' => ['step' => '0.0000001'],
                'wrapper' => ['class' => 'form-group col-md-6'],
            ]);
        }

        CRUD::addField([
            'name' => 'notes',
            'label' => 'Observations',
            'type' => 'textarea',
        ]);

        CRUD::addField([
            'name' => 'is_active',
            'label' => 'Localisation active',
            'type' => 'checkbox',
            'default' => 1,
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}