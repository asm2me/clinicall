<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenant
{
    public function __construct(private readonly TenantResolver $resolver)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->resolver->resolve($request);

        return $next($request);
    }
}