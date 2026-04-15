import { useMemo, useState } from 'react';

const useLocale = () => {
  const [locale, setLocale] = useState('en');

  return useMemo(
    () => ({
      locale,
      setLocale,
    }),
    [locale]
  );
};

export default useLocale;