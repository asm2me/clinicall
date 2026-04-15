import React from 'react';
import { ActivityIndicator, SafeAreaView, ScrollView, StatusBar, StyleSheet, Text, TextInput, TouchableOpacity, View } from 'react-native';

import { useTranslate } from '../AppRoot';
import { SUPPORTED_LOCALES } from '../i18n/localeStorage';
import translations from '../i18n/translations';
import { theme } from '../theme/theme';

export default function LoginScreen({
  onLogin,
  email,
  password,
  setEmail,
  setPassword,
  error,
  isSubmitting,
  locale,
  onLocaleChange,
}) {
  const t = useTranslate();
  const isRTL = translations[locale]?.meta?.direction === 'rtl';

  return (
    <SafeAreaView style={styles.authSafe}>
      <StatusBar barStyle="light-content" />
      <ScrollView contentContainerStyle={styles.authScroll} keyboardShouldPersistTaps="handled">
        <View style={styles.languageSwitcher}>
          <Text style={styles.languageLabel}>{t('language.label')}</Text>
          <View style={styles.languageOptions}>
            {SUPPORTED_LOCALES.map((item) => (
              <TouchableOpacity
                key={item}
                style={[styles.languageChip, locale === item && styles.languageChipActive]}
                onPress={() => onLocaleChange(item)}
                activeOpacity={0.85}
              >
                <Text style={[styles.languageChipText, locale === item && styles.languageChipTextActive]}>
                  {t(`language.${item}`)}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        <View style={styles.brandBlock}>
          <Text style={styles.brandIcon}>🏥</Text>
          <Text style={styles.brandTitle}>{t('auth.brandTitle')}</Text>
          <Text style={styles.brandSubtitle}>{t('auth.brandSubtitle')}</Text>
        </View>

        <View style={styles.card}>
          <Text style={[styles.cardTitle, isRTL && styles.textRight]}>{t('auth.title')}</Text>

          {error ? (
            <View style={styles.errorBox}>
              <Text style={[styles.errorText, isRTL && styles.textRight]}>{error}</Text>
            </View>
          ) : null}

          <View style={styles.fieldBlock}>
            <Text style={[styles.label, isRTL && styles.textRight]}>{t('auth.emailLabel')}</Text>
            <TextInput
              autoCapitalize="none"
              autoCorrect={false}
              editable={!isSubmitting}
              keyboardType="email-address"
              placeholder={t('auth.emailPlaceholder')}
              placeholderTextColor={theme.colors.muted}
              style={[styles.input, isRTL && styles.textRight]}
              textAlign={isRTL ? 'right' : 'left'}
              value={email}
              onChangeText={setEmail}
            />
          </View>

          <View style={styles.fieldBlock}>
            <Text style={[styles.label, isRTL && styles.textRight]}>{t('auth.passwordLabel')}</Text>
            <TextInput
              secureTextEntry
              editable={!isSubmitting}
              placeholder={t('auth.passwordPlaceholder')}
              placeholderTextColor={theme.colors.muted}
              style={[styles.input, isRTL && styles.textRight]}
              textAlign={isRTL ? 'right' : 'left'}
              value={password}
              onChangeText={setPassword}
            />
          </View>

          <TouchableOpacity style={[styles.primaryButton, isSubmitting && styles.primaryButtonDisabled]} onPress={onLogin} activeOpacity={0.85} disabled={isSubmitting}>
            {isSubmitting ? (
              <ActivityIndicator color={theme.colors.white} />
            ) : (
              <Text style={styles.primaryButtonText}>{t('auth.submit')}</Text>
            )}
          </TouchableOpacity>
        </View>

        <Text style={styles.versionText}>{t('auth.version')}</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  authSafe: {
    flex: 1,
    backgroundColor: theme.colors.primary,
  },
  authScroll: {
    flexGrow: 1,
    justifyContent: 'center',
    padding: 20,
  },
  languageSwitcher: {
    marginBottom: 20,
  },
  languageLabel: {
    color: theme.colors.white,
    fontSize: 13,
    fontWeight: '600',
    marginBottom: 10,
    textAlign: 'center',
  },
  languageOptions: {
    flexDirection: 'row',
    justifyContent: 'center',
    flexWrap: 'wrap',
    gap: 8,
  },
  languageChip: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  languageChipActive: {
    backgroundColor: theme.colors.white,
    borderColor: theme.colors.white,
  },
  languageChipText: {
    color: theme.colors.white,
    fontSize: 12,
    fontWeight: '700',
  },
  languageChipTextActive: {
    color: theme.colors.primary,
  },
  brandBlock: {
    alignItems: 'center',
    marginBottom: 24,
  },
  brandIcon: {
    fontSize: 48,
    marginBottom: 10,
  },
  brandTitle: {
    color: theme.colors.white,
    fontSize: 28,
    fontWeight: '700',
  },
  brandSubtitle: {
    color: 'rgba(255,255,255,0.8)',
    fontSize: 16,
    marginTop: 6,
    textAlign: 'center',
  },
  card: {
    backgroundColor: theme.colors.panel,
    borderRadius: 24,
    padding: 20,
    borderWidth: 1,
    borderColor: theme.colors.borderSoft,
    ...theme.shadow.card,
  },
  cardTitle: {
    color: theme.colors.heading,
    fontSize: 22,
    fontWeight: '700',
    marginBottom: 18,
  },
  errorBox: {
    backgroundColor: '#fff1f2',
    borderWidth: 1,
    borderColor: '#fecdd3',
    borderRadius: theme.radius.md,
    padding: 12,
    marginBottom: 16,
  },
  errorText: {
    color: theme.colors.danger,
    fontSize: 14,
    fontWeight: '600',
  },
  fieldBlock: {
    marginBottom: 16,
  },
  label: {
    color: theme.colors.heading,
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 8,
  },
  input: {
    backgroundColor: theme.colors.panelSoft,
    borderWidth: 1,
    borderColor: theme.colors.border,
    borderRadius: theme.radius.md,
    paddingHorizontal: 14,
    paddingVertical: 14,
    color: theme.colors.text,
    fontSize: 16,
  },
  primaryButton: {
    backgroundColor: theme.colors.primary,
    borderRadius: theme.radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    marginTop: 8,
    minHeight: 52,
  },
  primaryButtonDisabled: {
    opacity: 0.7,
  },
  primaryButtonText: {
    color: theme.colors.white,
    fontSize: 16,
    fontWeight: '700',
  },
  versionText: {
    marginTop: 18,
    textAlign: 'center',
    color: 'rgba(255,255,255,0.8)',
    fontSize: 13,
  },
  textRight: {
    textAlign: 'right',
  },
});
