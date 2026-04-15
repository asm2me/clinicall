import type { Metadata } from "next";
import Link from "next/link";
import type { ReactNode } from "react";
import "./globals.css";

export const metadata: Metadata = {
  title: {
    default: "Clinicall",
    template: "%s | Clinicall"
  },
  description: "Clinicall is a multi-tenant clinic platform for marketing sites, patient journeys, and admin operations."
};

export default function RootLayout({
  children
}: Readonly<{
  children: ReactNode;
}>) {
  return (
    <html lang="en">
      <body>
        <div className="app-shell">
          <header className="app-header">
            <div className="app-header__brand">
              <Link href="/">Clinicall</Link>
            </div>
            <nav className="app-header__nav" aria-label="Primary">
              <Link href="/">Home</Link>
              <Link href="/admin">Admin</Link>
              <Link href="/builder">Builder</Link>
              <Link href="/clinic/demo-clinic">Clinic Preview</Link>
            </nav>
          </header>
          <main className="app-main">{children}</main>
        </div>
      </body>
    </html>
  );
}
