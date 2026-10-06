<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        CRUD::setModel(Category::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/category');
        CRUD::setEntityNameStrings('catégorie', 'catégories');
    }

    protected function setupListOperation()
    {
        CRUD::addColumns([
            [
                'name' => 'name',
                'label' => 'Catégorie',
                'type' => 'text',
            ],
            [
                'name' => 'description',
                'label' => 'Description',
                'type' => 'text',
            ],
            [
                'name' => 'is_active',
                'label' => 'Active',
                'type' => 'boolean',
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(CategoryRequest::class);

        CRUD::addFields([
            [
                'name' => 'name',
                'label' => 'Nom de la catégorie',
                'type' => 'text',
                'attributes' => ['maxlength' => 150],
            ],
            [
                'name' => 'description',
                'label' => 'Description',
                'type' => 'textarea',
            ],
            [
                'name' => 'is_active',
                'label' => 'Catégorie active',
                'type' => 'checkbox',
                'default' => 1,
            ],
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}