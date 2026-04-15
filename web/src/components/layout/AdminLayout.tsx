import type { ReactNode } from 'react';

type AdminLayoutProps = {
  title: string;
  description: string;
  children: ReactNode;
};

const adminNav = [
  { label: 'Dashboard', href: '/app' },
  { label: 'Appointments', href: '/app/appointments' },
  { label: 'Patients', href: '/app/patients' },
  { label: 'Doctors', href: '/app/doctors' },
  { label: 'Services', href: '/app/services' },
  { label: 'Website', href: '/app/website/pages' },
  { label: 'RBAC', href: '/app/rbac/roles' },
  { label: 'Settings', href: '/app/settings' }
];

export function AdminLayout({ title, description, children }: AdminLayoutProps) {
  return (
    <div className="admin-shell">
      <aside className="admin-sidebar">
        <div>
          <div className="admin-kicker">Clinic dashboard</div>
          <h1>{title}</h1>
          <p>{description}</p>
        </div>
        <nav aria-label="Admin">
          <ul className="admin-nav">
            {adminNav.map((item) => (
              <li key={item.href}>
                <a href={item.href}>{item.label}</a>
              </li>
            ))}
          </ul>
        </nav>
      </aside>
      <section className="admin-content">{children}</section>
    </div>
  );
}