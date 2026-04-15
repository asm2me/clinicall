import type { ReactNode } from 'react';
import './globals.css';

export const metadata = {
  title: 'ClinicAll',
  description: 'Tenant-aware clinic SaaS frontend'
};

type RootLayoutProps = {
  children: ReactNode;
};

export default function RootLayout({ children }: RootLayoutProps) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}