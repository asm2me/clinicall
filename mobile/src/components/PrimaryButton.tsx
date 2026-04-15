import React from 'react';
import { Pressable, StyleSheet, Text } from 'react-native';

type Props = {
  label: string;
  onPress?: () => void;
  tone?: 'primary' | 'secondary';
};

export function PrimaryButton({ label, onPress, tone = 'primary' }: Props) {
  return (
    <Pressable
      onPress={onPress}
      style={({ pressed }) => [
        styles.base,
        tone === 'secondary' ? styles.secondary : styles.primary,
        pressed && styles.pressed
      ]}
    >
      <Text style={[styles.label, tone === 'secondary' && styles.secondaryLabel]}>{label}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    minHeight: 48,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 16
  },
  primary: {
    backgroundColor: '#2563EB'
  },
  secondary: {
    backgroundColor: '#DBEAFE'
  },
  label: {
    fontSize: 16,
    fontWeight: '700',
    color: '#FFFFFF'
  },
  secondaryLabel: {
    color: '#1D4ED8'
  },
  pressed: {
    opacity: 0.9
  }
});