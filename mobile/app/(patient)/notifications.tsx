import React from 'react';
import { Screen } from '../../src/components/Screen';
import { NotificationsPanel } from '../../src/features/notifications/NotificationsPanel';

export default function PatientNotifications() {
  return (
    <Screen scroll>
      <NotificationsPanel />
    </Screen>
  );
}