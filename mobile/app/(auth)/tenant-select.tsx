import React from 'react';
import { Screen } from '../../src/components/Screen';
import { TenantPicker } from '../../src/features/auth/TenantPicker';

export default function TenantSelectScreen() {
  return (
    <Screen scroll>
      <TenantPicker />
    </Screen>
  );
}