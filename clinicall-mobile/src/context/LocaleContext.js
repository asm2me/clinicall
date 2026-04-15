import React, { createContext, useContext } from 'react';

const LocaleContext = createContext({
  locale: 'en',
  setLocale: async () => {},
});

export const LocaleProvider = ({ children, value }) => {
  return <LocaleContext.Provider value={value}>{children}</LocaleContext.Provider>;
};

export const useLocaleContext = () => useContext(LocaleContext);

export default LocaleContext;
