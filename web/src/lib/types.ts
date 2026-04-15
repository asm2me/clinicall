export type Role = 'super_admin' | 'clinic_admin' | 'doctor' | 'receptionist' | 'patient';

export type TenantBranding = {
  name: string;
  slug: string;
  primaryColor: string;
  secondaryColor: string;
  logoUrl?: string | null;
  domain?: string | null;
  description?: string | null;
};

export type NavigationItem = {
  label: string;
  href: string;
  external?: boolean;
};

export type ClinicService = {
  id: string;
  name: string;
  slug: string;
  price?: string | null;
  durationMinutes?: number;
  description?: string | null;
};

export type DoctorProfile = {
  id: string;
  name: string;
  title: string;
  specialty: string;
  avatarUrl?: string | null;
  bio?: string | null;
};

export type WebsitePage = {
  id: string;
  slug: string;
  title: string;
  status: 'draft' | 'published';
  updatedAt: string;
};

export type BookingStep = 'service' | 'provider' | 'datetime' | 'details' | 'review' | 'done';

export type ApiEnvelope<T> = {
  data: T;
  meta?: {
    requestId?: string;
    tenantSlug?: string | null;
  };
};

export type TenantPublicPayload = {
  tenant: TenantBranding;
  navigation: NavigationItem[];
  services: ClinicService[];
  doctors: DoctorProfile[];
  pages: WebsitePage[];
};