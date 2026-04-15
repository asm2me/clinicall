import React from 'react';
import { Pressable, StyleSheet, Text as RNText, TextInput as RNTextInput, View as RNView } from 'react-native';

type Variant = 'title' | 'body' | 'label' | 'eyebrow' | 'caption';

export function View({ style, ...props }: React.ComponentProps<typeof RNView>) {
  return <RNView style={[styles.view, style]} {...props} />;
}

export function Text({
  variant = 'body',
  style,
  ...props
}: React.ComponentProps<typeof RNText> & { variant?: Variant }) {
  return <RNText style={[styles[variant], style]} {...props} />;
}

export function TextInput(props: React.ComponentProps<typeof RNTextInput>) {
  return <RNTextInput placeholderTextColor="#94A3B8" style={styles.input} {...props} />;
}

export function Card({ style, ...props }: React.ComponentProps<typeof RNView>) {
  return <RNView style={[styles.card, style]} {...props} />;
}

export function SurfaceButton({
  children,
  style,
  ...props
}: React.ComponentProps<typeof Pressable> & { children: React.ReactNode }) {
  return (
    <Pressable style={({ pressed }) => [styles.button, pressed && styles.pressed, style]} {...props}>
      <RNText style={styles.buttonText}>{children}</RNText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  view: {
    flexDirection: 'column'
  },
  title: {
    fontSize: 30,
    lineHeight: 36,
    fontWeight: '700',
    color: '#0F172A'
  },
  body: {
    fontSize: 16,
    lineHeight: 24,
    color: '#334155'
  },
  label: {
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '600',
    color: '#0F172A'
  },
  eyebrow: {
    fontSize: 12,
    lineHeight: 16,
    fontWeight: '700',
    letterSpacing: 1,
    textTransform: 'uppercase',
    color: '#2563EB'
  },
  caption: {
    fontSize: 12,
    lineHeight: 16,
    color: '#64748B'
  },
  input: {
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 16,
    color: '#0F172A',
    backgroundColor: '#FFFFFF'
  },
  card: {
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: 20,
    padding: 16,
    backgroundColor: '#FFFFFF',
    gap: 12
  },
  button: {
    minHeight: 48,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#2563EB',
    paddingHorizontal: 16
  },
  buttonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700'
  },
  pressed: {
    opacity: 0.9
  }
});
</styles>