import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { theme } from '../theme/theme';

export default function AppointmentCard({ item }) {
  return (
    <View style={styles.appointmentCard}>
      <View style={styles.appointmentRow}>
        <Text style={styles.appointmentTime}>{item.time}</Text>
        <View style={[styles.statusPill, item.status === 'Pending' ? styles.statusPending : styles.statusConfirmed]}>
          <Text style={styles.statusText}>{item.status}</Text>
        </View>
      </View>
      <Text style={styles.appointmentPatient}>{item.patient}</Text>
      <Text style={styles.appointmentMeta}>
        {item.doctor} · {item.clinic}
      </Text>
      <Text style={styles.appointmentMeta}>Ref: {item.id}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  appointmentCard: {
    backgroundColor: theme.colors.panelSoft,
    borderRadius: theme.radius.md,
    padding: 14,
    borderWidth: 1,
    borderColor: theme.colors.borderSoft,
  },
  appointmentRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  appointmentTime: {
    color: theme.colors.primaryDeep,
    fontSize: 16,
    fontWeight: '700',
  },
  appointmentPatient: {
    color: theme.colors.heading,
    fontSize: 17,
    fontWeight: '700',
    marginTop: 10,
    marginBottom: 4,
  },
  appointmentMeta: {
    color: theme.colors.muted,
    fontSize: 14,
    marginTop: 2,
  },
  statusPill: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: theme.radius.pill,
  },
  statusPending: {
    backgroundColor: '#fff7e6',
  },
  statusConfirmed: {
    backgroundColor: '#ecfdf3',
  },
  statusText: {
    color: theme.colors.heading,
    fontSize: 12,
    fontWeight: '700',
  },
});
