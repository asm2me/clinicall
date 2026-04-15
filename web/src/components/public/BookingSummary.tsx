import type { ClinicService, DoctorProfile } from '@/lib/types';

type BookingSummaryProps = {
  service?: ClinicService;
  doctor?: DoctorProfile;
  appointmentDate?: string;
};

export function BookingSummary({ service, doctor, appointmentDate }: BookingSummaryProps) {
  return (
    <aside className="section-card">
      <h2>Booking summary</h2>
      <ul className="summary-list">
        <li><strong>Service:</strong> {service?.name || 'Select a service'}</li>
        <li><strong>Provider:</strong> {doctor?.name || 'Optional'}</li>
        <li><strong>When:</strong> {appointmentDate || 'Choose a date and time'}</li>
      </ul>
    </aside>
  );
}