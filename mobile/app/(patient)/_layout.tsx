import { Stack } from 'expo-router';
import React from 'react';

export default function PatientLayout() {
  return (
    <Stack>
      <Stack.Screen name="dashboard" options={{ title: 'Patient Dashboard' }} />
      <Stack.Screen name="appointments" options={{ title: 'Appointments' }} />
      <Stack.Screen name="book" options={{ title: 'Book Appointment' }} />
      <Stack.Screen name="notifications" options={{ title: 'Notifications' }} />
    </Stack>
  );
}