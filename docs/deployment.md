# ClinicAll Deployment Guide

This repository is organized as a monorepo with three workspaces:

- `backend/` - Laravel API
- `web/` - Next.js web app
- `mobile/` - Expo React Native app

## Local infrastructure

The root `docker-compose.yml` provides the local development stack:

- PostgreSQL 16 for the database
- Redis 7 for queues, cache, and locks
- MinIO for S3-compatible object storage
- Backend API on `http://localhost:8000`
- Web app on `http://localhost:3000`
- Expo dev server on `http://localhost:8081`

## Environment setup

1. Copy `.env.example` to `.env`.
2. Fill in secrets if you are not using the defaults.
3. Make sure backend, web, and mobile workspace environment files mirror the root API/storage values if they need local overrides.

## Service endpoints

- Backend API: `http://localhost:8000/api/v1`
- Web app: `http://localhost:3000`
- MinIO API: `http://localhost:9000`
- MinIO console: `http://localhost:9001`
- Expo dev tools: `http://localhost:19002`

## Storage configuration

The stack is configured for S3-compatible object storage through MinIO.

Use these values in the backend environment:

- `FILESYSTEM_DISK=s3`
- `AWS_ENDPOINT=http://minio:9000`
- `AWS_USE_PATH_STYLE_ENDPOINT=true`
- `AWS_ACCESS_KEY_ID=clinicall`
- `AWS_SECRET_ACCESS_KEY=clinicall-secret`
- `AWS_BUCKET=clinicall`

## Running the stack

```bash
docker compose up -d postgres redis minio minio-init
docker compose up -d backend web mobile
```

## CI/CD conventions

GitHub Actions should validate:

- backend PHP syntax and tests
- web lint/build
- mobile lint/build/export
- workspace package and compose consistency

## Production deployment notes

For production, replace the local-only service images with hardened deployments:

- use managed PostgreSQL and Redis where available
- point object storage to S3-compatible cloud storage
- build backend and web images separately
- run backend workers and scheduler as dedicated processes
- set `APP_ENV=production` and disable debug mode
- ensure `APP_KEY`, database credentials, and S3 credentials are provided securely

## Health and readiness checks

Recommended application-level checks:

- backend: `GET /api/v1/health`
- web: Next.js server response on `/`
- mobile: Metro bundler availability for development only

If you add a dedicated backend health route, keep it lightweight and unauthenticated.