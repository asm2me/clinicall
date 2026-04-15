export type TenantId = string;

export interface Tenant {
  id: TenantId;
  name: string;
  slug: string;
  defaultDomain?: string;
  customDomain?: string;
}

export interface Domain {
  id: string;
  tenantId: TenantId;
  hostname: string;
  isPrimary: boolean;
  isVerified: boolean;
}

export interface Plan {
  id: string;
  key: "basic" | "pro" | "enterprise";
  name: string;
  limits: {
    clinics: number;
    users: number;
    branches: number;
    storageMb: number;
  };
}

export interface FeatureFlag {
  key: string;
  enabled: boolean;
  tenantId?: TenantId;
}

export interface ApiEnvelope<T> {
  data: T;
  meta?: Record<string, unknown>;
}

export interface WebsitePageBlock {
  type: "hero" | "about" | "doctors" | "services" | "booking" | "contact";
  props: Record<string, unknown>;
}

export interface WebsitePage {
  id: string;
  tenantId: TenantId;
  slug: string;
  title: string;
  jsonContent: WebsitePageBlock[];
  isPublished: boolean;
  seo?: {
    title?: string;
    description?: string;
  };
}
