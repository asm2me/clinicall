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

## How to run

If `pnpm` is not installed on your machine:

### Option 1: Enable Corepack
```bash
corepack enable
corepack prepare pnpm@9.12.3 --activate
```

Then install dependencies:
```bash
pnpm install
```

### Option 2: Use npm to install pnpm globally
```bash
npm install -g pnpm
```

Then install dependencies:
```bash
pnpm install
```

Start local infrastructure:
```bash
docker-compose up -d
```

Start the workspace:
```bash
pnpm dev
```

Run only the web app:
```bash
cd apps/web && pnpm dev
```

Run only the mobile app:
```bash
cd apps/mobile && pnpm start
```

## Notes for contributors

This repository currently contains the shared monorepo foundation and app scaffolds. Domain logic, tenant middleware, APIs, and UI workflows are added in their respective folders in later phases.
