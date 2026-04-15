# Frontend Strategy for Clinicall SaaS

## Scope
This document defines the frontend information architecture and route boundaries for the SaaS clinic platform across:
- Public clinic websites
- Clinic admin dashboard
- Super admin panel
- Website builder pages
- Public booking widget
- Tenant-aware layouts

## Assumptions
Because repository inspection was blocked by the execution environment, this strategy is written as a framework-agnostic frontend IA intended to be adapted into the existing web stack once accessible. It assumes a Next.js-style app router or similarly route-based web frontend.

## Core Frontend Principles
1. Tenant-aware by default
   - Every clinic-scoped page must resolve tenant context from subdomain, custom domain, or tenant slug.
   - Tenant data must be injected through layout-level providers rather than hardcoded in components.

2. Public and authenticated surfaces are separated
   - Public clinic site and booking flows remain anonymous.
   - Clinic admin and super admin require authenticated shells and role-based navigation.

3. Shared contracts drive UI
   - Website pages, booking DTOs, and tenant settings should be rendered from backend contracts.
   - UI should not duplicate backend entity definitions.

4. Reusable UI boundaries
   - Shells, navigation, forms, tables, cards, dialogs, and editors should be isolated into reusable component layers.
   - Website builder and admin dashboard must share a common design system.

## Proposed Route Map

### Public Clinic Website
- `/`
  - Tenant public homepage
- `/about`
- `/services`
- `/doctors`
- `/pricing`
- `/contact`
- `/book`
  - Public booking flow entry
- `/book/[serviceId]`
  - Service-specific booking flow
- `/book/[serviceId]/[providerId]`
  - Optional provider-specific booking step
- `/pages/[slug]`
  - Dynamic tenant CMS pages
- `/articles/[slug]`
  - Optional blog/content pages if enabled

### Public Booking Widget
- `/widget/book`
  - Embedded widget route
- `/widget/book/[step]`
  - Multi-step widget flow
- `/widget/confirm`
  - Confirmation state
- `/widget/error`
  - Error state
- `/embed/book`
  - iframe-safe embed entrypoint

### Clinic Admin Dashboard
- `/app`
  - Dashboard overview
- `/app/appointments`
- `/app/patients`
- `/app/doctors`
- `/app/services`
- `/app/schedule`
- `/app/invoices`
- `/app/messages`
- `/app/settings`
- `/app/settings/profile`
- `/app/settings/clinic`
- `/app/settings/website`
- `/app/website/pages`
- `/app/website/pages/[pageId]`
- `/app/website/navigation`
- `/app/website/media`
- `/app/website/forms`
- `/app/rbac/roles`
- `/app/rbac/users`

### Website Builder Pages
- `/app/website/builder`
  - Builder home
- `/app/website/builder/[pageId]`
  - Page editing canvas
- `/app/website/builder/templates`
- `/app/website/builder/sections`
- `/app/website/builder/assets`
- `/app/website/builder/preview/[pageId]`

### Super Admin Panel
- `/admin`
  - Global overview
- `/admin/tenants`
- `/admin/tenants/[tenantId]`
- `/admin/domains`
- `/admin/plans`
- `/admin/subscriptions`
- `/admin/feature-flags`
- `/admin/users`
- `/admin/audit-logs`
- `/admin/system-health`
- `/admin/billing`

## Layout Architecture

### PublicTenantLayout
Used for all clinic-public routes.
Responsibilities:
- Resolve tenant by domain or slug
- Load public tenant branding
- Inject metadata, theme tokens, and SEO
- Provide public navigation/footer
- Expose booking entry points

### PublicBookingLayout
Used for booking widget and booking flows.
Responsibilities:
- Minimal chrome
- Stepper/progress UI
- Embed-safe rendering
- Booking state persistence
- Tenant-aware theming

### ClinicAdminLayout
Used for authenticated clinic dashboard.
Responsibilities:
- Sidebar navigation
- Role-based nav filtering
- Tenant switcher if needed
- Notifications and top bar
- Shared admin tables/forms/panels

### SuperAdminLayout
Used for global platform administration.
Responsibilities:
- Global navigation
- Tenant and billing management
- Feature flag controls
- System diagnostics and audit views

### WebsiteBuilderLayout
Used for visual page building.
Responsibilities:
- Left sidebar: sections/blocks
- Center canvas: live preview
- Right panel: block/page properties
- Autosave and version history
- Device preview modes

## Component Boundaries

### Shared Foundation Components
- `AppShell`
- `AuthGate`
- `TenantGate`
- `RoleGate`
- `TopNav`
- `SideNav`
- `Breadcrumbs`
- `PageHeader`
- `DataTable`
- `StatCard`
- `Modal`
- `Drawer`
- `Tabs`
- `FormField`
- `FormSection`
- `EmptyState`
- `LoadingState`
- `ErrorState`

### Public Website Components
- `HeroSection`
- `ServiceGrid`
- `DoctorCard`
- `TestimonialCarousel`
- `FAQSection`
- `ContactSection`
- `BookingCTA`
- `ClinicFooter`
- `ClinicHeader`

### Booking Widget Components
- `BookingStepper`
- `ServicePicker`
- `ProviderPicker`
- `DateTimePicker`
- `PatientDetailsForm`
- `AppointmentReview`
- `BookingSuccess`
- `BookingFailure`

### Admin Dashboard Components
- `DashboardKPI`
- `AppointmentsTable`
- `PatientTable`
- `ScheduleCalendar`
- `InvoiceTable`
- `PermissionMatrix`
- `TenantSettingsForm`
- `WebsitePageList`
- `MediaLibrary`

### Website Builder Components
- `BuilderCanvas`
- `BlockPalette`
- `SectionList`
- `PageSettingsPanel`
- `BlockInspector`
- `TemplateGallery`
- `PreviewFrame`
- `VersionTimeline`

### Super Admin Components
- `TenantTable`
- `DomainTable`
- `PlanTable`
- `SubscriptionTable`
- `FeatureFlagPanel`
- `AuditLogTable`
- `HealthStatusPanel`

## Tenant-Aware Data Flow
1. Resolve tenant from request origin or route context.
2. Load tenant branding, feature flags, and enabled modules.
3. Load public website pages and navigation.
4. Provide tenant-scoped API client to child components.
5. Render route groups based on authenticated role or public access.

## API Integration Strategy
Frontend should consume versioned endpoints such as:
- `/api/v1/public/tenants/:slug`
- `/api/v1/public/pages/:slug`
- `/api/v1/public/bookings`
- `/api/v1/tenant/dashboard`
- `/api/v1/tenant/appointments`
- `/api/v1/tenant/website/pages`
- `/api/v1/admin/tenants`
- `/api/v1/admin/subscriptions`

## Reuse Strategy
- Public clinic UI should reuse the same design tokens as admin.
- Booking widget should reuse form controls and validation components.
- Website builder should reuse media picker, forms, and preview components from admin.
- Super admin should reuse data table, filter, and export utilities from admin.

## Recommended Next Steps
1. Inspect the existing web app stack and map these routes onto the current framework.
2. Add tenant-aware layouts first.
3. Add shared design system primitives.
4. Implement public booking widget and public website rendering.
5. Implement clinic admin and super admin route groups.
6. Add website builder with live preview and versioning.