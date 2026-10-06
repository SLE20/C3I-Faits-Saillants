<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'department' => ['required', 'string', 'max:100'],
            'commune' => ['required', 'string', 'max:150'],
            'communal_section' => ['nullable', 'string', 'max:150'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom du lieu',
            'department' => 'département',
            'commune' => 'commune',
            'communal_section' => 'section communale',
            'neighborhood' => 'quartier',
            'address' => 'adresse',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'notes' => 'observations',
            'is_active' => 'statut actif',
        ];
    }
}