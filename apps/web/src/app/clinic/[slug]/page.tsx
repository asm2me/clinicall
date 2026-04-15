import type { Metadata } from "next";

type ClinicPageProps = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: ClinicPageProps): Promise<Metadata> {
  const { slug } = await params;

  return {
    title: `${slug} Clinic`,
    description: `Public website preview for ${slug}.`
  };
}

export default async function ClinicPage({ params }: ClinicPageProps) {
  const { slug } = await params;

  return (
    <section className="hero-card">
      <p className="eyebrow">Public clinic site</p>
      <h1>{slug}</h1>
      <p className="lead">
        This dynamic route will render tenant-specific pages from stored JSON website blocks.
      </p>
    </section>
  );
}
