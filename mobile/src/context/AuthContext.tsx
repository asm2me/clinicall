import React, { createContext, useContext, useMemo, useState } from 'react';
import type { Role, Session } from '../types';

type AuthContextValue = {
  session: Session | null;
  setSession: (session: Session | null) => void;
  signInAs: (role: Role) => void;
  signOut: () => void;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [session, setSession] = useState<Session | null>(null);

  const value = useMemo<AuthContextValue>(() => ({
    session,
    setSession,
    signInAs: (role: Role) => {
      setSession({
        token: 'demo-token',
        userId: `demo-${role}`,
        role,
        tenantId: 'demo-tenant',
        tenantSlug: 'demo-clinic',
        displayName: role === 'patient' ? 'Patient Demo' : role === 'doctor' ? 'Doctor Demo' : 'Clinic Admin Demo'
      });
    },
    signOut: () => setSession(null)
  }), [session]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}