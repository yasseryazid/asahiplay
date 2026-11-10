<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Play extends Model
{
    protected $fillable = [
        'level', 'topic', 'stars', 'items', 'misses', 'score',
        'together', 'by_kid', 'by_parent', 'stamp', 'played_at',
    ];

    protected function casts(): array
    {
        return [
            'together' => 'boolean',
            'played_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /** Bentuk entri Buku Kenangan yang dipakai frontend. */
    public function toAsahi(): array
    {
        return [
            'id' => $this->id,
            'd' => $this->played_at->getTimestampMs(),
            'lv' => $this->level,
            'tk' => $this->topic,
            'stars' => $this->stars,
            'score' => $this->score,
            'together' => $this->together,
            'by' => [$this->by_kid, $this->by_parent],
            'stamp' => $this->stamp,
        ];
    }
}
