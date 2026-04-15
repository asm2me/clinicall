<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Tenant;
use App\Services\Tenancy\TenantContext;
use App\Services\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

final class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, static fn (): TenantContext => new TenantContext());
        $this->app->singleton(TenantResolver::class, static fn ($app): TenantResolver => new TenantResolver(
            $app->make(Request::class),
            $app->make(TenantContext::class),
        ));
    }

    public function boot(TenantResolver $resolver): void
    {
        $resolver->resolve();
    }
}