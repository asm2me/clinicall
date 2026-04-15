import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import React from 'react';
import { AuthProvider } from '../src/context/AuthContext';

export default function RootLayout() {
  return (
    <AuthProvider>
      <StatusBar style="dark" />
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="index" />
        <Stack.Screen name="(auth)" />
        <Stack.Screen name="(patient)" />
        <Stack.Screen name="(doctor)" />
        <Stack.Screen name="(admin)" />
        <Stack.Screen name="(shared)" />
      </Stack>
    </AuthProvider>
  );
}