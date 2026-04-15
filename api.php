<?php
$cfg = require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/core/helpers.php';

header('Content-Type: application/json; charset=utf-8');

function api_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_request_path(): string
{
    if (!empty($_SERVER['PATH_INFO'])) {
        return '/' . trim((string)$_SERVER['PATH_INFO'], '/');
    }

    $uri = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');

    if ($scriptName && str_starts_with($uri, $scriptName)) {
        $uri = substr($uri, strlen($scriptName));
    }

    return '/' . trim((string)$uri, '/');
}

function api_request_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function api_permission_list(): array
{
    $all = [
        'clinic.view','clinic.create','clinic.edit','clinic.delete',
        'doctor.view','doctor.create','doctor.edit','doctor.delete',
        'schedule.view','schedule.create','schedule.edit','schedule.delete',
        'exception.view','exception.create','exception.delete',
        'appointment.view','appointment.create','appointment.edit','appointment.delete',
        'user.view','user.create','user.edit','user.delete',
    ];

    return array_values(array_filter($all, fn($permission) => Auth::can($permission)));
}

function api_user_payload(): ?array
{
    $user = Auth::user();
    if (!$user) {
        return null;
    }

    return [
        'id' => (string)$user['id'],
        'name' => (string)$user['name'],
        'email' => (string)$user['email'],
        'role' => (string)$user['role'],
        'tenantId' => isset($user['tenant_id']) ? (string)$user['tenant_id'] : null,
        'doctorId' => isset($user['doctor_id']) ? (string)$user['doctor_id'] : null,
    ];
}

function api_tenant_payload(): ?array
{
    $tenant = Auth::tenant();
    if (!$tenant) {
        return null;
    }

    return [
        'id' => (string)$tenant['id'],
        'name' => (string)$tenant['name'],
        'slug' => (string)$tenant['slug'],
    ];
}

function api_session_payload(): array
{
    return [
        'token' => session_id(),
        'user' => api_user_payload(),
        'tenant' => api_tenant_payload(),
        'permissions' => api_permission_list(),
        'expiresAt' => null,
    ];
}

function api_require_login(): void
{
    if (!Auth::check()) {
        api_json([
            'code' => 'unauthorized',
            'message' => 'Authentication required.',
        ], 401);
    }
}

$path = api_request_path();
$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($path === '/api/auth/login' && $method === 'POST') {
    $body = api_request_body();
    $email = trim((string)($body['email'] ?? ''));
    $password = (string)($body['password'] ?? '');

    if ($email === '' || $password === '') {
        api_json([
            'code' => 'invalid_credentials',
            'message' => 'Email and password are required.',
        ], 422);
    }

    $attempts = &$_SESSION['_login_attempts'];
    $lockUntil = $_SESSION['_login_lock'] ?? 0;

    if ($lockUntil > time()) {
        api_json([
            'code' => 'account_locked',
            'message' => 'Too many failed attempts. Try again later.',
            'lockout' => [
                'lockedUntil' => date(DATE_ATOM, (int)$lockUntil),
            ],
        ], 423);
    }

    if (!Auth::attempt($email, $password)) {
        $attempts = ($attempts ?? 0) + 1;

        if ($attempts >= 5) {
            $_SESSION['_login_lock'] = time() + 900;
            unset($_SESSION['_login_attempts']);

            api_json([
                'code' => 'account_locked',
                'message' => 'Account locked for 15 minutes due to too many failed attempts.',
                'lockout' => [
                    'lockedUntil' => date(DATE_ATOM, time() + 900),
                ],
            ], 423);
        }

        api_json([
            'code' => 'invalid_credentials',
            'message' => 'Invalid email or password.',
            'remainingAttempts' => 5 - $attempts,
        ], 401);
    }

    unset($_SESSION['_login_attempts'], $_SESSION['_login_lock']);

    api_json(api_session_payload());
}

if ($path === '/api/auth/me' && $method === 'GET') {
    api_require_login();
    api_json(api_session_payload());
}

