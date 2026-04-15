import React from 'react';
import { Screen } from '../../src/components/Screen';
import { AppointmentList } from '../../src/features/appointments/AppointmentList';

export default function PatientAppointments() {
  return (
    <Screen scroll>
      <AppointmentList scope="patient" />
    </Screen>
  );
}