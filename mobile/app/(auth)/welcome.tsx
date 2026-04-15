import { Link } from 'expo-router';
import React from 'react';
import { Screen } from '../../src/components/Screen';
import { PrimaryButton } from '../../src/components/PrimaryButton';
import { Text, View } from '../../src/components/primitives';

export default function WelcomeScreen() {
  return (
    <Screen scroll>
      <View style={{ gap: 24 }}>
        <View style={{ gap: 8 }}>
          <Text variant="eyebrow">ClinicAll Mobile</Text>
          <Text variant="title">Multi-tenant clinic care in your pocket</Text>
          <Text variant="body">
            Secure access for patients, doctors, clinic admins, and super admins across Android, iOS, web, and desktop wrappers.
          </Text>
        </View>

        <View style={{ gap: 12 }}>
          <Link href="/(auth)/sign-in" asChild>
            <PrimaryButton label="Sign in" />
          </Link>
          <Link href="/(auth)/sign-up" asChild>
            <PrimaryButton label="Create account" tone="secondary" />
          </Link>
        </View>
      </View>
    </Screen>
  );
}