if ($path === '/api/auth/logout' && $method === 'POST') {
    if (Auth::check()) {
        Auth::logout();
    }

    api_json([
        'ok' => true,
    ]);
}

if ($path === '/api/dashboard/summary' && $method === 'GET') {
    api_require_login();

    $tid = Auth::tenantId();
    $today = date('Y-m-d');
    $stats = [];
    $appointments = [];

    if ($tid) {
        $stats[] = [
            'key' => 'clinics',
            'label' => 'Clinics',
            'value' => (int)Database::val(
                "SELECT COUNT(*) FROM clinics WHERE tenant_id=:t AND enabled=" . Database::bool(true),
                [':t' => $tid]
            ),
        ];
        $stats[] = [
            'key' => 'doctors',
            'label' => 'Doctors',
            'value' => (int)Database::val(
                "SELECT COUNT(*) FROM doctors WHERE tenant_id=:t AND enabled=" . Database::bool(true),
                [':t' => $tid]
            ),
        ];
        $stats[] = [
            'key' => 'today',
            'label' => "Today's Appointments",
            'value' => (int)Database::val(
                "SELECT COUNT(*) FROM appointments WHERE tenant_id=:t AND appointment_date=:d",
                [':t' => $tid, ':d' => $today]
            ),
        ];
        $stats[] = [
            'key' => 'pending',
            'label' => 'Pending Approval',
            'value' => (int)Database::val(
                "SELECT COUNT(*) FROM appointments WHERE tenant_id=:t AND status='pending'",
                [':t' => $tid]
            ),
        ];
        $stats[] = [
            'key' => 'this_month',
            'label' => 'This Month',
            'value' => (int)Database::val(
                "SELECT COUNT(*) FROM appointments WHERE tenant_id=:t AND DATE_TRUNC('month',appointment_date::date)=DATE_TRUNC('month',CURRENT_DATE)",
                [':t' => $tid]
            ),
        ];

        $todayAppointments = Database::all(
            "SELECT a.*, c.name AS clinic_name, d.name AS doctor_name
             FROM appointments a
             JOIN clinics c ON c.id = a.clinic_id
             JOIN doctors d ON d.id = a.doctor_id
             WHERE a.tenant_id = :t AND a.appointment_date = :d
             ORDER BY a.appointment_time",
            [':t' => $tid, ':d' => $today]
        );

        $appointments = array_map(static function (array $appointment): array {
            return [
                'id' => (string)$appointment['id'],
                'patientName' => (string)$appointment['patient_name'],
                'time' => substr((string)$appointment['appointment_time'], 0, 5),
                'status' => (string)$appointment['status'],
                'doctorName' => (string)$appointment['doctor_name'],
                'location' => (string)$appointment['clinic_name'],
                'type' => null,
            ];
        }, $todayAppointments);
    } else {
        $stats[] = [
            'key' => 'tenants',
            'label' => 'Tenants',
            'value' => (int)Database::val("SELECT COUNT(*) FROM tenants"),
        ];
        $stats[] = [
            'key' => 'clinics',
            'label' => 'Clinics',
            'value' => (int)Database::val("SELECT COUNT(*) FROM clinics"),
        ];
        $stats[] = [
            'key' => 'doctors',
            'label' => 'Doctors',
            'value' => (int)Database::val("SELECT COUNT(*) FROM doctors"),
        ];
        $stats[] = [
            'key' => 'today',
            'label' => "Today's Appointments",
            'value' => (int)Database::val(
                "SELECT COUNT(*) FROM appointments WHERE appointment_date=:d",
                [':d' => $today]
            ),
        ];
    }

    api_json([
        'stats' => $stats,
        'appointments' => $appointments,
        'meta' => [
            'generatedAt' => date(DATE_ATOM),
            'tenantId' => $tid,
        ],
    ]);
}

api_json([
    'code' => 'not_found',
    'message' => 'Endpoint not found.',
], 404);
