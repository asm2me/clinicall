export type Role = 'patient' | 'doctor' | 'clinic_admin' | 'super_admin' | 'receptionist';

export type Session = {
  token: string;
  userId: string;
  role: Role;
  tenantId?: string;
  tenantSlug?: string;
  displayName: string;
};

export type ApiTenant = {
  id: string;
  slug: string;
  name: string;
  branding?: {
    primaryColor?: string;
    logoUrl?: string;
  };
};

export type AppointmentSummary = {
  id: string;
  clinicName: string;
  providerName: string;
  serviceName: string;
  startsAt: string;
  status: 'requested' | 'confirmed' | 'completed' | 'cancelled';
};

export type ScheduleSlot = {
  id: string;
  startsAt: string;
  endsAt: string;
  providerName: string;
  status: 'available' | 'booked' | 'blocked';
};

export type NotificationItem = {
  id: string;
  title: string;
  body: string;
  createdAt: string;
  read: boolean;
};