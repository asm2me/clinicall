import React, { useState } from 'react';
import { Link, router } from 'expo-router';
import { PrimaryButton } from '../../components/PrimaryButton';
import { Card, Text, TextInput, View } from '../../components/primitives';
import { useAuth } from '../../context/AuthContext';

type Props = {
  mode: 'sign-in' | 'sign-up' | 'forgot-password';
};

export function AuthForm({ mode }: Props) {
  const { signInAs } = useAuth();
  const [email, setEmail] = useState('patient@demo.local');
  const [password, setPassword] = useState('password');
  const [name, setName] = useState('Demo User');
  const [role, setRole] = useState<'patient' | 'doctor' | 'clinic_admin'>('patient');

  const submit = () => {
    if (mode === 'forgot-password') {
      router.replace('/(auth)/welcome');
      return;
    }

    signInAs(role);
    router.replace('/');
  };

  return (
    <View style={{ gap: 16 }}>
      <View style={{ gap: 8 }}>
        <Text variant="title">{mode === 'sign-in' ? 'Sign in' : mode === 'sign-up' ? 'Create account' : 'Reset password'}</Text>
        <Text variant="body">
          {mode === 'sign-in'
            ? 'Access your appointment history, schedules, and notifications.'
            : mode === 'sign-up'
              ? 'Create a tenant-aware account for booking and care management.'
              : 'We will send a password reset link to your email address.'}
        </Text>
      </View>

      <Card style={{ gap: 12 }}>
        {mode === 'sign-up' ? (
          <View style={{ gap: 8 }}>
            <Text variant="label">Full name</Text>
            <TextInput value={name} onChangeText={setName} placeholder="Your name" />
          </View>
        ) : null}

        {mode !== 'forgot-password' ? (
          <View style={{ gap: 8 }}>
            <Text variant="label">Email</Text>
            <TextInput value={email} onChangeText={setEmail} autoCapitalize="none" keyboardType="email-address" placeholder="email@clinic.com" />
          </View>
        ) : null}

        {mode !== 'forgot-password' ? (
          <View style={{ gap: 8 }}>
            <Text variant="label">Password</Text>
            <TextInput value={password} onChangeText={setPassword} secureTextEntry placeholder="Password" />
          </View>
        ) : null}

        {mode === 'sign-up' ? (
          <View style={{ gap: 8 }}>
            <Text variant="label">Role</Text>
            <TextInput value={role} onChangeText={(value) => setRole(value as typeof role)} placeholder="patient, doctor, or clinic_admin" />
          </View>
        ) : null}

        <PrimaryButton label={mode === 'forgot-password' ? 'Send reset link' : mode === 'sign-in' ? 'Sign in' : 'Create account'} onPress={submit} />
      </Card>

      <View style={{ gap: 10 }}>
        {mode !== 'sign-in' ? <Link href="/(auth)/sign-in">Already have an account? Sign in</Link> : null}
        {mode !== 'sign-up' ? <Link href="/(auth)/sign-up">Need an account? Sign up</Link> : null}
        {mode !== 'forgot-password' ? <Link href="/(auth)/forgot-password">Forgot password?</Link> : null}
      </View>
    </View>
  );
}