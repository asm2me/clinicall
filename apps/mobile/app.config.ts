import type { ExpoConfig } from "expo/config";

const config: ExpoConfig = {
  name: "Clinicall",
  slug: "clinicall",
  scheme: "clinicall",
  version: "0.1.0",
  orientation: "portrait",
  userInterfaceStyle: "light",
  platforms: ["ios", "android", "web"],
  extra: {
    apiBaseUrl: process.env.EXPO_PUBLIC_API_BASE_URL ?? "https://api.platform.com"
  }
};

export default config;
