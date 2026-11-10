<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    public const GRADES = ['tk', 'k1', 'k2', 'k3', 'k4', 'k5'];

    protected $fillable = ['name', 'grade'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plays(): HasMany
    {
        return $this->hasMany(Play::class);
    }

    /** Bintang terbaik per "level:topic", sama seperti ws_best di versi offline. */
    public function bestStars(): array
    {
        return $this->plays()
            ->selectRaw('level, topic, MAX(stars) as best')
            ->groupBy('level', 'topic')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->level}:{$r->topic}" => (int) $r->best])
            ->all();
    }

    public function toAsahi(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'grade' => $this->grade];
    }
}
