import type { ExpoConfig } from "expo/config";

type AppExtra = {
  apiBaseUrl?: string;
};

export function getApiBaseUrl(config: ExpoConfig): string {
  const extra = (config.extra ?? {}) as AppExtra;
  return extra.apiBaseUrl ?? "https://api.platform.com";
}
