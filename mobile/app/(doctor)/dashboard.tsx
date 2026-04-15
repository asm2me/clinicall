import React from 'react';
import { Screen } from '../../src/components/Screen';
import { DashboardSummary } from '../../src/features/dashboard/DashboardSummary';

export default function DoctorDashboard() {
  return (
    <Screen scroll>
      <DashboardSummary role="doctor" />
    </Screen>
  );
}