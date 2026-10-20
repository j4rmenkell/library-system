# Cloud Deployment & Infrastructure as Code

[![ci](https://github.com/j4rmenkell/library-system/actions/workflows/ci.yml/badge.svg)](https://github.com/j4rmenkell/library-system/actions/workflows/ci.yml)

A project for learning Docker, Terraform and CI/CD by taking a web app from a laptop to production on Azure, with every piece of infrastructure defined as code.

**Live:** https://library.j4rmen.me

The app itself, _Marguerite's Athenaeum_, is a library system built for my 4th-year Laravel course: librarians manage books, members and loans, and readers browse the catalog and request books.

## What it covers

- **Docker:** a multi-stage build (Composer, then Node, then a slim PHP-FPM + Nginx runtime running as a non-root user), and Docker Compose with PostgreSQL for running the production setup locally.
- **Terraform:** three providers (Azure, Neon, Cloudflare) with remote state in HCP Terraform. It provisions Azure Container Apps, a serverless Neon PostgreSQL database (imported from an existing project), Cloudflare DNS with a managed HTTPS certificate, a managed identity with least-privilege access, and a budget alert.
- **CI/CD:** GitHub Actions runs the tests, builds the image and pushes it to GHCR tagged with the commit SHA, then deploys it to Azure. It logs in with OIDC, so no cloud credentials are stored anywhere.
- **Secrets:** the app key, admin password and database password are generated or passed by Terraform straight into Container App secrets. None of them are typed by hand or committed.

## Stack

| Layer          | Tools                                                   |
| -------------- | ------------------------------------------------------- |
| App            | Laravel 13, React 19, Inertia, TypeScript, Tailwind CSS |
| Container      | Docker, Docker Compose, serversideup/php                |
| Infrastructure | Terraform, HCP Terraform                                |
| Cloud          | Azure Container Apps, Neon PostgreSQL, Cloudflare DNS   |
| CI/CD          | GitHub Actions, GitHub Container Registry, OIDC         |

## Design decisions

- **Container Apps instead of AKS.** It scales to zero when idle, so the project costs nothing to keep running. Kubernetes would be overkill for one container.
- **Neon instead of Azure Database for PostgreSQL.** Serverless Postgres with a free tier that also suspends when idle. It runs in Singapore, the same region as the app.
- **Images tagged by commit SHA.** Every deploy points to the exact commit it was built from.
- **Terraform owns the infrastructure, CI/CD owns the app version.** Terraform ignores the image tag, so applying infrastructure changes never rolls back a deploy.
- **Book covers are Open Library URLs.** The app stores no files, so it needs no storage account.

## Repository layout

```
app/, resources/, routes/, ...   Laravel + React application
Dockerfile, compose.yaml         Container image and local production-like setup
infra/                           Terraform configuration
.github/workflows/ci.yml         Test, build, push and deploy pipeline
```

## Running it locally

With Docker:

```bash
docker compose up --build
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan books:import
```

Then open http://localhost:8080.

For development without Docker:

```bash
composer setup
composer run dev
```
