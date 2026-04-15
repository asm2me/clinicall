import { ClinicLayout } from '@/components/layout/ClinicLayout';
import { SectionCard } from '@/components/ui/SectionCard';
import { getTenantPublicData } from '@/lib/api';

const tenantSlug = process.env.NEXT_PUBLIC_DEFAULT_TENANT_SLUG || 'clinic-demo';

export default async function AboutPage() {
  const { tenant, navigation } = await getTenantPublicData(tenantSlug);

  return (
    <ClinicLayout tenant={tenant} navigation={navigation}>
      <SectionCard title="About our clinic" eyebrow="Public site">
        <p>
          ClinicAll powers tenant-aware clinic websites with branded pages, service listings, and booking flows.
        </p>
      </SectionCard>
    </ClinicLayout>
  );
}