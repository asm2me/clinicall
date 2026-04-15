<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Tenant extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'public.tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'status',
        'schema_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}