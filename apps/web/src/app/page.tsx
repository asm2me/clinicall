import Link from "next/link";
import { type Metadata } from "next";

export const metadata: Metadata = {
  title: "Home",
  description: "Clinicall public website, admin dashboard, and booking platform foundation."
};

const clinicRoutes = [
  { href: "/clinic/demo-clinic", label: "Demo Clinic Public Site" },
  { href: "/builder", label: "Website Builder" },
  { href: "/admin", label: "Admin Dashboard" }
];

export default function HomePage() {
  return (
    <section className="page-grid">
      <div className="hero-card">
        <p className="eyebrow">SaaS clinic platform</p>
        <h1>Multi-tenant clinics, website builder, and booking experiences in one platform.</h1>
        <p className="lead">
          Clinicall is structured for public clinic websites, admin workflows, and cross-platform patient journeys.
        </p>
        <div className="button-row">
          {clinicRoutes.map((route) => (
            <Link key={route.href} className="button" href={route.href}>
              {route.label}
            </Link>
          ))}
        </div>
      </div>
      <aside className="info-panel">
        <h2>Phase 1 scaffold</h2>
        <ul>
          <li>Next.js app router foundation</li>
          <li>Shared TypeScript contracts via `@shared/*`</li>
          <li>Tenant-aware route structure</li>
        </ul>
      </aside>
    </section>
  );
}
