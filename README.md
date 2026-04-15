# ClinicAll SaaS

ClinicAll is a production monorepo foundation for a multi-tenant clinic platform with:

- Laravel backend API in `backend/`
- Next.js web app in `web/`
- Expo React Native app in `mobile/`

## Workspace layout

- `backend/` — Laravel API foundation, tenancy and RBAC contracts, tenant-aware route structure
- `web/` — Next.js frontend workspace for public clinic websites, booking flows, clinic admin, and super admin
- `mobile/` — Expo app workspace for patient, doctor, receptionist, and admin mobile experiences

## Shared platform contracts

### API base path
- `/api/v1`

### Tenant middleware aliases
- `tenant.resolve`
- `tenant.enforce`
- `tenant.scope`

### RBAC middleware aliases
- `rbac.api`
- `rbac.web`

### Roles
- `super_admin`
- `clinic_admin`
- `doctor`
- `receptionist`
- `patient`

### Public schema tables
- `tenants`
- `domains`
- `plans`
- `subscriptions`
- `feature_flags`

### Tenant schema tables
- `users`
- `doctors`
- `patients`
- `appointments`
- `schedules`
- `invoices`
- `services`
- `settings`
- `website_pages`

## Root scripts

- `npm run dev` — starts the backend workspace dev entry
- `npm run dev:backend` — backend workspace dev entry
- `npm run dev:web` — web workspace dev entry
- `npm run dev:mobile` — mobile workspace dev entry
- `npm run build` — builds the frontend workspaces
- `npm run lint` — runs frontend lint workflows
- `npm run test` — runs backend test workflow
- `npm run bootstrap` — confirms monorepo foundation status

## Local development stack

`docker-compose.yml` provides:

- PostgreSQL 16
- Redis 7
- backend service
- web service
- mobile service

## Environment expectations

Each workspace should define its own environment file:

- `backend/.env`
- `web/.env.local`
- `mobile/.env`

Recommended shared values:

- backend API URL for web/mobile clients
- database credentials
- Redis connection
- S3-compatible storage endpoint and bucket
- tenant domain pattern
- super admin role/permission contract

## Status

This repository now contains the workspace foundation and shared SaaS contracts. Later implementation agents should build production application slices on top of these contracts without renaming the shared aliases, roles, or API base path.