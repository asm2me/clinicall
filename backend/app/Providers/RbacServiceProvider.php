<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class RbacServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(static function (?User $user, string $ability): ?bool {
            if ($user === null) {
                return null;
            }

            return $user->hasRole('super_admin') ? true : null;
        });
    }
}