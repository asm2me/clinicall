import type { ReactNode } from 'react';

type BuilderLayoutProps = {
  pageTitle: string;
  children: ReactNode;
};

export function BuilderLayout({ pageTitle, children }: BuilderLayoutProps) {
  return (
    <div className="builder-shell">
      <aside className="builder-panel">
        <div className="admin-kicker">Website builder</div>
        <h1>{pageTitle}</h1>
        <p>Compose tenant pages with reusable sections and preview changes before publishing.</p>
      </aside>
      <section className="builder-canvas">{children}</section>
    </div>
  );
}