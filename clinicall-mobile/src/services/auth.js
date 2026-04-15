import { buildApiUrl } from '../config/api';

function normalizeString(value) {
  return typeof value === 'string' ? value.trim() : '';
}

function normalizeUser(user) {
  if (!user || typeof user !== 'object') {
    return null;
  }

  const id = normalizeString(user.id ?? user._id);
  if (!id) {
    return null;
  }

  return {
    id,
    name: normalizeString(user.name) || undefined,
    email: normalizeString(user.email) || undefined,
    role: normalizeString(user.role) || undefined,
    tenantId: normalizeString(user.tenantId ?? user.tenant_id) || undefined,
    doctorId: normalizeString(user.doctorId ?? user.doctor_id) || undefined,
  };
}

function normalizeTenant(tenant) {
  if (!tenant || typeof tenant !== 'object') {
    return undefined;
  }

  const id = normalizeString(tenant.id ?? tenant._id);
  if (!id) {
    return undefined;
  }

  return {
    id,
    name: normalizeString(tenant.name) || undefined,
    slug: normalizeString(tenant.slug) || undefined,
  };
}

function normalizeLockout(lockout) {
  if (!lockout || typeof lockout !== 'object') {
    return undefined;
  }

  const remainingAttempts =
    typeof lockout.remainingAttempts === 'number'
      ? lockout.remainingAttempts
      : typeof lockout.remaining_attempts === 'number'
        ? lockout.remaining_attempts
        : undefined;

  const lockedUntil = normalizeString(lockout.lockedUntil ?? lockout.locked_until) || undefined;

  if (remainingAttempts === undefined && !lockedUntil) {
    return undefined;
  }

  return {
    remainingAttempts,
    lockedUntil,
  };
}

export function normalizeAuthSession(payload) {
  if (!payload || typeof payload !== 'object') {
    return null;
  }

  const token = normalizeString(payload.token ?? payload.accessToken ?? payload.access_token);
  const user = normalizeUser(payload.user);

  if (!token || !user) {
    return null;
  }

  const refreshToken =
    normalizeString(payload.refreshToken ?? payload.refresh_token) || undefined;
  const expiresAt = payload.expiresAt ?? payload.expires_at;
  const permissions = Array.isArray(payload.permissions)
    ? payload.permissions.filter((item) => typeof item === 'string' && item.trim())
    : undefined;
  const tenant = normalizeTenant(payload.tenant);
  const lockout = normalizeLockout(payload.lockout);

  return {
    user,
    token,
    refreshToken,
    expiresAt: typeof expiresAt === 'string' || typeof expiresAt === 'number' ? expiresAt : undefined,
    permissions,
    tenant,
    lockout,
  };
}

function createAuthError(status, code, message, extra = {}) {
  const error = new Error(message);
  error.name = 'AuthError';
  error.status = status;
  error.code = code;
  Object.assign(error, extra);
  return error;
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

export function isAuthError(error) {
  return Boolean(error && typeof error === 'object' && error.name === 'AuthError');
}

export function getAuthErrorMessage(error) {
  if (error && typeof error === 'object' && typeof error.message === 'string' && error.message.trim()) {
    return error.message.trim();
  }

  return 'Authentication failed.';
}

async function performAuthRequest(path, options = {}) {
  const response = await fetch(buildApiUrl(path), {
    credentials: 'include',
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
  });

  const payload = await readJsonResponse(response);

  return {
    response,
    payload,
  };
}

export async function login(credentials) {
  const email = normalizeString(credentials && credentials.email);
  const password = normalizeString(credentials && credentials.password);

  if (!email || !password) {
    return {
      ok: false,
      error: {
        code: 'invalid_credentials',
        message: 'Email and password are required.',
      },
    };
  }

  try {
    const { response, payload } = await performAuthRequest('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      const errorCode =
        payload && typeof payload === 'object' && typeof payload.code === 'string'
          ? payload.code
          : response.status === 401
            ? 'invalid_credentials'
            : response.status === 423
              ? 'account_locked'
              : 'auth_request_failed';

      const error = {
        code: errorCode,
        message: extractErrorMessage(
          payload,
          response.status === 401
            ? 'Invalid email or password.'
            : response.status === 423
              ? 'Account is locked.'
              : 'Unable to sign in.'
        ),
        status: response.status,
      };

      if (payload && typeof payload === 'object') {
        if (typeof payload.remainingAttempts === 'number') {
          error.remainingAttempts = payload.remainingAttempts;
        } else if (payload.lockout && typeof payload.lockout.remainingAttempts === 'number') {
          error.remainingAttempts = payload.lockout.remainingAttempts;
        }

        if (typeof payload.lockedUntil === 'string') {
          error.lockedUntil = payload.lockedUntil;
        } else if (payload.lockout && typeof payload.lockout.lockedUntil === 'string') {
          error.lockedUntil = payload.lockout.lockedUntil;
        }
      }

      return {
        ok: false,
        error,
      };
    }

    const session = normalizeAuthSession(payload);

    if (!session) {
      throw createAuthError(500, 'invalid_auth_response', 'Received an invalid login response from the server.');
    }

    return {
      ok: true,
      ...session,
      raw: payload,
    };
  } catch (error) {
    if (isAuthError(error)) {
      return {
        ok: false,
        error: {
          code: error.code || 'auth_request_failed',
          message: getAuthErrorMessage(error),
          status: error.status,
        },
      };
    }

    return {
      ok: false,
      error: {
        code: 'network_error',
        message: 'Unable to sign in right now. Please try again.',
      },
    };
  }
}

export async function getSession() {
  try {
    const { response, payload } = await performAuthRequest('/api/auth/me', {
      method: 'GET',
    });

    if (!response.ok) {
      return {
        ok: false,
        error: {
          code: response.status === 401 ? 'unauthorized' : 'session_request_failed',
          message: extractErrorMessage(payload, 'Unable to restore session.'),
          status: response.status,
        },
      };
    }

    const session = normalizeAuthSession(payload);

    if (!session) {
      throw createAuthError(500, 'invalid_session_response', 'Received an invalid session response from the server.');
    }

    return {
      ok: true,
      ...session,
      raw: payload,
    };
  } catch (error) {
    return {
      ok: false,
      error: {
        code: isAuthError(error) ? error.code || 'session_request_failed' : 'network_error',
        message: isAuthError(error) ? getAuthErrorMessage(error) : 'Unable to restore session.',
        status: isAuthError(error) ? error.status : undefined,
      },
    };
  }
}

export async function logout() {
  try {
    await performAuthRequest('/api/auth/logout', {
      method: 'POST',
      body: JSON.stringify({}),
    });

    return {
      ok: true,
    };
  } catch {
    return {
      ok: false,
      error: {
        code: 'network_error',
        message: 'Unable to sign out cleanly.',
      },
    };
  }
}
