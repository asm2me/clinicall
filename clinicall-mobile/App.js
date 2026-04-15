import React, { useCallback, useEffect, useMemo, useState } from 'react';

import AppRoot from './src/AppRoot';
import { LocaleProvider } from './src/context/LocaleContext';
import localeStorage from './src/i18n/localeStorage';
import { clearSession, loadSession, saveSession } from './src/storage/session';

export default function App() {
  const [session, setSession] = useState(null);
  const [locale, setLocaleState] = useState('en');
  const [isBootstrapping, setIsBootstrapping] = useState(true);

  useEffect(() => {
    let active = true;

    async function bootstrap() {
      try {
        const [storedSession, storedLocale] = await Promise.all([loadSession(), localeStorage.get()]);

        if (!active) {
          return;
        }

        setSession(storedSession);
        setLocaleState(storedLocale || 'en');
      } finally {
        if (active) {
          setIsBootstrapping(false);
        }
      }
    }

    bootstrap();

    return () => {
      active = false;
    };
  }, []);

  const handleSessionChange = useCallback(async (nextSession) => {
    setSession(nextSession);

    if (nextSession) {
      await saveSession(nextSession);
    } else {
      await clearSession();
    }
  }, []);

  const handleLocaleChange = useCallback(async (nextLocale) => {
    setLocaleState(nextLocale);
    await localeStorage.set(nextLocale);
  }, []);

  const localeValue = useMemo(
    () => ({
      locale,
      setLocale: handleLocaleChange,
    }),
    [handleLocaleChange, locale]
  );

  return (
    <LocaleProvider value={localeValue}>
      <AppRoot
        initialSession={session}
        isBootstrapping={isBootstrapping}
        onSessionChange={handleSessionChange}
      />
    </LocaleProvider>
  );
}
