<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    protected $fillable = ['slug', 'title', 'level', 'ordered', 'is_public', 'assets'];

    protected function casts(): array
    {
        return [
            'ordered' => 'boolean',
            'is_public' => 'boolean',
            'assets' => 'array',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }
}
