<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Tenant;

final class TenantContext
{
    private ?Tenant $tenant = null;

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function tenantId(): ?string
    {
        return $this->tenant?->getKey();
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }
}