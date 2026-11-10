<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = ['position', 'fmt', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }

    /** Soal dalam bentuk yang sama persis dengan objek soal di frontend. */
    public function toAsahi(): array
    {
        return ['fmt' => $this->fmt] + $this->payload;
    }
}
