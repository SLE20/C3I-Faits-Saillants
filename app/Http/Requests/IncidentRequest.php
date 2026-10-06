<?php

namespace App\Http\Requests;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],

            'event_date' => ['required', 'date_format:Y-m-d'],
            'event_time' => ['nullable', 'date_format:H:i'],

            'location_details' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'involved_parties' => ['nullable', 'string'],

            'deaths_count' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'injuries_count' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'kidnapped_count' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'arrests_count' => ['nullable', 'integer', 'min:0', 'max:4294967295'],

            'material_damage' => ['nullable', 'string'],
            'seizures' => ['nullable', 'string'],

            'source_type' => [
                'nullable',
                Rule::in(array_keys(Incident::SOURCE_TYPE_OPTIONS)),
            ],
            'source_details' => ['nullable', 'string'],
            'source_reference' => ['nullable', 'string', 'max:255'],

            'reliability' => [
                'required',
                Rule::in(array_keys(Incident::RELIABILITY_OPTIONS)),
            ],
            'importance' => [
                'required',
                Rule::in(array_keys(Incident::IMPORTANCE_OPTIONS)),
            ],
            'confidentiality' => [
                'required',
                Rule::in(array_keys(Incident::CONFIDENTIALITY_OPTIONS)),
            ],
            'verification_status' => [
                'required',
                Rule::in(array_keys(Incident::VERIFICATION_OPTIONS)),
            ],

            'follow_up' => ['nullable', 'string'],
            'analyst_notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // MySQL retourne généralement une heure au format HH:MM:SS.
        // Le formulaire et la validation utilisent HH:MM.
        $time = $this->input('event_time');

        if (
            is_string($time)
            && preg_match('/^\d{2}:\d{2}:00$/', $time)
        ) {
            $this->merge([
                'event_time' => substr($time, 0, 5),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'category_id' => 'catégorie',
            'location_id' => 'localisation',
            'event_date' => 'date du fait',
            'event_time' => 'heure du fait',
            'description' => 'description',
            'deaths_count' => 'nombre de morts',
            'injuries_count' => 'nombre de blessés',
            'kidnapped_count' => 'nombre de personnes enlevées',
            'arrests_count' => 'nombre de personnes interpellées',
            'source_type' => 'type de source',
            'source_reference' => 'référence de la source',
            'reliability' => 'fiabilité',
            'importance' => 'importance',
            'confidentiality' => 'confidentialité',
            'verification_status' => 'statut de vérification',
        ];
    }
}