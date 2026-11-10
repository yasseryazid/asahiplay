<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Akun = orang tua (login Google). Anak adalah profil di dalamnya.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'google_id', 'avatar',
        'parent_call', 'note_message', 'note_promise', 'sound', 'consent_at',
    ];

    protected $attributes = ['sound' => true];

    protected $hidden = ['password', 'remember_token', 'google_id'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'consent_at' => 'datetime',
            'sound' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function plays(): HasManyThrough
    {
        return $this->hasManyThrough(Play::class, Child::class);
    }

    /** Data profil untuk frontend Asahi. */
    public function toAsahi(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'parent' => $this->parent_call,
            'msg' => $this->note_message,
            'promise' => $this->note_promise,
            'sound' => $this->sound,
            'consented' => $this->consent_at !== null,
        ];
    }
}
