import React from 'react';
import { Screen } from '../../src/components/Screen';
import { ScheduleView } from '../../src/features/schedule/ScheduleView';

export default function DoctorSchedule() {
  return (
    <Screen scroll>
      <ScheduleView />
    </Screen>
  );
}