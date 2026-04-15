import type { WebsitePage } from "@clinicall/shared";

export function getPageTitle(page: WebsitePage): string {
  return page.seo?.title ?? page.title;
}
