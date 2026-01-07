<?php

namespace App\Models;

use App\Support\Eloquent\Concerns\HasUserTimestamps;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layer extends Model
{
    use Filterable;
    use HasUserTimestamps;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'namespace',
        'datasource',
        'geometry',
        'geometry_type',
        'properties',
        'zindex',
        'writeable',
        'autoload',
        'default_style_id',
        'select_style_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'json',
            'writeable' => 'boolean',
            'autoload' => 'boolean',
        ];
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function defaultStyle(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'default_style_id');
    }

    public function selectStyle(): BelongsTo
    {
        return $this->belongsTo(Style::class, 'select_style_id');
    }

    public function member(): HasMany
    {
        return $this->hasMany(LayerGroupMember::class, 'layer_id');
    }

    public function group()
    {
        return $this->member()->with('group');
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class, 'layer_id');
    }
}
