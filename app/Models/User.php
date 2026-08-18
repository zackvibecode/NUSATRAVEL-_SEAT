<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'pic_name'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    /**
     * PIC identity used for filtering payment records. Admins get null
     * (no filter — they see every PIC). Sales users fall back to their
     * display name when no explicit PIC name is configured.
     */
    public function picFilterName(): ?string
    {
        if ($this->isAdmin()) {
            return null;
        }

        return filled($this->pic_name) ? trim((string) $this->pic_name) : trim((string) $this->name);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
