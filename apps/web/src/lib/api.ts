import type { ApiEnvelope } from "@clinicall/shared";

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? "https://api.platform.com";

export async function fetchJson<T>(path: string): Promise<ApiEnvelope<T>> {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      "Content-Type": "application/json"
    }
  });

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`);
  }

  return (await response.json()) as ApiEnvelope<T>;
}
