<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

final class TenantRepository
{
    public function findActiveById(string $tenantId): ?Tenant
    {
        return Tenant::query()->whereKey($tenantId)->where('status', 'active')->first();
    }

    public function allActive(): Collection
    {
        return Tenant::query()->where('status', 'active')->orderBy('name')->get();
    }
}