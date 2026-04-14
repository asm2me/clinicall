<?php
/**
 * ClinicAll — JSON API
 *
 * Handles requests from both admin (session auth) and public booking (tenant slug).
 *
 * GET  ajax.php?action=slots&doctor_id=&date=&tid=
 * GET  ajax.php?action=doctors&clinic_id=&tid=
 * GET  ajax.php?action=calendar&doctor_id=&year=&month=&tid=
 * GET  ajax.php?action=stats&tid=          (admin auth required)
 */

$cfg = require_once __DIR__ . '/bootstrap.php';

if (!$cfg['app']['installed']) {
    http_response_code(503);
    echo json_encode(['error' => 'Not installed.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$action = preg_replace('/[^a-z_]/', '', (string)($_GET['action'] ?? ''));

// ── Resolve tenant ────────────────────────────────────────────────────────
// Priority: 1) logged-in session tenant, 2) ?tid= param, 3) slug from t param
$tenant_id = Auth::check() ? Auth::tenantId() : null;

if (!$tenant_id) {
    $raw_tid = clean_uuid($_GET['tid'] ?? '');
    if ($raw_tid) {
        $t = Database::row(
            "SELECT id FROM tenants WHERE id=:id AND enabled=".Database::bool(true),
            [':id' => $raw_tid]
        );
        if ($t) { $tenant_id = $t['id']; }
    }
}

if (!$tenant_id) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($_GET['t'] ?? '')));
    if ($slug) {
        $t = resolve_tenant_by_slug($slug);
        if ($t) { $tenant_id = $t['id']; }
    }
}

if (!$tenant_id) {
    echo json_encode(['error' => 'Tenant not resolved.']);
    exit;
}

// ── ACTION: slots ─────────────────────────────────────────────────────────
if ($action === 'slots') {
    $doctor_id = clean_uuid($_GET['doctor_id'] ?? '');
    $date      = preg_replace('/[^0-9\-]/', '', (string)($_GET['date'] ?? ''));

    if (!$doctor_id || !$date) { echo json_encode([]); exit; }

    // Verify doctor belongs to tenant
    if (!Database::row("SELECT id FROM doctors WHERE id=:id AND tenant_id=:t",[':id'=>$doctor_id,':t'=>$tenant_id])) {
        echo json_encode([]); exit;
    }

    $slots = available_slots($tenant_id, $doctor_id, $date);
    echo json_encode(array_values($slots));
    exit;
}

// ── ACTION: doctors ───────────────────────────────────────────────────────
if ($action === 'doctors') {
    $clinic_id = clean_uuid($_GET['clinic_id'] ?? '');
    if (!$clinic_id) { echo json_encode([]); exit; }

    $rows = Database::all(
        "SELECT id, name, specialty FROM doctors WHERE tenant_id=:t AND clinic_id=:c AND enabled=".Database::bool(true)." ORDER BY name",
        [':t'=>$tenant_id, ':c'=>$clinic_id]
    );
    echo json_encode($rows);
    exit;
}

// ── ACTION: calendar ──────────────────────────────────────────────────────
// Returns per-day availability for a month as {"YYYY-MM-DD": true/false}
if ($action === 'calendar') {
    $doctor_id = clean_uuid($_GET['doctor_id'] ?? '');
    $year      = (int)($_GET['year']  ?? date('Y'));
    $month     = (int)($_GET['month'] ?? date('n'));

    if (!$doctor_id || $month < 1 || $month > 12) { echo json_encode([]); exit; }

    $days_in_month = (int)date('t', mktime(0,0,0,$month,1,$year));
    $today         = date('Y-m-d');
    $result        = [];

    for ($d = 1; $d <= $days_in_month; $d++) {
        $ds = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $result[$ds] = ($ds >= $today) && !empty(available_slots($tenant_id, $doctor_id, $ds));
    }
    echo json_encode($result);
    exit;
}

// ── ACTION: stats (admin only) ────────────────────────────────────────────
if ($action === 'stats') {
    if (!Auth::check()) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

    $date = preg_replace('/[^0-9\-]/', '', (string)($_GET['date'] ?? date('Y-m-d')));
    $rows = Database::all(
        "SELECT status, COUNT(*) AS cnt FROM appointments WHERE tenant_id=:t AND appointment_date=:d GROUP BY status",
        [':t'=>$tenant_id, ':d'=>$date]
    );
    $out = [];
    foreach ($rows as $r) { $out[$r['status']] = (int)$r['cnt']; }
    echo json_encode($out);
    exit;
}

echo json_encode(['error' => 'Unknown action.']);
