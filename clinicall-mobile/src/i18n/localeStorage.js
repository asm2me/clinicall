import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Localization from 'expo-localization';

const LOCALE_STORAGE_KEY = 'clinicall.locale';
const SUPPORTED_LOCALES = ['en', 'ar', 'fr', 'de'];

function normalizeLocale(value) {
  if (!value || typeof value !== 'string') {
    return 'en';
  }

  const shortCode = value.toLowerCase().split('-')[0];
  return SUPPORTED_LOCALES.includes(shortCode) ? shortCode : 'en';
}

const localeStorage = {
  get: async () => {
    const stored = await AsyncStorage.getItem(LOCALE_STORAGE_KEY);

    if (stored) {
      return normalizeLocale(stored);
    }

    const deviceLocale = Localization.getLocales?.()[0]?.languageCode || Localization.locale || 'en';
    return normalizeLocale(deviceLocale);
  },
  set: async (locale) => {
    const normalized = normalizeLocale(locale);
    await AsyncStorage.setItem(LOCALE_STORAGE_KEY, normalized);
    return normalized;
  },
};

export { SUPPORTED_LOCALES, normalizeLocale };
export default localeStorage;
