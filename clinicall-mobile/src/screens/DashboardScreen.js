import React from 'react';
import { ActivityIndicator, SafeAreaView, ScrollView, StatusBar, StyleSheet, Text, TouchableOpacity, View } from 'react-native';

import { useTranslate } from '../AppRoot';
import AppointmentCard from '../components/AppointmentCard';
import StatCard from '../components/StatCard';
import { quickActions } from '../data/mockDashboard';
import { SUPPORTED_LOCALES } from '../i18n/localeStorage';
import translations from '../i18n/translations';
import { theme } from '../theme/theme';

function normalizeStats(summary) {
  if (!summary || !Array.isArray(summary.stats) || summary.stats.length === 0) {
    return [];
  }

  return summary.stats.map((item, index) => ({
    label: item.label || `Stat ${index + 1}`,
    value: item.value ?? '—',
  }));
}

function normalizeAppointments(summary) {
  if (!summary || !Array.isArray(summary.appointments) || summary.appointments.length === 0) {
    return [];
  }

  return summary.appointments.map((item, index) => ({
    id: item.id || `appointment-${index + 1}`,
    time: item.time || '—',
    patient: item.patientName || 'Unknown patient',
    doctor: item.doctorName || 'Unknown doctor',
    clinic: item.location || 'Unknown clinic',
    status: item.status || 'Scheduled',
  }));
}

