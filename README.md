# Clinicall Monorepo

Production-oriented monorepo foundation for the Clinicall SaaS platform.

## Architecture

- `apps/backend` - Laravel API backend
- `apps/web` - Next.js web app
- `apps/mobile` - Expo React Native app
- `packages/shared` - shared TypeScript contracts and utilities
- `infra` - infrastructure-as-code and deployment support
- `.github/workflows` - CI workflows

## Conventions

- Package manager: `pnpm`
- Workspace layout: `apps/*` and `packages/*`
- Shared TypeScript alias: `@shared/*` -> `packages/shared/src/*`
- Keep app-specific business logic inside each app boundary
- Put cross-app contracts in `packages/shared`

## Root scripts

- `pnpm dev` - run all available app dev scripts in parallel
- `pnpm build` - run all available app build scripts
- `pnpm lint` - run all available app lint scripts
- `pnpm typecheck` - run all available app typecheck scripts
- `pnpm format` - format the repository with Prettier
- `pnpm format:check` - verify formatting without writing changes
- `pnpm check` - run lint, typecheck, and formatting checks

## Notes for contributors

This repository currently contains the shared monorepo foundation and app scaffolds. Domain logic, tenant middleware, APIs, and UI workflows are added in their respective folders in later phases.
