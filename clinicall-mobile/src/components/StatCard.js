import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { theme } from '../theme/theme';

export default function StatCard({ label, value }) {
  return (
    <View style={styles.statCard}>
      <Text style={styles.statValue}>{value}</Text>
      <Text style={styles.statLabel}>{label}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  statCard: {
    flexGrow: 1,
    minWidth: 140,
    backgroundColor: theme.colors.panel,
    borderRadius: theme.radius.md,
    padding: 16,
    borderWidth: 1,
    borderColor: theme.colors.borderSoft,
    ...theme.shadow.card,
  },
  statValue: {
    color: theme.colors.heading,
    fontSize: 24,
    fontWeight: '800',
    marginBottom: 4,
  },
  statLabel: {
    color: theme.colors.muted,
    fontSize: 14,
    fontWeight: '600',
  },
});
