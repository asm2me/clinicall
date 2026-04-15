<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'tenant_role_user', 'user_id', 'role_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }
}