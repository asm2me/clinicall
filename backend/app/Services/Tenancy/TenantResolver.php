<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;

final class TenantResolver
{
    public function __construct(
        private readonly Request $request,
        private readonly TenantContext $context,
    ) {
    }

    public function resolve(?Request $request = null): ?Tenant
    {
        $request ??= $this->request;

        $tenantId = $request->headers->get('X-Tenant-Id')
            ?? $request->query('tenant_id')
            ?? $request->route('tenant');

        if (!is_string($tenantId) || $tenantId === '') {
            $this->context->setTenant(null);

            return null;
        }

        $tenant = Tenant::query()->whereKey($tenantId)->where('status', 'active')->first();

        $this->context->setTenant($tenant);

        if ($tenant !== null) {
            $request->attributes->set('tenant', $tenant);
        }

        return $tenant;
    }
}