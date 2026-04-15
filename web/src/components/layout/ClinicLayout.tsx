import type { ReactNode } from 'react';
import type { NavigationItem, TenantBranding } from '@/lib/types';

type ClinicLayoutProps = {
  tenant: TenantBranding;
  navigation: NavigationItem[];
  children: ReactNode;
};

export function ClinicLayout({ tenant, navigation, children }: ClinicLayoutProps) {
  return (
    <div className="clinic-shell">
      <header className="clinic-header">
        <div>
          <div className="clinic-brand">{tenant.name}</div>
          <div className="clinic-subtitle">{tenant.description || 'Trusted care, built around you.'}</div>
        </div>
        <nav aria-label="Primary">
          <ul className="clinic-nav">
            {navigation.map((item) => (
              <li key={item.href}>
                <a href={item.href} target={item.external ? '_blank' : undefined} rel={item.external ? 'noreferrer' : undefined}>
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
        </nav>
      </header>
      <main>{children}</main>
      <footer className="clinic-footer">
        <span>{tenant.name}</span>
        <span>Powered by ClinicAll</span>
      </footer>
    </div>
  );
}