<?php
/**
 * ClinicAll — Global helper functions
 */

// ── UUID ─────────────────────────────────────────────────────────────────────

function generate_uuid(): string
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

// ── Output escaping ──────────────────────────────────────────────────────────

function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── CSRF ─────────────────────────────────────────────────────────────────────

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return $token && hash_equals(csrf_token(), $token);
}

// ── Flash messages ───────────────────────────────────────────────────────────

function flash(string $msg, string $type = 'success'): void
{
    $_SESSION['_flash'][] = ['msg' => $msg, 'type' => $type];
}

function flash_html(): string
{
    $out   = '';
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    foreach ($items as $f) {
        $cls = match($f['type']) {
            'danger'  => 'alert-danger',
            'warning' => 'alert-warning',
            'info'    => 'alert-info',
            default   => 'alert-success',
        };
        $out .= '<div class="alert ' . $cls . ' alert-dismissible fade show" role="alert">'
              . e($f['msg'])
              . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
              . '</div>';
    }
    return $out;
}

// ── Redirect ─────────────────────────────────────────────────────────────────

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// ── Request helpers ──────────────────────────────────────────────────────────

function post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function get_param(string $key, mixed $default = ''): mixed
{
    return $_GET[$key] ?? $default;
}

function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

function clean_uuid(?string $v): string
{
    return preg_replace('/[^a-f0-9\-]/i', '', (string)$v);
}

// ── Date / Time helpers ──────────────────────────────────────────────────────

function days_of_week(): array
{
    return [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',
            4=>'Thursday',5=>'Friday',6=>'Saturday'];
}

function appointment_statuses(): array
{
    return ['pending'=>'Pending','confirmed'=>'Confirmed',
            'cancelled'=>'Cancelled','completed'=>'Completed','no_show'=>'No Show'];
}

function status_badge(string $s): string
{
    $cls = match($s) {
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'primary',
        'no_show'   => 'warning',
        default     => 'secondary',
    };
    $label = appointment_statuses()[$s] ?? ucfirst($s);
    return "<span class=\"badge text-bg-$cls\">$label</span>";
}

function fmt_date(string $d, string $fmt = 'd M Y'): string
{
    return $d ? date($fmt, strtotime($d)) : '';
}

// ── Slot calculation ─────────────────────────────────────────────────────────

function time_slots(string $start, string $end, int $duration): array
{
    $slots   = [];
    $current = strtotime($start);
    $finish  = strtotime($end);
    while ($current < $finish) {
        $slots[] = date('H:i', $current);
        $current += $duration * 60;
    }
    return $slots;
}

function booked_slots(string $tenant_id, string $doctor_id, string $date): array
{
    $rows = Database::all(
        "SELECT appointment_time FROM appointments
         WHERE tenant_id = :t AND doctor_id = :d AND appointment_date = :dt
           AND status NOT IN ('cancelled')",
        [':t' => $tenant_id, ':d' => $doctor_id, ':dt' => $date]
    );
    return array_map(fn($r) => substr($r['appointment_time'], 0, 5), $rows);
}

function doctor_day_schedules(string $tenant_id, string $doctor_id, int $dow): array
{
    return Database::all(
        "SELECT * FROM doctor_schedules
         WHERE tenant_id = :t AND doctor_id = :d AND day_of_week = :dow AND enabled = " . Database::bool(true) . "
         ORDER BY start_time",
        [':t' => $tenant_id, ':d' => $doctor_id, ':dow' => $dow]
    );
}

function has_exception(string $tenant_id, string $doctor_id, string $date): bool
{
    return (int)Database::val(
        "SELECT COUNT(*) FROM schedule_exceptions
         WHERE tenant_id = :t AND doctor_id = :d AND exception_date = :dt",
        [':t' => $tenant_id, ':d' => $doctor_id, ':dt' => $date]
    ) > 0;
}

function available_slots(string $tenant_id, string $doctor_id, string $date): array
{
    if (has_exception($tenant_id, $doctor_id, $date)) { return []; }

    $dow       = (int)date('w', strtotime($date));
    $schedules = doctor_day_schedules($tenant_id, $doctor_id, $dow);
    if (empty($schedules)) { return []; }

    $booked    = booked_slots($tenant_id, $doctor_id, $date);
    $available = [];

    foreach ($schedules as $s) {
        $dur  = (int)$s['slot_duration'];
        $max  = (int)$s['max_appointments'];
        foreach (time_slots(substr($s['start_time'],0,5), substr($s['end_time'],0,5), $dur) as $slot) {
            $count = count(array_filter($booked, fn($b) => $b === $slot));
            if ($count < $max && !in_array($slot, $available, true)) {
                $available[] = $slot;
            }
        }
    }
    sort($available);
    return $available;
}

// ── Pagination helper ────────────────────────────────────────────────────────

function paginate(int $total, int $per_page, int $current_page, string $url_pattern): array
{
    $pages = (int)ceil($total / $per_page);
    return [
        'total'    => $total,
        'per_page' => $per_page,
        'page'     => $current_page,
        'pages'    => $pages,
        'offset'   => ($current_page - 1) * $per_page,
        'prev'     => $current_page > 1      ? str_replace('{page}', $current_page - 1, $url_pattern) : null,
        'next'     => $current_page < $pages ? str_replace('{page}', $current_page + 1, $url_pattern) : null,
    ];
}

// ── Tenant resolution for public booking ────────────────────────────────────

function resolve_tenant_by_slug(string $slug): ?array
{
    return Database::row(
        "SELECT * FROM tenants WHERE slug = :s AND enabled = " . Database::bool(true),
        [':s' => strtolower(trim($slug))]
    );
}

function resolve_tenant_by_host(string $host): ?array
{
    return Database::row(
        "SELECT * FROM tenants WHERE custom_domain = :h AND enabled = " . Database::bool(true),
        [':h' => strtolower(trim($host))]
    );
}
