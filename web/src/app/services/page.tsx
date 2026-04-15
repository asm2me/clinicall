import { ClinicLayout } from '@/components/layout/ClinicLayout';
import { SectionCard } from '@/components/ui/SectionCard';
import { getTenantPublicData } from '@/lib/api';

const tenantSlug = process.env.NEXT_PUBLIC_DEFAULT_TENANT_SLUG || 'clinic-demo';

export default async function ServicesPage() {
  const { tenant, navigation, services } = await getTenantPublicData(tenantSlug);

  return (
    <ClinicLayout tenant={tenant} navigation={navigation}>
      <SectionCard title="Services" eyebrow="Public clinic website">
        <ul>
          {services.map((service) => (
            <li key={service.id}>
              <strong>{service.name}</strong>
              <p>{service.description}</p>
            </li>
          ))}
        </ul>
      </SectionCard>
    </ClinicLayout>
  );
}