import React from 'react';
import { Screen } from '../../src/components/Screen';
import { DashboardSummary } from '../../src/features/dashboard/DashboardSummary';

export default function PatientDashboard() {
  return (
    <Screen scroll>
      <DashboardSummary role="patient" />
    </Screen>
  );
}