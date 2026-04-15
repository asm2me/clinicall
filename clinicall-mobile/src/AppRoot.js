import React, { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, I18nManager, SafeAreaView, StyleSheet, Text, View } from 'react-native';
import { NavigationContainer, DefaultTheme } from '@react-navigation/native';

import RootNavigator from './navigation/RootNavigator';
import translations from './i18n/translations';
import { useLocaleContext } from './context/LocaleContext';
import { theme } from './theme/theme';

function translate(locale, key, params = {}) {
  const fallbackLocale = translations.en || {};
  const activeLocale = translations[locale] || fallbackLocale;
  const value = key.split('.').reduce((acc, part) => (acc && acc[part] != null ? acc[part] : undefined), activeLocale);

  const template =
    typeof value === 'string'
      ? value
      : key.split('.').reduce((acc, part) => (acc && acc[part] != null ? acc[part] : undefined), fallbackLocale) || key;

  return Object.entries(params).reduce((message, [paramKey, paramValue]) => {
    return message.replaceAll(`{{${paramKey}}}`, String(paramValue));
  }, template);
}

export function useTranslate() {
  const { locale } = useLocaleContext();

  return useMemo(() => {
    return (key, params) => translate(locale, key, params);
  }, [locale]);
}

const navigationTheme = {
  ...DefaultTheme,
  colors: {
    ...DefaultTheme.colors,
    background: theme.colors.background,
    card: theme.colors.panel,
    text: theme.colors.heading,
    primary: theme.colors.primary,
    border: theme.colors.borderSoft,
  },
};

function BootSplash() {
  const t = useTranslate();

  return (
    <SafeAreaView style={styles.bootSafe}>
      <View style={styles.bootInner}>
        <ActivityIndicator color={theme.colors.primary} size="large" />
        <Text style={styles.bootText}>{t('auth.brandTitle')}</Text>
      </View>
    </SafeAreaView>
  );
}

export default function AppRoot({ initialSession, isBootstrapping, onSessionChange }) {
  const { locale } = useLocaleContext();
  const [session, setSession] = useState(initialSession);

  useEffect(() => {
    setSession(initialSession);
  }, [initialSession]);

  useEffect(() => {
    const direction = translations[locale]?.meta?.direction || 'ltr';
    const shouldBeRTL = direction === 'rtl';

    if (I18nManager.isRTL !== shouldBeRTL) {
      I18nManager.allowRTL(shouldBeRTL);
      I18nManager.forceRTL(shouldBeRTL);
    }
  }, [locale]);

  if (isBootstrapping) {
    return <BootSplash />;
  }

  return (
    <NavigationContainer theme={navigationTheme}>
      <RootNavigator session={session} setSession={setSession} onSessionChange={onSessionChange} />
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  bootSafe: {
    flex: 1,
    backgroundColor: theme.colors.background,
  },
  bootInner: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 16,
  },
  bootText: {
    color: theme.colors.heading,
    fontSize: 20,
    fontWeight: '700',
  },
});
