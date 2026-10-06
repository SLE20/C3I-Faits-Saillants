<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Incident extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    public const RELIABILITY_OPTIONS = [
        'not_assessed' => 'Non évaluée',
        'low' => 'Faible',
        'medium' => 'Moyenne',
        'high' => 'Élevée',
    ];

    public const IMPORTANCE_OPTIONS = [
        'low' => 'Faible',
        'medium' => 'Moyenne',
        'high' => 'Élevée',
        'critical' => 'Critique',
    ];

    public const CONFIDENTIALITY_OPTIONS = [
        'internal' => 'Interne',
        'restricted' => 'Diffusion restreinte',
        'confidential' => 'Confidentiel',
    ];

    public const VERIFICATION_OPTIONS = [
        'to_verify' => 'À vérifier',
        'confirmed' => 'Confirmé',
        'disproved' => 'Infirmé',
    ];

    public const SOURCE_TYPE_OPTIONS = [
        'police_report' => 'Rapport de police',
        'field_report' => 'Compte rendu de terrain',
        'testimony' => 'Témoignage',
        'institution' => 'Source institutionnelle',
        'media' => 'Média',
        'social_network' => 'Réseau social',
        'other' => 'Autre',
    ];

    protected $fillable = [
        'title',
        'category_id',
        'location_id',
        'event_date',
        'event_time',
        'location_details',
        'description',
        'involved_parties',
        'deaths_count',
        'injuries_count',
        'kidnapped_count',
        'arrests_count',
        'material_damage',
        'seizures',
        'source_type',
        'source_details',
        'source_reference',
        'reliability',
        'importance',
        'confidentiality',
        'verification_status',
        'follow_up',
        'analyst_notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'deaths_count' => 'integer',
            'injuries_count' => 'integer',
            'kidnapped_count' => 'integer',
            'arrests_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Incident $incident) {
            // Référence indépendante de la date du fait.
            $incident->reference = 'FS-'
                . now()->format('Ymd')
                . '-'
                . Str::upper(Str::random(10));

            $incident->created_by = backpack_auth()->id();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}