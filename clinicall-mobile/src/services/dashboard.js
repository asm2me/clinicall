import { buildApiUrl } from '../config/api';

function normalizeString(value) {
  return typeof value === 'string' ? value.trim() : '';
}

function normalizeStat(stat, index) {
  if (!stat || typeof stat !== 'object') {
    return null;
  }

  const key = normalizeString(stat.key ?? stat.id ?? stat.label) || `stat-${index}`;

  return {
    key,
    label: normalizeString(stat.label) || key,
    value:
      typeof stat.value === 'number' || typeof stat.value === 'string'
        ? stat.value
        : '',
    icon: normalizeString(stat.icon) || undefined,
    change: normalizeString(stat.change) || undefined,
    trend:
      stat.trend === 'up' || stat.trend === 'down' || stat.trend === 'neutral'
        ? stat.trend
        : undefined,
    color: normalizeString(stat.color) || undefined,
  };
}

function normalizeAppointment(appointment, index) {
  if (!appointment || typeof appointment !== 'object') {
    return null;
  }

  const id = normalizeString(appointment.id ?? appointment._id) || `appointment-${index}`;

  return {
    id,
    patientName: normalizeString(appointment.patientName ?? appointment.patient_name) || 'Unknown patient',
    time: normalizeString(appointment.time) || undefined,
    status: normalizeString(appointment.status) || undefined,
    type: normalizeString(appointment.type) || undefined,
    doctorName: normalizeString(appointment.doctorName ?? appointment.doctor_name) || undefined,
    location: normalizeString(appointment.location) || undefined,
  };
}

export function normalizeDashboardSummary(payload) {
  if (!payload || typeof payload !== 'object') {
    return null;
  }

  const statsSource = Array.isArray(payload.stats)
    ? payload.stats
    : Array.isArray(payload.summary?.stats)
      ? payload.summary.stats
      : [];
  const appointmentsSource = Array.isArray(payload.appointments)
    ? payload.appointments
    : Array.isArray(payload.summary?.appointments)
      ? payload.summary.appointments
      : [];

  const stats = statsSource
    .map(normalizeStat)
    .filter(Boolean);
  const appointments = appointmentsSource
    .map(normalizeAppointment)
    .filter(Boolean);

  const totalsSource = payload.totals && typeof payload.totals === 'object'
    ? payload.totals
    : payload.summary && typeof payload.summary === 'object' && payload.summary.totals && typeof payload.summary.totals === 'object'
      ? payload.summary.totals
      : undefined;

  const metaSource = payload.meta && typeof payload.meta === 'object'
    ? payload.meta
    : payload.summary && typeof payload.summary === 'object' && payload.summary.meta && typeof payload.summary.meta === 'object'
      ? payload.summary.meta
      : undefined;

  return {
    stats,
    appointments,
    totals: totalsSource
      ? {
          patients: typeof totalsSource.patients === 'number' ? totalsSource.patients : undefined,
          upcomingAppointments:
            typeof totalsSource.upcomingAppointments === 'number'
              ? totalsSource.upcomingAppointments
              : typeof totalsSource.upcoming_appointments === 'number'
                ? totalsSource.upcoming_appointments
                : undefined,
          pendingTasks:
            typeof totalsSource.pendingTasks === 'number'
              ? totalsSource.pendingTasks
              : typeof totalsSource.pending_tasks === 'number'
                ? totalsSource.pending_tasks
                : undefined,
          revenue: typeof totalsSource.revenue === 'number' ? totalsSource.revenue : undefined,
        }
      : undefined,
    meta: metaSource
      ? {
          generatedAt: normalizeString(metaSource.generatedAt ?? metaSource.generated_at) || undefined,
          tenantId: normalizeString(metaSource.tenantId ?? metaSource.tenant_id) || undefined,
        }
      : undefined,
  };
}

async function readJsonResponse(response) {
  const text = await response.text();
  if (!text) {
    return null;
  }

  try {
    return JSON.parse(text);
  } catch {
    return null;
  }
}

function extractErrorMessage(payload, fallback) {
  if (!payload || typeof payload !== 'object') {
    return fallback;
  }

  if (typeof payload.message === 'string' && payload.message.trim()) {
    return payload.message.trim();
  }

  if (typeof payload.error === 'string' && payload.error.trim()) {
    return payload.error.trim();
  }

  if (payload.error && typeof payload.error === 'object') {
    return extractErrorMessage(payload.error, fallback);
  }

  return fallback;
}

export function isDashboardError(error) {
  return Boolean(error && typeof error === 'object' && error.name === 'DashboardError');
}

export function getDashboardErrorMessage(error) {
  if (error && typeof error === 'object' && typeof error.message === 'string' && error.message.trim()) {
    return error.message.trim();
  }

  return 'Unable to load dashboard summary.';
}

export async function getDashboardSummary(options = {}) {
  const token = normalizeString(options.token);
  const tenantId = normalizeString(options.tenantId);

  try {
    const response = await fetch(buildApiUrl('/api/dashboard/summary'), {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(tenantId ? { 'X-Tenant-Id': tenantId } : {}),
      },
    });

    const payload = await readJsonResponse(response);

    if (!response.ok) {
      return {
        ok: false,
        error: {
          code: payload && typeof payload === 'object' && typeof payload.code === 'string'
            ? payload.code
            : 'dashboard_request_failed',
          message: extractErrorMessage(payload, 'Unable to load dashboard summary.'),
          status: response.status,
        },
      };
    }

    const summary = normalizeDashboardSummary(payload);

    if (!summary) {
      const error = new Error('Received an invalid dashboard summary response from the server.');
      error.name = 'DashboardError';
      error.status = 500;
      error.code = 'invalid_dashboard_response';
      throw error;
    }

    return {
      ok: true,
      summary: {
        ...summary,
        raw: payload,
      },
    };
  } catch (error) {
    if (isDashboardError(error)) {
      return {
        ok: false,
        error: {
          code: error.code || 'dashboard_request_failed',
          message: getDashboardErrorMessage(error),
          status: error.status,
        },
      };
    }

    return {
      ok: false,
      error: {
        code: 'network_error',
        message: 'Unable to load dashboard summary right now. Please try again.',
      },
    };
  }
}