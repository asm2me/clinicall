import React, { useEffect, useState } from 'react';
import { ActivityIndicator, SafeAreaView, StyleSheet, Text, View } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import { useTranslate } from '../AppRoot';
import { useLocaleContext } from '../context/LocaleContext';
import DashboardScreen from '../screens/DashboardScreen';
import LoginScreen from '../screens/LoginScreen';
import { getSession } from '../services/auth';
import { getDashboardSummary } from '../services/dashboard';
import { theme } from '../theme/theme';
import ROUTES from './routeNames';

const Stack = createNativeStackNavigator();

function LoadingScreen() {
  const t = useTranslate();

  return (
    <SafeAreaView style={styles.loadingSafe}>
      <View style={styles.loadingInner}>
        <ActivityIndicator color={theme.colors.primary} size="large" />
        <Text style={styles.loadingText}>{t('auth.loading')}</Text>
      </View>
    </SafeAreaView>
  );
}

export default function RootNavigator({ session, setSession, onSessionChange }) {
  const { locale, setLocale } = useLocaleContext();
  const t = useTranslate();

  const [email, setEmail] = useState(session?.user?.email || 'admin@example.com');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [dashboardSummary, setDashboardSummary] = useState(null);
  const [dashboardError, setDashboardError] = useState('');
  const [isDashboardLoading, setIsDashboardLoading] = useState(false);
  const [isRefreshingSession, setIsRefreshingSession] = useState(Boolean(session?.token));

  useEffect(() => {
    setEmail(session?.user?.email || 'admin@example.com');
  }, [session?.user?.email]);

  useEffect(() => {
    let active = true;

    async function refreshSession() {
      if (!session?.token) {
        setIsRefreshingSession(false);
        return;
      }

      setIsRefreshingSession(true);
      const result = await getSession();

      if (!active) {
        return;
      }

      if (result.ok) {
        await onSessionChange({
          token: result.token,
          user: result.user,
          tenant: result.tenant,
          permissions: result.permissions,
          expiresAt: result.expiresAt,
        });
      } else {
        await onSessionChange(null);
      }

      if (active) {
        setIsRefreshingSession(false);
      }
    }

    refreshSession();

    return () => {
      active = false;
    };
  }, [onSessionChange, session?.token]);

  async function handleLogin() {
    if (!email.trim() || !password.trim()) {
      setError(t('auth.required'));
      return;
    }

    setIsSubmitting(true);
    setError('');

    const authService = await import('../services/auth');
    const result = await authService.login({
      email,
      password,
    });

    if (!result.ok) {
      const lockSuffix = result.error?.lockedUntil ? ` ${result.error.lockedUntil}` : '';
      setError(`${result.error?.message || t('auth.failed')}${lockSuffix}`);
      setIsSubmitting(false);
      return;
    }

    await onSessionChange({
      user: result.user,
      token: result.token,
      refreshToken: result.refreshToken,
      expiresAt: result.expiresAt,
      permissions: result.permissions,
      tenant: result.tenant,
    });

    setPassword('');
    setIsSubmitting(false);
  }

  async function loadDashboard(currentSession = session) {
    if (!currentSession?.token) {
      setDashboardSummary(null);
      return;
    }

    setIsDashboardLoading(true);
    setDashboardError('');

    const result = await getDashboardSummary({
      token: currentSession.token,
      tenantId: currentSession.tenant?.id,
    });

    if (result.ok) {
      setDashboardSummary(result.summary);
      setDashboardError('');
    } else {
      setDashboardSummary(null);
      setDashboardError(result.error?.message || t('dashboard.unavailable'));
    }

    setIsDashboardLoading(false);
  }

  useEffect(() => {
    if (session?.token) {
      loadDashboard(session);
    } else {
      setDashboardSummary(null);
      setDashboardError('');
    }
  }, [locale, session?.token]);

  async function handleLogout() {
    setPassword('');
    setError('');
    setDashboardError('');
    setDashboardSummary(null);

    const authService = await import('../services/auth');
    if (typeof authService.logout === 'function') {
      await authService.logout();
    }

    await onSessionChange(null);
  }

  if (isRefreshingSession) {
    return <LoadingScreen />;
  }

  return (
    <Stack.Navigator
      screenOptions={{
        headerShadowVisible: false,
        animation: 'none',
        gestureEnabled: false,
        contentStyle: { backgroundColor: theme.colors.background },
      }}
    >
      {session?.token ? (
        <Stack.Screen name={ROUTES.DASHBOARD} options={{ title: t('dashboard.eyebrow') }}>
          {() => (
            <DashboardScreen
              onLogout={handleLogout}
              session={session}
              dashboardSummary={dashboardSummary}
              dashboardError={dashboardError}
              isDashboardLoading={isDashboardLoading}
              onRetryDashboard={() => loadDashboard(session)}
              locale={locale}
              onLocaleChange={setLocale}
            />
          )}
        </Stack.Screen>
      ) : (
        <Stack.Screen name={ROUTES.LOGIN} options={{ headerShown: false }}>
          {() => (
            <LoginScreen
              onLogin={handleLogin}
              email={email}
              password={password}
              setEmail={setEmail}
              setPassword={setPassword}
              error={error}
              isSubmitting={isSubmitting}
              locale={locale}
              onLocaleChange={setLocale}
            />
          )}
        </Stack.Screen>
      )}
    </Stack.Navigator>
  );
}

const styles = StyleSheet.create({
  loadingSafe: {
    flex: 1,
    backgroundColor: theme.colors.background,
  },
  loadingInner: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 16,
  },
  loadingText: {
    color: theme.colors.heading,
    fontSize: 16,
    fontWeight: '600',
  },
});
