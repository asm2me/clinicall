import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Website Builder",
  description: "Clinic website builder scaffold."
};

export default function BuilderPage() {
  return (
    <section className="hero-card">
      <p className="eyebrow">Builder workspace</p>
      <h1>Drag-and-drop website builder</h1>
      <p className="lead">
        This workspace will power tenant website editing, template selection, and content publishing.
      </p>
    </section>
  );
}
