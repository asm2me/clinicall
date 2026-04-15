import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Admin Dashboard",
  description: "Tenant-aware operations dashboard scaffold."
};

export default function AdminPage() {
  return (
    <section className="hero-card">
      <p className="eyebrow">Admin workspace</p>
      <h1>Clinic operations dashboard</h1>
      <p className="lead">
        This route is reserved for clinic administrators, doctors, and reception workflows in the production build.
      </p>
    </section>
  );
}
