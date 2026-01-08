<?php

namespace App\Models\Color;

use App\Support\Eloquent\Concerns\HasUserTimestamps;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColorItem extends Model
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
        'color_group_id',
        'label',
        'color',
    ];

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

    public function group(): Builder|BelongsTo|ColorGroup
    {
        return $this->belongsTo(ColorGroup::class, 'color_group_id');
    }
}
