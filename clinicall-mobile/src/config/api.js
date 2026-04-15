const DEFAULT_API_BASE_URL = 'http://localhost:3000';

function normalizeBaseUrl(value) {
  if (typeof value !== 'string') {
    return '';
  }

  return value.trim().replace(/\/+$/, '');
}

export function getApiBaseUrl() {
  const envBaseUrl =
    typeof process !== 'undefined' && process.env
      ? process.env.EXPO_PUBLIC_API_BASE_URL || process.env.EXPO_PUBLIC_API_URL
      : '';

  const normalizedEnvBaseUrl = normalizeBaseUrl(envBaseUrl);

  return normalizedEnvBaseUrl || DEFAULT_API_BASE_URL;
}

export const API_BASE_URL = getApiBaseUrl();

export function buildApiUrl(path) {
  const baseUrl = getApiBaseUrl();
  const normalizedPath =
    typeof path === 'string' ? `/${path.trim().replace(/^\/+/, '')}` : '';

  return `${baseUrl}${normalizedPath}`;
}