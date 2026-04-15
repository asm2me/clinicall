import type { ApiTenant, AppointmentSummary, NotificationItem, ScheduleSlot, Session } from '../../src/types';

const DEFAULT_BASE_URL = 'https://api.clinicall.local/api/v1';

type ApiRequestOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  token?: string | null;
  body?: unknown;
};

async function request<T>(path: string, options: ApiRequestOptions = {}): Promise<T> {
  const response = await fetch(`${DEFAULT_BASE_URL}${path}`, {
    method: options.method ?? 'GET',
    headers: {
      'Content-Type': 'application/json',
      ...(options.token ? { Authorization: `Bearer ${options.token}` } : {})
    },
    body: options.body ? JSON.stringify(options.body) : undefined
  });

  if (!response.ok) {
    throw new Error(`API request failed: ${response.status}`);
  }

  return (await response.json()) as T;
}

export const apiClient = {
  auth: {
    signIn: (email: string, password: string) =>
      request<{ session: Session }>('/auth/sign-in', {
        method: 'POST',
        body: { email, password }
      }),
    signUp: (payload: { name: string; email: string; password: string; role: Session['role'] }) =>
      request<{ session: Session }>('/auth/sign-up', {
        method: 'POST',
        body: payload
      }),
    forgotPassword: (email: string) =>
      request<{ message: string }>('/auth/forgot-password', {
        method: 'POST',
        body: { email }
      }),
    getTenants: () => request<{ tenants: ApiTenant[] }>('/public/tenants')
  },
  patient: {
    getAppointments: (token: string) => request<{ appointments: AppointmentSummary[] }>('/patient/appointments', { token }),
    createBooking: (token: string, payload: { serviceId: string; providerId?: string; startsAt: string; notes?: string }) =>
      request<{ appointment: AppointmentSummary }>('/patient/bookings', {
        method: 'POST',
        token,
        body: payload
      })
  },
  doctor: {
    getSchedule: (token: string) => request<{ slots: ScheduleSlot[] }>('/doctor/schedule', { token })
  },
  notifications: {
    list: (token: string) => request<{ notifications: NotificationItem[] }>('/notifications', { token })
  }
};