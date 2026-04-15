import { Stack } from 'expo-router';
import React from 'react';

export default function DoctorLayout() {
  return (
    <Stack>
      <Stack.Screen name="dashboard" options={{ title: 'Doctor Dashboard' }} />
      <Stack.Screen name="schedule" options={{ title: 'Schedule' }} />
      <Stack.Screen name="notifications" options={{ title: 'Notifications' }} />
    </Stack>
  );
}