import type {
  ApiEnvelope,
  ClinicService,
  DoctorProfile,
  TenantPublicPayload,
  WebsitePage
} from './types';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api/v1';

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...init,
    headers: {
      'Content-Type': 'application/json',
      ...(init?.headers || {})
    },
    cache: 'no-store'
  });

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`);
  }

  return response.json() as Promise<T>;
}

export async function getTenantPublicData(slug: string): Promise<TenantPublicPayload> {
  const payload = await request<ApiEnvelope<TenantPublicPayload>>(`/public/tenants/${encodeURIComponent(slug)}`);
  return payload.data;
}

export async function getPublicPage(slug: string): Promise<WebsitePage | null> {
  try {
    const payload = await request<ApiEnvelope<WebsitePage>>(`/public/pages/${encodeURIComponent(slug)}`);
    return payload.data;
  } catch {
    return null;
  }
}

export async function getServices(slug: string): Promise<ClinicService[]> {
  const payload = await request<ApiEnvelope<ClinicService[]>>(`/public/tenants/${encodeURIComponent(slug)}/services`);
  return payload.data;
}

export async function getDoctors(slug: string): Promise<DoctorProfile[]> {
  const payload = await request<ApiEnvelope<DoctorProfile[]>>(`/public/tenants/${encodeURIComponent(slug)}/doctors`);
  return payload.data;
}

export async function submitBooking(slug: string, body: Record<string, unknown>): Promise<{ bookingId: string; status: string }> {
  const payload = await request<ApiEnvelope<{ bookingId: string; status: string }>>(`/public/bookings`, {
    method: 'POST',
    body: JSON.stringify({ tenantSlug: slug, ...body })
  });
  return payload.data;
}