# COE Management System — Architecture Documentation

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Technology Stack](#2-technology-stack)
3. [Infrastructure & Environment](#3-infrastructure--environment)
4. [Application Layers](#4-application-layers)
5. [Database Architecture](#5-database-architecture)
6. [Authentication & Authorization](#6-authentication--authorization)
7. [Backend Architecture](#7-backend-architecture)
8. [Frontend Architecture](#8-frontend-architecture)
9. [External Integrations](#9-external-integrations)
10. [File Storage](#10-file-storage)
11. [Data Flow Diagrams](#11-data-flow-diagrams)
12. [Key Design Decisions](#12-key-design-decisions)

---

## 1. System Overview

The **Certificate of Employment (COE) Management System** is an internal web application that automates the request, approval, and generation of COE documents for employees. It replaces manual paper-based processes by providing a centralized platform where employees can submit requests, supervisors and HR can approve them, and approved COEs can be generated and printed as formatted documents.

**Core Responsibilities:**
- Accept COE requests from employees with supporting attachments
- Route requests through an approval workflow (pending → approved/rejected → generated)
- Generate printable COE documents of three types (Without Comp, With Comp, Inactive)
- Provide role-scoped visibility (HR admin / supervisor / employee)
- Integrate with external HRIS and SSO systems for employee data and authentication

---

## 2. Technology Stack

| Layer | Technology | Version |
|---|---|---|
| Backend Framework | Laravel | 12.0 |
| Language | PHP | 8.2+ |
| Frontend UI | React | 18.2 |
| SPA Bridge | Inertia.js | 2.0 |
| Build Tool | Vite | 6.2 |
| CSS Framework | Tailwind CSS + DaisyUI | 3.2 / 5.0 |
| UI Components | Ant Design | 6.0 |
| Icon Library | Lucide React | 0.555 |
| Toast Notifications | Sonner | 2.0 |
| Client State | Zustand | 5.0 |
| HTTP Client (server) | Laravel HTTP / Guzzle | — |
| HTTP Client (client) | Axios | — |
| Auth Package | Laravel Sanctum | 4.0 |
| Route Helper | Ziggy | 2.0 |
| Testing | Pest | — |

---

## 3. Infrastructure & Environment

### Network Topology

```
[Browser]
    |
    | HTTP
    v
[Laravel App Server :8004]  ─── local MySQL (primary DB)
    |
    |─── TCP 192.168.1.28   →  Remote HRIS MySQL (masterlist DB)
    |─── TCP 192.168.2.221  →  Remote Authify MySQL (SSO DB)
    |─── HTTP               →  HRIS API (configurable URL)
    |─── HTTP               →  Authify SSO App :8001
```

### Environment Variables

| Variable | Purpose |
|---|---|
| `APP_NAME` | URL prefix for all routes (e.g., `coe`) |
| `APP_DISPLAY_NAME` | UI display name |
| `SSO_COOKIE_NAME` | Cookie name holding the SSO token (default: `sso_token`) |
| `DB_*` | Primary application database credentials |
| `MDB_*` | HRIS masterlist database credentials |
| `ADB_*` | Authify SSO database credentials |
| `SERVICES_HRIS_URL` | Base URL of the HRIS REST API |
| `SERVICES_HRIS_KEY` | API key for HRIS API requests |
| `SERVICES_INTERNAL_KEY` | Key for internal service-to-service calls |

### Development Commands

```bash
composer run dev      # Starts PHP server :8004, queue listener, log stream, and Vite in parallel
npm run dev           # Frontend only (Vite HMR)
npm run build         # Production frontend build
composer run test     # Runs Pest test suite
```

---

## 4. Application Layers

The application follows a strict **Controller → Service → Repository** layered architecture.

```
HTTP Request
    │
    ▼
┌─────────────────────────────────────────────┐
│            Middleware Pipeline              │
│  AuthMiddleware → AdminMiddleware (admin)   │
│  HandleInertiaRequests                      │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│              Controller Layer               │
│  Validates input, orchestrates response,    │
│  calls Service layer, returns Inertia/JSON  │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│               Service Layer                 │
│  Business logic, data assembly,             │
│  external API calls (HRIS), file handling   │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│             Repository Layer                │
│  Database queries via Eloquent models,      │
│  CRUD operations, pagination                │
└──────────────────────┬──────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────┐
│               Model Layer                   │
│  Eloquent ORM — maps tables to PHP objects  │
└─────────────────────────────────────────────┘
```

### Responsibility Boundaries

| Layer | Does | Does NOT |
|---|---|---|
| Controller | Input validation, request parsing, response formatting | Business logic, direct DB queries |
| Service | Business rules, orchestration, external API calls | HTTP concerns, response formatting |
| Repository | All DB queries, Eloquent scoping | Business decisions |
| Model | Schema definition, relationships, casts | Queries beyond simple accessors |

---

## 5. Database Architecture

### Multiple Database Connections

```
config/database.php
├── mysql        → Primary app DB (local)        ← CoeRecord, Purpose, AdminList, etc.
├── masterlist   → HRIS DB (192.168.1.28)        ← Employee master data (read-only)
└── authify      → SSO DB (192.168.2.221)        ← Session token validation (read-only)
```

### Primary Database Schema

**`coe_record`**
```
id              — Auto increment PK
employid        — Employee number (from HRIS)
emp_position    — Stored at time of request
emp_class       — Employee classification (numeric)
emp_sex         — M/F code
purpose         — FK or text of COE purpose
date_request    — Date of request
coe_type        — 1=Without Compensation, 2=Inactive, 3=With Compensation
status          — 0=Pending, 1=Approved, 2=Generated, 3=Rejected, 5=Available for Claim
remarks         — Disapproval notes or admin comments
pcn_status      — Position control number status
created_at      — Laravel timestamp
updated_at      — Laravel timestamp
```

**`attachment_files`**
```
id                  — Auto increment PK
file_id             — UUID (public-safe identifier)
employid            — Owning employee
record_id           — FK → coe_record.id
original_file_name  — Original upload filename
file_location       — Relative path in storage
file_name           — Stored filename (may differ from original)
file_type           — MIME type
file_size           — Bytes
date_filed          — Upload timestamp
```

**`purpose`**
```
id                  — Auto increment PK
purpose             — Purpose label text
created_by_emp_num  — Who created it
date_created        — Creation timestamp
updated_by_emp_num  — Who last updated it
date_updated        — Update timestamp
```

**`admin_list`**
```
id                  — Auto increment PK
admin_id            — Employee number
emp_role            — superadmin | admin | moderator
last_updated_by     — Employee number of updater
created_by_emp_num  — Creator
date_created
updated_by_emp_num
date_updated
```

**`system_status`**
```
id      — Always 1 (single-row config)
status  — online | maintenance
message — Maintenance message to display
updated_at
```

### COE Status Flow

```
                    ┌───────────────────────────────┐
                    │         0 = Pending             │  ← Initial state on creation
                    └───────────┬──────────┬─────────┘
                                │          │
                         [Approve]    [Reject/Disapprove]
                                │          │
                                ▼          ▼
                    ┌───────────────┐  ┌──────────────────┐
                    │  1 = Approved │  │  3 = Rejected     │
                    └───────┬───────┘  └──────────────────┘
                            │
                       [Generate]
                            │
                            ▼
                    ┌───────────────┐
                    │ 2 = Generated │
                    └───────────────┘

  (5 = Available for Claim — reserved for future pickup workflow)
```

---

## 6. Authentication & Authorization

### SSO Authentication Flow

```
[Browser]                [COE App]               [Authify SSO :8001]       [Authify DB]
    │                        │                           │                      │
    │  GET /coe/...           │                           │                      │
    ├───────────────────────►│                           │                      │
    │                        │ AuthMiddleware runs       │                      │
    │                        │ Check sso_token cookie    │                      │
    │                        │ ─────────────────────────────────────────────► │
    │                        │                           │  Query session table  │
    │                        │ ◄───────────────────────────────────────────── │
    │                        │                           │  (token valid/expired)│
    │  If invalid/missing:    │                           │                      │
    │  Redirect to SSO login  │                           │                      │
    │ ◄─────────────────────  │                           │                      │
    │                        │                           │                      │
    │  GET /login?redirect=..│                           │                      │
    ├───────────────────────────────────────────────────►│                      │
    │  [User logs in]         │                           │                      │
    │ ◄─── Redirect back ─────────────────────────────── │                      │
    │  (sso_token cookie set) │                           │                      │
    │                        │                           │                      │
    │  GET /coe/... (with cookie)                         │                      │
    ├───────────────────────►│                           │                      │
    │                        │ Validates token again     │                      │
    │                        │ Stores emp data in session│                      │
    │                        │ Serves page               │                      │
    │ ◄─────────────────────  │                           │                      │
```

### Token Validation Priority

`AuthMiddleware` checks for the SSO token in this order:
1. `?key=` query parameter (internal service calls via `X-Internal-Key` header)
2. `sso_token` cookie (normal browser session)
3. PHP `$_SESSION` (already authenticated this session)

### Session Data Stored After Auth

```php
session([
    'token'           => $token,
    'emp_id'          => $emp_id,
    'emp_name'        => $full_name,
    'emp_firstname'   => $first_name,
    'emp_dept_id'     => $dept_id,
    'emp_jobtitle_id' => $jobtitle_id,
    'emp_prodline_id' => $prodline_id,
    'emp_position_id' => $position_id,
    'emp_station_id'  => $station_id,
    'shift_type'      => $shift_type,
    'team'            => $team,
    'generated_at'    => now(),
])
```

### Role-Based Authorization

| Role | How Determined | Access Scope |
|---|---|---|
| HR Admin | In `admin_list` table AND `emp_dept_id` = Human Resources dept | All records from all employees |
| Supervisor | Has subordinates in HRIS (direct reports list is non-empty) | Own records + direct reports' records |
| Employee | Default (none of the above) | Own records only |
| System Admin | In `admin_list` with any role | Admin management pages |

**Scope Logic in `CoeRecordService::getEmpScope()`:**
```
if (user is HR admin)
    → no filter (see everything)
else if (user has direct reports)
    → filter: employid IN [self + subordinates]
else
    → filter: employid = self only
```

---

## 7. Backend Architecture

### Route Structure

```
routes/web.php
├── include routes/auth.php
│   └── /{APP_NAME}/logout                  GET   → AuthenticationController@logout
│   └── /{APP_NAME}/unauthorized            GET   → Unauthorized page
│
├── include routes/general.php
│   └── /                                   redirect → /{APP_NAME}
│   └── /{APP_NAME}                         GET   → DashboardController@index
│   └── /{APP_NAME}/profile                 GET   → ProfileController@index
│   └── /{APP_NAME}/change-password         POST  → ProfileController@changePassword
│   └── /{APP_NAME}/admin          [admin]  GET   → AdminController@index
│   └── /{APP_NAME}/new-admin      [admin]  GET   → AdminController@index_addAdmin
│   └── /{APP_NAME}/add-admin      [admin]  POST  → AdminController@addAdmin
│   └── /{APP_NAME}/remove-admin   [admin]  POST  → AdminController@removeAdmin
│   └── /{APP_NAME}/change-admin-role [admin] PATCH → AdminController@changeAdminRole
│
└── include routes/coe.php
    └── /{APP_NAME}/coe-records              GET   → CoeRecordController@index
    └── /{APP_NAME}/coe-records/create       GET   → CoeRecordController@create
    └── /{APP_NAME}/coe-record               POST  → CoeRecordController@store
    └── /{APP_NAME}/coe-record/{id}/status   PUT   → CoeRecordController@updateStatus
    └── /{APP_NAME}/coe-record/{id}          DELETE → CoeRecordController@destroy
    └── /{APP_NAME}/coe-record/{id}/generate-data GET → CoeRecordController@generateData
    └── /{APP_NAME}/coe-record/{id}/attachments   GET → CoeRecordController@getAttachments
    └── /{APP_NAME}/coe-records/bulk-status  PUT   → CoeRecordController@bulkUpdateStatus
```

All routes under `/{APP_NAME}` require `AuthMiddleware`. Admin sub-routes additionally require `AdminMiddleware`.

### Controller Responsibilities

**`CoeRecordController`** — Primary controller
- `index()`: Decodes `?q=` base64 filters, determines user scope, paginates records, returns Inertia page
- `create()`: Loads purposes and current employee data, returns Inertia page
- `store()`: Validates form input, delegates to service for creation + file handling
- `updateStatus()`: Validates status value, calls service to update single record
- `bulkUpdateStatus()`: Validates array of IDs, calls service for batch update
- `destroy()`: Deletes record and linked attachments
- `generateData()`: Returns JSON of employee + salary data for in-browser COE preview
- `getAttachments()`: Returns JSON list of attachment files for a record

**`AdminController`** — Admin management
- `index()`: Lists current admins with roles
- `index_addAdmin()`: Paginated employee list for admin addition
- `addAdmin()`, `removeAdmin()`, `changeAdminRole()`: Admin CRUD operations

**`DashboardController`** — Simple dashboard landing

**`ProfileController`** — User profile view and password change

**`AuthenticationController`** — Logout (redirects to Authify logout URL)

### Key Services

**`CoeRecordService`**
- Central business logic for all COE operations
- Calls `HrisApiService` for employee data enrichment
- Calls `AttachmentFileService` for file operations
- Calls `DataTableService` for filter/pagination assembly

**`HrisApiService`**
- Wraps all HTTP calls to the HRIS REST API
- Handles authentication headers (`X-Internal-Key`)
- Methods: `fetchWorkDetails()`, `fetchSalaryData()`, `fetchDirectReports()`, `fetchEmployeesBulk()`, etc.

**`DataTableService`**
- Generic server-side table engine
- Supports: search, date range filter, sorting, pagination, CSV export, join support
- Used for admin pages and generalized data display

### Inertia Shared Props

Every page render via Inertia receives these shared props from `HandleInertiaRequests`:

```javascript
{
  emp_data: {
    emp_id, emp_name, emp_firstname,
    emp_dept_id, emp_jobtitle_id, emp_prodline_id,
    emp_position_id, emp_station_id, shift_type, team
  },
  flash: { success, error },
  auth: { /* current user object */ },
  appName: "coe",
  display_name: "COE System"
}
```

---

## 8. Frontend Architecture

### Application Entry Point (`resources/js/app.jsx`)

```
app.jsx
├── createInertiaApp()         — Resolves page components, sets up React root
├── ThemeProvider              — Dark/light mode context
├── Ant Design ConfigProvider  — Theme algorithm (default/dark)
├── Sonner <Toaster>           — Global toast notifications
└── Stores authify token       — Saves SSO token to localStorage on load
```

### Page Resolution

Inertia resolves page components by name:
```
"CoeRecord/Index"  →  resources/js/Pages/CoeRecord/Index.jsx
"CoeRecord/Create" →  resources/js/Pages/CoeRecord/Create.jsx
"Admin"            →  resources/js/Pages/Admin.jsx
"Dashboard"        →  resources/js/Pages/Dashboard.jsx
"Profile"          →  resources/js/Pages/Profile.jsx
"404"              →  resources/js/Pages/404.jsx
"Unauthorized"     →  resources/js/Pages/Unauthorized.jsx
```

### Layout Structure

```
AuthenticatedLayout
├── <Sidebar>           — Vertical navigation links
│   ├── Dashboard
│   ├── COE Records
│   └── Admin (if admin)
├── <NavBar>            — Top bar with user info, theme toggle, logout
└── <main>              — Page content slot (children)
    └── {page component}

GuestLayout
└── Centered card for login redirect page
```

### Component Hierarchy (COE Records)

```
CoeRecord/Index.jsx
├── Tabs (Pending | History)
├── FilterBar
│   ├── Search input
│   ├── COE Type select
│   └── Rows per page select
├── Bulk Action Bar (admin only, pending tab)
│   ├── Select all checkbox
│   ├── Approve Selected button
│   └── Disapprove Selected button
├── <table>
│   ├── <SortableHead> (column headers with sort)
│   ├── <TableSkeleton> (loading state)
│   └── Rows
│       ├── <StatusBadge>
│       └── <RowActions> dropdown
│           ├── Approve → <ApproveDialog>
│           ├── Disapprove → <DisapproveDialog>
│           ├── View Remarks → <RemarksDialog>
│           ├── Generate COE → <GenerateCoeDialog>
│           └── View Attachments → <AttachmentModal>
└── Pagination controls
```

### State Management

**Filter State** — Zustand store (`useCoeFilters`)
```javascript
{
  search: "",
  coe_type: "",
  sort_by: "date_request",
  sort_dir: "desc",
  per_page: 10,
  page: 1
}
// Serialized as base64 JSON in URL: ?q=<base64>
// Server reads, applies, and passes back as props
```

**Bulk Selection** — Local React state (`useBulkSelection`)
```javascript
selectedIds: Set<number>  // Set of selected record IDs
```

**COE Generation** — Custom hook (`useGenerateCoe`)
```javascript
// Fetches /coe-record/{id}/generate-data on dialog open
// Creates hidden print portal
// Calls window.print() on confirm
// Marks record as Generated via status update
```

### Path Aliases

`@/` resolves to `resources/js/`, enabling clean imports:
```javascript
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout"
import StatusBadge from "@/Pages/CoeRecord/components/StatusBadge"
```

### Theme System

- `ThemeContext` provides `{ theme, toggleTheme }` globally
- Ant Design switches between `theme.defaultAlgorithm` and `theme.darkAlgorithm`
- Sonner toaster syncs `theme` prop
- Tailwind classes use `dark:` variants

---

## 9. External Integrations

### Authify SSO

| Aspect | Detail |
|---|---|
| Type | HTTP redirect-based SSO |
| Login URL | `http://127.0.0.1:8001/login?redirect={return_url}` |
| Logout URL | `http://127.0.0.1:8001/logout?token={token}&redirect={return_url}` |
| Validation | Direct DB query to `authify_sessions` table on `192.168.2.221` |
| Token location | Cookie (`sso_token` by default), also stored in PHP session |
| Access control | `emp_from` field in session record controls which apps an employee can access |

### HRIS API

| Aspect | Detail |
|---|---|
| Type | Internal REST API |
| Auth | `X-Internal-Key` header |
| Base URL | Configured via `SERVICES_HRIS_URL` env var |

**Endpoints Used:**

| Endpoint | Method | Purpose |
|---|---|---|
| `/api/employees/{id}` | GET | Fetch single employee name |
| `/api/employees/{id}/work` | GET | Work details (status, prodline, hire date, position) |
| `/api/employees/{id}/salary` | GET | Salary breakdown for COE with compensation |
| `/api/employees/operation-director` | GET | Fetch operation director info |
| `/api/employees/active` | GET | Paginated + searchable active employee list |
| `/api/employees/direct-reports/{id}` | GET | Subordinate list for supervisors |
| `/api/employees/bulk` | POST | Batch fetch multiple employees by ID array |

---

## 10. File Storage

Attachments are stored on the server filesystem under Laravel's storage system.

```
storage/
└── app/
    └── public/
        └── coe_attachments/
            └── {file_name}   ← Actual uploaded files
```

Files are symlinked to `public/storage/` via `php artisan storage:link`, making them accessible via browser.

**File Handling Flow:**
1. User uploads file on Create page (drag-drop or browse)
2. Laravel validates type (PDF, JPG, PNG) and size (max 5MB)
3. `CoeRecordService::handleAttachment()` stores file and creates `attachment_files` record
4. `file_id` (UUID) is used as the public reference (never the raw path)
5. View link resolves via `Storage::url($file_location)`

---

## 11. Data Flow Diagrams

### COE Request Submission

```
[Employee Browser]
       │
       │ POST /coe-record
       │ {coe_type, purpose, attachment}
       ▼
[CoeRecordController::store()]
       │ Validate input
       ▼
[CoeRecordService::createRequest()]
       │
       ├──► [AttachmentFileService::handleAttachment()]
       │         │ Store file to disk
       │         │ Insert attachment_files record
       │         └─────────────────────────────┐
       │                                       │
       ├──► [CoeRecordRepository::create()]    │
       │         │ Insert coe_record row        │
       │         └─────────────────────────────┘
       │
       ▼
[Inertia redirect → /coe-records]
[Flash: "Request submitted successfully"]
```

### COE Generation (Print)

```
[Admin/Employee Browser]
       │
       │ Click "Generate COE"
       ▼
[GenerateCoeDialog opens]
       │
       │ GET /coe-record/{id}/generate-data
       ▼
[CoeRecordController::generateData()]
       │
       ▼
[CoeRecordService::getGenerateData()]
       │
       ├──► [HrisApiService::fetchWorkDetails()]   → HRIS API
       ├──► [HrisApiService::fetchSalaryData()]    → HRIS API (if With Comp)
       ├──► [HrisApiService::fetchApprovers()]     → HRIS API
       └──► [HrisApiService::fetchOperationDirector()] → HRIS API
       │
       ▼
[JSON response: empData + salaryData + approvers]
       │
       ▼
[React: Render COE document in hidden print portal]
       │
       │ User clicks "Print"
       ▼
[window.print()]
       │
       │ PUT /coe-record/{id}/status {status: 2}
       ▼
[Record marked as "Generated"]
```

### Filter/Pagination Flow

```
[Browser URL] ?q=<base64({ search, coe_type, sort_by, sort_dir, per_page, page })>
       │
       │ GET /coe-records?q=...
       ▼
[CoeRecordController::index()]
       │ Decode base64 → filter array
       ▼
[CoeRecordService::getPaginatedRecords()]
       │
       ├── getEmpScope()   → determine visible employee IDs
       ▼
[DataTableService / CoeRecordRepository::paginate()]
       │ Apply filters, sort, paginate
       ▼
[Inertia::render('CoeRecord/Index', { records, filters, pagination })]
       │
       ▼
[React: useCoeFilters hydrates Zustand from server props]
[Any filter change → router.get() with new base64 ?q=]
```

---

## 12. Key Design Decisions

### Why Inertia.js Instead of a REST API?

The system is a small internal tool where the backend always controls navigation. Inertia eliminates the need for a separate REST API layer — Laravel renders pages server-side, passes data directly as React props. This simplifies authentication (session-based, no JWT management in frontend) and reduces boilerplate while still providing a single-page app experience.

### Why Base64-Encoded Filter Params?

Complex filter state (multiple fields, sort, pagination) encoded as base64 JSON keeps the URL clean, avoids multi-param encoding issues, and allows the server to safely decode a single parameter. The encoded state is also easy to share/bookmark.

### Why Three Database Connections?

The COE system is one of several internal apps in the ecosystem. Employee master data lives in a central HRIS database, and auth sessions live in a central Authify database. Rather than duplicating data, the COE app reads directly from these sources — ensuring data is always current without sync jobs.

### Why Repository Pattern?

The repository pattern decouples business logic (services) from data access (repositories). This makes services testable with mock repositories and allows future database changes (e.g., swapping Eloquent for a query builder) without touching service code.

### COE Status as Integer Enum

Statuses are stored as integers (`0, 1, 2, 3, 5`) rather than strings to save space and allow numeric comparisons. Status `5` ("Available for Claim") is non-sequential intentionally — it was added later to support a future physical pickup workflow without renumbering existing statuses.
