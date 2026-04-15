import React from 'react';
import { Screen } from '../../src/components/Screen';
import { BookingFlow } from '../../src/features/booking/BookingFlow';

export default function PatientBook() {
  return (
    <Screen scroll>
      <BookingFlow />
    </Screen>
  );
}