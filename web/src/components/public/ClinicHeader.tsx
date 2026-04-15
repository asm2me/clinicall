import type { NavigationItem, TenantBranding } from '@/lib/types';

type ClinicHeaderProps = {
  tenant: TenantBranding;
  navigation: NavigationItem[];
};

export function ClinicHeader({ tenant, navigation }: ClinicHeaderProps) {
  return (
    <header className="public-hero">
      <div className="public-hero-copy">
        <span className="section-eyebrow">Tenant aware public website</span>
        <h1>{tenant.name}</h1>
        <p>{tenant.description || 'Book visits, meet our doctors, and manage your care online.'}</p>
      </div>
      <nav aria-label="Public clinic navigation">
        <ul className="public-nav">
          {navigation.map((item) => (
            <li key={item.href}>
              <a href={item.href}>{item.label}</a>
            </li>
          ))}
        </ul>
      </nav>
    </header>
  );
}