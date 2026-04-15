import type { BookingStep } from '@/lib/types';

const labels: Record<BookingStep, string> = {
  service: 'Service',
  provider: 'Provider',
  datetime: 'Date & time',
  details: 'Your details',
  review: 'Review',
  done: 'Confirmation'
};

type BookingStepperProps = {
  currentStep: BookingStep;
};

export function BookingStepper({ currentStep }: BookingStepperProps) {
  const steps = Object.keys(labels) as BookingStep[];
  const currentIndex = steps.indexOf(currentStep);

  return (
    <ol className="booking-stepper">
      {steps.map((step, index) => (
        <li key={step} aria-current={step === currentStep ? 'step' : undefined}>
          <span className={index <= currentIndex ? 'booking-step-active' : 'booking-step-idle'}>{labels[step]}</span>
        </li>
      ))}
    </ol>
  );
}