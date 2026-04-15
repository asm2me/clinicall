# ClinicAll SaaS

Workspace bootstrap for a multi-tenant clinic platform.

## Architecture

- `backend/` Laravel backend contracts
- `web/` Next.js frontend workspace placeholder
- `mobile/` Expo React Native workspace placeholder

## Current contracts

### Backend
- API base path: `/api/v1`
- Tenant middleware aliases:
  - `tenant.resolve`
  - `tenant.enforce`
  - `tenant.scope`
- RBAC middleware aliases:
  - `rbac.api`
  - `rbac.web`

### Multi-tenant tables
- Public schema:
  - `tenants`
  - `domains`
  - `plans`
  - `subscriptions`
  - `feature_flags`
- Tenant schema:
  - `users`
  - `doctors`
  - `patients`
  - `appointments`
  - `schedules`
  - `invoices`
  - `services`
  - `settings`
  - `website_pages`

### Roles
- `super_admin`
- `clinic_admin`
- `doctor`
- `receptionist`
- `patient`

## Status

This repository currently contains bootstrap manifests and backend contracts to establish the monorepo and SaaS platform structure.
