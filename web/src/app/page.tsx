import { ClinicLayout } from '@/components/layout/ClinicLayout';
import { SectionCard } from '@/components/ui/SectionCard';
import { getTenantPublicData } from '@/lib/api';

const tenantSlug = process.env.NEXT_PUBLIC_DEFAULT_TENANT_SLUG || 'clinic-demo';

export default async function HomePage() {
  const { tenant, navigation, services, doctors } = await getTenantPublicData(tenantSlug);

  return (
    <ClinicLayout tenant={tenant} navigation={navigation}>
      <div className="page-grid">
        <SectionCard title="Welcome" eyebrow="Public clinic website">
          <p>{tenant.description || 'Explore services, doctors, and self-service booking.'}</p>
        </SectionCard>
        <SectionCard title="Featured services" eyebrow="Service catalog">
          <ul>
            {services.map((service) => (
              <li key={service.id}>
                <strong>{service.name}</strong>
                <span>{service.price || 'Contact us for pricing'}</span>
              </li>
            ))}
          </ul>
        </SectionCard>
        <SectionCard title="Our doctors" eyebrow="Care team">
          <ul>
            {doctors.map((doctor) => (
              <li key={doctor.id}>
                <strong>{doctor.name}</strong>
                <span>{doctor.specialty}</span>
              </li>
            ))}
          </ul>
        </SectionCard>
      </div>
    </ClinicLayout>
  );
}