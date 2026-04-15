import { Redirect } from 'expo-router';
import React from 'react';
import { useAuth } from '../src/context/AuthContext';

export default function IndexRoute() {
  const { session } = useAuth();

  if (!session) {
    return <Redirect href="/(auth)/welcome" />;
  }

  if (session.role === 'doctor') {
    return <Redirect href="/(doctor)/dashboard" />;
  }

  if (session.role === 'clinic_admin' || session.role === 'super_admin') {
    return <Redirect href="/(admin)/dashboard" />;
  }

  return <Redirect href="/(patient)/dashboard" />;
}