export default function DashboardScreen({
  onLogout,
  session,
  dashboardSummary,
  dashboardError,
  isDashboardLoading,
  onRetryDashboard,
  locale,
  onLocaleChange,
}) {
  const t = useTranslate();
  const stats = normalizeStats(dashboardSummary);
  const appointments = normalizeAppointments(dashboardSummary);
  const displayName = session?.user?.name || session?.user?.email || 'Admin';
  const isRTL = translations[locale]?.meta?.direction === 'rtl';

  return (
    <SafeAreaView style={styles.appSafe}>
      <StatusBar barStyle="dark-content" />
      <ScrollView contentContainerStyle={styles.appScroll}>
        <View style={styles.languageRow}>
          <Text style={[styles.languageRowLabel, isRTL && styles.textRight]}>{t('language.label')}</Text>
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

        <View style={styles.heroCard}>
          <Text style={[styles.heroEyebrow, isRTL && styles.textRight]}>{t('dashboard.eyebrow')}</Text>
          <Text style={[styles.heroTitle, isRTL && styles.textRight]}>{t('dashboard.welcome', { name: displayName })}</Text>
          <Text style={[styles.heroSubtitle, isRTL && styles.textRight]}>{t('dashboard.subtitle')}</Text>

          <View style={styles.heroActions}>
            <TouchableOpacity style={styles.primaryButtonSmall} activeOpacity={0.85}>
              <Text style={styles.primaryButtonText}>{t('dashboard.newAppointment')}</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.secondaryButtonSmall} onPress={onLogout} activeOpacity={0.85}>
              <Text style={styles.secondaryButtonText}>{t('dashboard.logout')}</Text>
            </TouchableOpacity>
          </View>
        </View>

        {dashboardError ? (
          <View style={styles.errorCard}>
            <Text style={[styles.errorTitle, isRTL && styles.textRight]}>{t('dashboard.unavailable')}</Text>
            <Text style={[styles.errorMessage, isRTL && styles.textRight]}>{dashboardError}</Text>
            <TouchableOpacity style={styles.retryButton} onPress={onRetryDashboard} activeOpacity={0.85}>
              <Text style={styles.retryButtonText}>{t('dashboard.retry')}</Text>
            </TouchableOpacity>
          </View>
        ) : null}

        <View style={styles.sectionCard}>
          <View style={styles.sectionHeaderRow}>
            <Text style={[styles.sectionTitle, isRTL && styles.textRight]}>{t('dashboard.summary')}</Text>
            {isDashboardLoading ? <ActivityIndicator color={theme.colors.primary} /> : null}
          </View>

          {stats.length > 0 ? (
            <View style={styles.statsGrid}>
              {stats.map((item) => (
                <StatCard key={item.label} label={String(item.label)} value={String(item.value)} />
              ))}
            </View>
          ) : (
            <Text style={[styles.emptyText, isRTL && styles.textRight]}>
              {isDashboardLoading ? t('dashboard.loadingSummary') : t('dashboard.noSummary')}
            </Text>
          )}
        </View>

        <View style={styles.sectionCard}>
          <Text style={[styles.sectionTitle, isRTL && styles.textRight]}>{t('dashboard.appointmentsTitle')}</Text>
          <Text style={[styles.sectionSubtitle, isRTL && styles.textRight]}>{t('dashboard.appointmentsSubtitle')}</Text>
          <View style={styles.listStack}>
            {appointments.length > 0 ? (
              appointments.map((item) => <AppointmentCard key={item.id} item={item} />)
            ) : (
              <Text style={[styles.emptyText, isRTL && styles.textRight]}>
                {isDashboardLoading ? t('dashboard.loadingAppointments') : t('dashboard.noAppointments')}
              </Text>
            )}
          </View>
        </View>

        <View style={styles.sectionCard}>
          <Text style={[styles.sectionTitle, isRTL && styles.textRight]}>{t('dashboard.quickActions')}</Text>
          <View style={styles.quickActionsGrid}>
            {quickActions.map((item) => (
              <View key={item} style={styles.quickActionTile}>
                <Text style={styles.quickActionText}>{item}</Text>
              </View>
            ))}
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  appSafe: {
    flex: 1,
    backgroundColor: theme.colors.background,
  },
  appScroll: {
    padding: 16,
    paddingBottom: 32,
  },
  languageRow: {
    marginBottom: 12,
  },
  languageRowLabel: {
    color: theme.colors.muted,
    fontSize: 13,
    fontWeight: '600',
    marginBottom: 8,
  },
  languageOptions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  languageChip: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: theme.colors.panel,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  languageChipActive: {
    backgroundColor: theme.colors.primary,
    borderColor: theme.colors.primary,
  },
  languageChipText: {
    color: theme.colors.heading,
    fontSize: 12,
    fontWeight: '700',
  },
  languageChipTextActive: {
    color: theme.colors.white,
  },
  heroCard: {
    backgroundColor: theme.colors.panel,
    borderRadius: theme.radius.lg,
    padding: 20,
    borderWidth: 1,
    borderColor: theme.colors.borderSoft,
    marginBottom: 16,
    ...theme.shadow.card,
  },
  heroEyebrow: {
    color: theme.colors.primary,
    fontSize: 13,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 6,
  },
  heroTitle: {
    color: theme.colors.heading,
    fontSize: 26,
    fontWeight: '800',
    marginBottom: 8,
  },
  heroSubtitle: {
    color: theme.colors.muted,
    fontSize: 15,
    lineHeight: 22,
  },
  heroActions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    marginTop: 18,
  },
  primaryButtonSmall: {
    backgroundColor: theme.colors.primary,
    borderRadius: theme.radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    minWidth: 150,
  },
  secondaryButtonSmall: {
    backgroundColor: theme.colors.panel,
    borderRadius: theme.radius.md,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    minWidth: 120,
    borderWidth: 1,
    borderColor: theme.colors.border,
  },
  primaryButtonText: {
    color: theme.colors.white,
    fontSize: 16,
    fontWeight: '700',
  },
  secondaryButtonText: {
    color: theme.colors.heading,
    fontSize: 16,
    fontWeight: '700',
  },
  errorCard: {
    backgroundColor: '#fff1f2',
    borderWidth: 1,
    borderColor: '#fecdd3',
    borderRadius: theme.radius.lg,
    padding: 16,
    marginBottom: 16,
  },
  errorTitle: {
    color: theme.colors.danger,
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  errorMessage: {
    color: theme.colors.text,
    fontSize: 14,
    marginBottom: 12,
  },
  retryButton: {
    alignSelf: 'flex-start',
    backgroundColor: theme.colors.primary,
    borderRadius: theme.radius.md,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  retryButtonText: {
    color: theme.colors.white,
    fontSize: 14,
    fontWeight: '700',
  },
  sectionCard: {
    backgroundColor: theme.colors.panel,
    borderRadius: theme.radius.lg,
    padding: 18,
    borderWidth: 1,
    borderColor: theme.colors.borderSoft,
    marginBottom: 16,
    ...theme.shadow.card,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    color: theme.colors.heading,
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 4,
  },
  sectionSubtitle: {
    color: theme.colors.muted,
    fontSize: 14,
    marginBottom: 16,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  listStack: {
    gap: 12,
  },
  emptyText: {
    color: theme.colors.muted,
    fontSize: 14,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  quickActionTile: {
    minWidth: 100,
    paddingVertical: 14,
    paddingHorizontal: 12,
    borderRadius: theme.radius.md,
    backgroundColor: theme.colors.primarySoft,
    borderWidth: 1,
    borderColor: '#cfe0ff',
  },
  quickActionText: {
    color: theme.colors.primaryDeep,
    fontSize: 14,
    fontWeight: '700',
    textAlign: 'center',
  },
  textRight: {
    textAlign: 'right',
  },
});
