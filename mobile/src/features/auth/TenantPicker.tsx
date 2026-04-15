import React from 'react';
import { useRouter } from 'expo-router';
import { PrimaryButton } from '../../components/PrimaryButton';
import { Card, Text, View } from '../../components/primitives';

export function TenantPicker() {
  const router = useRouter();

  return (
    <View style={{ gap: 16 }}>
      <View style={{ gap: 8 }}>
        <Text variant="title">Choose your clinic</Text>
        <Text variant="body">
          Select a tenant to continue. This is a placeholder for the production tenant resolution flow.
        </Text>
      </View>

      <Card>
        <Text variant="label">Demo Clinic</Text>
        <Text variant="caption">demo-clinic</Text>
        <PrimaryButton label="Continue" onPress={() => router.back()} />
      </Card>
    </View>
  );
}