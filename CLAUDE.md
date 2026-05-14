# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12, Inertia.js (server-side adapter)
- **Frontend:** React 18, Vite 6, Tailwind CSS 3 + DaisyUI 5, Ant Design 6, Radix UI
- **State/Forms:** Zustand, React Hook Form
- **Database:** MySQL with multiple connections (local + remote HRIS + remote Authify SSO)
- **Testing:** Pest (Laravel integration)

## Development Commands

```bash
# Full dev environment (PHP server :8004, queue listener, log stream, Vite — all in parallel)
composer run dev

# Frontend only
npm run dev

# Production build
npm run build

# Run tests
composer run test
```

## Architecture Overview

This is a **Certificate of Employment (COE) management system** built as a Laravel + React SPA using Inertia.js. Laravel handles routing, auth, and data; React handles the UI. Inertia.js passes server-side data as props to React page components — there is no separate REST API consumed by the frontend.

### Backend Structure

- **Routes:** `routes/web.php` includes `auth.php`, `general.php`, and `coe.php`
- **Controllers → Services → Repositories:** Business logic lives in `app/Services/`, data access in `app/Repositories/`, HTTP handling in `app/Http/Controllers/`
- **Middleware:** `AuthMiddleware` validates SSO tokens; `AdminMiddleware` restricts admin routes
- **Shared props to frontend:** `app/Http/Middleware/HandleInertiaRequests.php` passes `emp_data`, `flash`, `auth`, `appName`, `display_name`

### Frontend Structure

- **Entry point:** `resources/js/app.jsx` — sets up Inertia, Ant Design theme, Sonner toasts, and resolves page components
- **Pages:** `resources/js/Pages/` — one file per route (e.g., `CoeRecord/Index.jsx`, `CoeRecord/Create.jsx`)
- **Layouts:** `AuthenticatedLayout` (sidebar + navbar) wraps authenticated pages; `GuestLayout` wraps login
- **Path alias:** `@/` maps to `resources/js/`

### Authentication

Authentication is handled externally by an **Authify SSO system** (not in this repo). The flow:

1. Unauthenticated users are redirected to `http://127.0.0.1:8001/login?redirect=...`
2. On return, `AuthMiddleware` validates the `sso_token` cookie against the `authify_sessions` table on the remote Authify DB
3. Employee session data (`emp_id`, `emp_name`, `emp_dept`, `emp_position`, etc.) is stored in the PHP session and passed to the frontend via Inertia shared props

### Multiple Database Connections

Configured in `config/database.php` and `.env`:
- `mysql` — primary app database (local)
- `masterlist` — remote HRIS database at `192.168.1.28` (employee data)
- `authify` — remote SSO database at `192.168.2.221` (auth sessions)

### COE Record Statuses

| Value | Label |
|-------|-------|
| `0` | Pending (For Approval) |
| `1` | Approved |
| `2` | Generated (PDF created) |
| `3` | Rejected/Disapproved |
| `5` | Available for Claim |

COE types: `1` = Without Compensation, `2` = Inactive, `3` = With Compensation.

### URL Filtering Pattern

Complex table filters are encoded as base64 in the query string (`?q=<base64>`). See `resources/js/hooks/useCoeFilter.js` and related logic.
