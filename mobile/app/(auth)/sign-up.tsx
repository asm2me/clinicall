import React from 'react';
import { Screen } from '../../src/components/Screen';
import { AuthForm } from '../../src/features/auth/AuthForm';

export default function SignUpScreen() {
  return (
    <Screen scroll>
      <AuthForm mode="sign-up" />
    </Screen>
  );
}