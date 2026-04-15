<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Repositories\TenantRepository;
use Illuminate\Http\JsonResponse;

final class AuthController extends Controller
{
    public function __construct(private readonly TenantRepository $tenants)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Authentication endpoint ready.',
            'tenant' => $this->tenants->findActiveById((string) $request->header('X-Tenant-Id')),
        ]);
    }
}