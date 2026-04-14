<?php
/**
 * ClinicAll — Authentication & Authorization
 */
class Auth
{
    // Role → allowed permissions  (* = all)
    private const ROLE_PERMS = [
        'superadmin' => ['*'],
        'admin'  => [
            'clinic.view','clinic.create','clinic.edit','clinic.delete',
            'doctor.view','doctor.create','doctor.edit','doctor.delete',
            'schedule.view','schedule.create','schedule.edit','schedule.delete',
            'exception.view','exception.create','exception.delete',
            'appointment.view','appointment.create','appointment.edit','appointment.delete',
            'user.view','user.create','user.edit','user.delete',
        ],
        'staff'  => [
            'clinic.view','doctor.view','schedule.view','exception.view',
            'appointment.view','appointment.create','appointment.edit',
        ],
        'doctor' => [
            'schedule.view','appointment.view',
        ],
    ];

    // ── Login / Logout ────────────────────────────────────────────────────

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::row(
            "SELECT * FROM users WHERE email = :e AND enabled = " . Database::bool(true),
            [':e' => strtolower(trim($email))]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Fetch tenant
        $tenant = null;
        if ($user['tenant_id']) {
            $tenant = Database::row(
                "SELECT * FROM tenants WHERE id = :id AND enabled = " . Database::bool(true),
                [':id' => $user['tenant_id']]
            );
            if (!$tenant && $user['role'] !== 'superadmin') {
                return false;  // tenant disabled
            }
        }

        session_regenerate_id(true);
        $_SESSION['user']   = [
            'id'        => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'tenant_id' => $user['tenant_id'],
            'doctor_id' => $user['doctor_id'] ?? null,
        ];
        $_SESSION['tenant'] = $tenant;

        // Update last_login
        Database::exec(
            "UPDATE users SET last_login = NOW() WHERE id = :id",
            [':id' => $user['id']]
        );

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ── State helpers ─────────────────────────────────────────────────────

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    /** Redirect to login if not authenticated */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('?page=login');
        }
    }

    public static function user(): ?array  { return $_SESSION['user']   ?? null; }
    public static function tenant(): ?array { return $_SESSION['tenant'] ?? null; }

    public static function userId(): ?string    { return self::user()['id']        ?? null; }
    public static function tenantId(): ?string  { return self::user()['tenant_id'] ?? null; }
    public static function role(): string       { return self::user()['role']      ?? 'staff'; }

    public static function isSuperAdmin(): bool { return self::role() === 'superadmin'; }
    public static function isAdmin(): bool      { return in_array(self::role(), ['superadmin','admin']); }

    // ── Permissions ───────────────────────────────────────────────────────

    public static function can(string $permission): bool
    {
        $role  = self::role();
        $perms = self::ROLE_PERMS[$role] ?? [];
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public static function requirePermission(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);
            flash('You do not have permission to perform this action.', 'danger');
            redirect('?page=dashboard');
        }
    }

    // ── Superadmin tenant switching ───────────────────────────────────────

    public static function switchTenant(string $tenantId): bool
    {
        if (!self::isSuperAdmin()) { return false; }
        $tenant = Database::row(
            "SELECT * FROM tenants WHERE id = :id",
            [':id' => $tenantId]
        );
        if (!$tenant) { return false; }
        $_SESSION['tenant']              = $tenant;
        $_SESSION['user']['tenant_id']   = $tenantId;
        return true;
    }

    // ── Password helpers ──────────────────────────────────────────────────

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
