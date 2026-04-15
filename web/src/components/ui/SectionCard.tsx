import type { ReactNode } from 'react';

type SectionCardProps = {
  title: string;
  eyebrow?: string;
  children: ReactNode;
};

export function SectionCard({ title, eyebrow, children }: SectionCardProps) {
  return (
    <section className="section-card">
      {eyebrow ? <p className="section-eyebrow">{eyebrow}</p> : null}
      <h2>{title}</h2>
      <div>{children}</div>
    </section>
  );
}