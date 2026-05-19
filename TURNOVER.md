# COE Management System — Turnover Documentation

## Table of Contents

1. [System Purpose & Scope](#1-system-purpose--scope)
2. [User Roles & Permissions](#2-user-roles--permissions)
3. [Complete Process Flows](#3-complete-process-flows)
   - 3.1 [Employee: Submit a COE Request](#31-employee-submit-a-coe-request)
   - 3.2 [HR Admin / Supervisor: Approve or Reject a Request](#32-hr-admin--supervisor-approve-or-reject-a-request)
   - 3.3 [HR Admin / Supervisor: Bulk Approve or Reject](#33-hr-admin--supervisor-bulk-approve-or-reject)
   - 3.4 [Employee / Admin: Generate and Print a COE](#34-employee--admin-generate-and-print-a-coe)
   - 3.5 [Admin: Manage System Administrators](#35-admin-manage-system-administrators)
   - 3.6 [Admin: Add or Remove Purposes](#36-admin-add-or-remove-purposes)
   - 3.7 [User: Change Password](#37-user-change-password)
   - 3.8 [System: Authentication via SSO](#38-system-authentication-via-sso)
4. [Page-by-Page Guide](#4-page-by-page-guide)
5. [COE Document Types](#5-coe-document-types)
6. [Status Reference](#6-status-reference)
7. [File Attachments](#7-file-attachments)
8. [Filtering & Searching Records](#8-filtering--searching-records)
9. [Common Scenarios & Troubleshooting](#9-common-scenarios--troubleshooting)
10. [Developer Handoff Notes](#10-developer-handoff-notes)

---

## 1. System Purpose & Scope

The **COE Management System** is an internal web application that digitizes the end-to-end process of requesting, approving, and generating **Certificates of Employment**. Before this system, COEs were requested and approved manually via paper or informal channels.

### What the System Handles

- **Request intake**: Employees submit COE requests with purpose and file attachments
- **Approval workflow**: HR or supervisors review and approve/reject requests
- **Document generation**: Approved requests can be printed as formatted COE documents
- **Record keeping**: All requests and their history are searchable and stored

### What the System Does NOT Handle

- Payroll computation (salary data is fetched live from HRIS)
- Employee master data management (maintained in HRIS)
- Authentication (handled by the separate Authify SSO system)
- Physical document dispatch or tracking after printing

---

## 2. User Roles & Permissions

There are three functional roles in the system. Role assignment is automatic based on department and admin table membership.

### Role Comparison Table

| Capability | Employee | Supervisor | HR Admin |
|---|:---:|:---:|:---:|
| Submit COE request | ✓ | ✓ | ✓ |
| View own requests | ✓ | ✓ | ✓ |
| View direct reports' requests | — | ✓ | ✓ |
| View all requests | — | — | ✓ |
| Approve / Reject requests | — | ✓ (own team) | ✓ (all) |
| Bulk approve / reject | — | ✓ (own team) | ✓ (all) |
| Generate (print) COE | ✓ (own) | ✓ | ✓ |
| Delete own pending request | ✓ | ✓ | ✓ |
| Access Admin pages | — | — | ✓ (if in admin_list) |
| Add/remove system admins | — | — | ✓ (superadmin only) |

### How Roles Are Determined

```
Login → AuthMiddleware validates SSO token → emp_dept_id stored in session
                                                    │
                          ┌─────────────────────────┼──────────────────────────┐
                          │                          │                          │
                   Is emp in            Is emp dept = HR?              Does emp have
                   admin_list?          AND in admin_list?             direct reports?
                          │                          │                          │
                         Yes                        Yes                        Yes
                          │                          │                          │
                    System Admin               HR Admin Role              Supervisor Role
                  (admin pages only)         (see all records)         (see own + team)
```

**Note:** Being an HR Admin does NOT automatically grant admin page access — the employee must also be in the `admin_list` table. An employee can be a supervisor and still not be a system admin.

---

## 3. Complete Process Flows

### 3.1 Employee: Submit a COE Request

**Who can do this:** All authenticated users (employees, supervisors, HR admins)

**Step-by-step:**

```
1. Log in via SSO (Authify)
        │
        ▼
2. Navigate to "COE Records" in the sidebar
        │
        ▼
3. Click the "Request COE" (or "+ New Request") button
        │
        ▼
4. On the Create page:
   a. Employee information is auto-filled from session data
   b. Select "Purpose" from the dropdown (e.g., Loan, Visa, Employment Proof)
   c. Select "COE Type":
      - Without Compensation  (proves employment only)
      - With Compensation     (includes salary breakdown)
      - Inactive              (for separated employees — includes end date)
   d. (Optional) Upload a supporting document
      - Accepted: PDF, JPG, PNG
      - Max size: 5MB
        │
        ▼
5. Click "Submit"
        │
        ▼
6. System creates the record with status: PENDING (0)
        │
        ▼
7. Redirected to COE Records list
   Success toast: "Request submitted successfully"
```

**What happens after submission:**
- The request appears in the **Pending** tab of the COE Records table
- The assigned approver (HR admin or supervisor) will see it on their Pending tab
- The requestor waits for approval before generating the document

---

### 3.2 HR Admin / Supervisor: Approve or Reject a Request

**Who can do this:** HR Admins (all records) and Supervisors (own team's records only)

**Step-by-step — Approving:**

```
1. Navigate to "COE Records" → "Pending" tab
        │
        ▼
2. Find the record to approve (use search/filter if needed)
        │
        ▼
3. Click the "Actions" dropdown (⋮) on the row
        │
        ▼
4. Click "Approve"
        │
        ▼
5. Approve Dialog opens:
   - Optional: Enter remarks/notes
   - Click "Confirm Approve"
        │
        ▼
6. Record status changes: PENDING (0) → APPROVED (1)
   Record moves from "Pending" tab to "History" tab
   Success toast shown
```

**Step-by-step — Rejecting:**

```
1. Navigate to "COE Records" → "Pending" tab
        │
        ▼
2. Find the record
        │
        ▼
3. Click "Actions" dropdown → "Disapprove"
        │
        ▼
4. Disapprove Dialog opens:
   - REQUIRED: Enter reason/remarks
   - Click "Confirm Disapprove"
        │
        ▼
5. Record status changes: PENDING (0) → REJECTED (3)
   Record moves to "History" tab
   Remarks are stored and viewable by the requestor
```

**What the requestor sees after rejection:**
- Status badge shows "Rejected" (red)
- Can click "View Remarks" in the Actions dropdown to read the rejection reason

---

### 3.3 HR Admin / Supervisor: Bulk Approve or Reject

**Who can do this:** HR Admins and Supervisors, on the Pending tab only

**Step-by-step:**

```
1. Navigate to "COE Records" → "Pending" tab
        │
        ▼
2. Use checkboxes on the left column to select records:
   - Click individual checkboxes to select specific rows
   - Click the header checkbox to select ALL visible records
        │
        ▼
3. Bulk action bar appears at the top of the table:
   - Shows count of selected records
   - Two action buttons: "Approve Selected" | "Disapprove Selected"
        │
        ▼
4. Click the desired bulk action
        │
        ▼
5. Confirmation dialog appears — confirm the action
        │
        ▼
6. All selected records update simultaneously
   Success toast shows count of updated records
```

**Notes:**
- Bulk selection resets when switching tabs or when the data refreshes
- Bulk disapprove requires a single shared remark applied to all selected records
- Only records visible in the current filter/page are selectable (pagination applies)

---

### 3.4 Employee / Admin: Generate and Print a COE

**Who can do this:**
- The requestor (employee) — only for their own APPROVED records
- HR Admins and Supervisors — for any APPROVED or GENERATED record in their scope

**Step-by-step:**

```
1. Navigate to "COE Records"
        │
        ▼
2. Find the record with status "Approved"
   (appears in History tab after approval)
        │
        ▼
3. Click "Actions" dropdown → "Generate COE"
        │
        ▼
4. Generate COE Dialog opens:
   - Loading spinner while fetching data from HRIS...
   - Fetches: employee details, work history, salary data (if With Comp),
     approver info, operation director info
        │
        ▼
5. COE document preview renders in the dialog
   Shows the formatted certificate with employee data filled in
        │
        ▼
6. Click "Print"
        │
        ▼
7. Browser print dialog opens
   - The COE document is formatted for A4/Letter printing
   - Contains: company header, employee details, certification text,
     signature blocks (immediate head, HR manager, operation director)
        │
        ▼
8. User prints or saves as PDF
        │
        ▼
9. Record status automatically updates: APPROVED (1) → GENERATED (2)
```

**COE Document Contents by Type:**

| Field | Without Comp | With Comp | Inactive |
|---|:---:|:---:|:---:|
| Employee name | ✓ | ✓ | ✓ |
| Position/title | ✓ | ✓ | ✓ |
| Department | ✓ | ✓ | ✓ |
| Date hired | ✓ | ✓ | ✓ |
| Date of separation | — | — | ✓ |
| Employment status | ✓ | ✓ | ✓ |
| Basic salary | — | ✓ | — |
| 13th month pay | — | ✓ | — |
| Signature blocks | ✓ | ✓ | ✓ |

**Note:** If HRIS data is missing or invalid (e.g., no salary data for the employee class), the system will show an error and the COE cannot be generated until the HRIS data is corrected.

---

### 3.5 Admin: Manage System Administrators

**Who can do this:** System admins in `admin_list` (typically superadmin role)

**Adding a New Admin:**

```
1. Navigate to "Admin" in the sidebar
        │
        ▼
2. Current admin list is displayed
        │
        ▼
3. Click "Add New Admin"
        │
        ▼
4. Search for an employee by name or ID
   (Fetches from HRIS active employee list)
        │
        ▼
5. Select the employee from search results
        │
        ▼
6. Assign a role:
   - superadmin  — Full admin access
   - admin       — Standard admin
   - moderator   — Limited admin capabilities
        │
        ▼
7. Click "Add Admin"
   Record created in admin_list table
```

**Removing an Admin:**

```
1. On the Admin list page, find the admin to remove
        │
        ▼
2. Click "Remove" (or trash icon)
        │
        ▼
3. Confirm the action
        │
        ▼
4. Record deleted from admin_list
   Employee loses admin access immediately (on their next page navigation)
```

**Changing an Admin's Role:**

```
1. On the Admin list page, click the role dropdown or edit button
        │
        ▼
2. Select the new role
        │
        ▼
3. Confirm — admin_list record updated
```

---

### 3.6 Admin: Add or Remove Purposes

Purposes are the dropdown options in the COE request form (e.g., "For Bank Loan", "For Visa Application").

**These are managed directly in the `purpose` table** via database or a dedicated admin UI if implemented. Check the current admin routes for any purpose management endpoints.

---

### 3.7 User: Change Password

**Note:** This changes the password in the Authify SSO system. All sessions across all apps using Authify will be invalidated upon change.

```
1. Click your name/avatar in the top navigation bar
        │
        ▼
2. Navigate to "Profile"
        │
        ▼
3. Scroll to "Change Password" section
        │
        ▼
4. Enter:
   - Current password
   - New password
   - Confirm new password
        │
        ▼
5. Click "Change Password"
        │
        ▼
6. System validates and updates password in Authify
   User is logged out of all active Authify sessions
   Redirected to login page
```

---

### 3.8 System: Authentication via SSO

This process is invisible to users but important for developers/operators to understand.

```
User visits any COE URL
        │
        ▼
AuthMiddleware checks for sso_token
        │
   ┌────┴────────────────────────────────────────────┐
   │ Token found?                                     │ Token missing/invalid?
   ▼                                                  ▼
Query authify_sessions table                  Redirect to Authify login
(192.168.2.221)                               http://127.0.0.1:8001/login
        │                                     ?redirect={original_url}
   ┌────┴────┐                                        │
   │ Valid?  │                                   User logs in at Authify
   ▼         ▼                                         │
  Yes        No                                 Authify sets sso_token cookie
   │         │                                   Redirects back to COE
   │    Redirect to login                               │
   │                                             AuthMiddleware validates again
   ▼                                                    │
Store emp_data in PHP session                           ▼
Check maintenance mode                          Same flow as "Token found" →
Serve the requested page
```

**Maintenance Mode:**
- If `system_status.status = 'maintenance'`, all authenticated requests (except `/logout` and `/system-status`) are intercepted and show the maintenance message
- To take the system out of maintenance: update `system_status` table: `UPDATE system_status SET status='online' WHERE id=1`

---

## 4. Page-by-Page Guide

### Dashboard (`/{APP_NAME}/`)
- **Purpose:** Landing page after login
- **Visible to:** All authenticated users
- **Contents:** Welcome message, quick stats (if implemented)

### COE Records — List (`/{APP_NAME}/coe-records`)
- **Purpose:** View, filter, and manage COE requests
- **Visible to:** All authenticated users (scoped to role)
- **Tabs:**
  - **Pending** — Requests awaiting approval (status = 0). Admins see approve/reject actions here
  - **History** — All non-pending requests (status = 1, 2, 3, 5). Filter and view completed records
- **Filters available:**
  - Search by employee ID or purpose text
  - Filter by COE Type (Without Comp / Inactive / With Comp)
  - Sort by any column (click column header)
  - Rows per page (10, 25, 50, 100)

### COE Records — Create (`/{APP_NAME}/coe-records/create`)
- **Purpose:** Submit a new COE request
- **Visible to:** All authenticated users
- **Form fields:** Employee info (auto-filled), Purpose (dropdown), COE Type (radio), Attachment (file upload)

### Profile (`/{APP_NAME}/profile`)
- **Purpose:** View personal details and change password
- **Visible to:** All authenticated users
- **Data source:** Fetched from HRIS masterlist (read-only display)

### Admin — List (`/{APP_NAME}/admin`)
- **Purpose:** View and manage system administrators
- **Visible to:** System admins only (`admin_list` membership required)

### Admin — Add New (`/{APP_NAME}/new-admin`)
- **Purpose:** Search employees and grant admin access
- **Visible to:** System admins only

### Unauthorized (`/{APP_NAME}/unauthorized`)
- **Shown when:** User lacks permission to access a specific resource
- **Action:** Contains navigation link back to dashboard

---

## 5. COE Document Types

### Type 1: Without Compensation
**Use case:** Employee needs proof of employment without salary details (e.g., travel visa, residency applications)

**Contents:**
- Employee full name and gender prefix (Mr./Ms.)
- Position/job title
- Department/production line
- Date hired (formatted: "15th day of January 2020")
- Current employment status
- Certification statement
- Three signature blocks: Immediate Head, HR Manager, Operations Director

### Type 2: Inactive (For Separated Employees)
**Use case:** Former employee requesting proof of past employment

**Contents:** Same as Without Compensation PLUS:
- Date of separation from the company
- Modified certification text reflecting past employment

### Type 3: With Compensation
**Use case:** Employee needs employment proof including salary (e.g., bank loans, housing applications)

**Contents:** Same as Without Compensation PLUS:
- Basic monthly salary
- 13th month pay equivalent
- Salary is fetched live from HRIS at time of generation

---

## 6. Status Reference

| Status Code | Label | Color | Description |
|---|---|---|---|
| `0` | Pending / For Approval | Yellow/Orange | Newly submitted, awaiting HR/supervisor action |
| `1` | Approved | Green | Approved, requestor can now generate the COE |
| `2` | Generated | Blue | COE document has been printed/saved |
| `3` | Rejected / Disapproved | Red | Request denied; remarks contain the reason |
| `5` | Available for Claim | Purple | Reserved for future physical pickup workflow |

### Status Transitions

```
[Submit Request]    →  0 (Pending)
[Admin Approves]    →  1 (Approved)
[Admin Rejects]     →  3 (Rejected)
[Generate/Print]    →  2 (Generated)
```

Once a record reaches status `1`, `2`, or `3`, it appears in the **History** tab. Only status `0` appears in the **Pending** tab.

---

## 7. File Attachments

### Supported File Types
- PDF (`.pdf`)
- JPEG Image (`.jpg`, `.jpeg`)
- PNG Image (`.png`)

### Size Limit
- Maximum: **5MB per file**

### Storage Location
Files are stored in: `storage/app/public/coe_attachments/`

### Viewing Attachments
1. On any record row, click "Actions" → "View Attachments"
2. A modal lists all attachments for that record
3. Click "View" on any attachment to open the file in a new tab

### Security
- Files are referenced by a UUID (`file_id`), not by filename or path
- Direct file URLs are generated server-side via Laravel's `Storage::url()`

---

## 8. Filtering & Searching Records

### How Filters Work
All filters are stored in the URL as a base64-encoded JSON string in the `?q=` parameter.

Example URL:
```
/coe/coe-records?q=eyJzZWFyY2giOiIiLCJjb2VfdHlwZSI6IiIsInNvcnRfYnkiOiJkYXRlX3JlcXVlc3QiLCJzb3J0X2RpciI6ImRlc2MiLCJwZXJfcGFnZSI6MTAsInBhZ2UiOjF9
```

Decoded:
```json
{
  "search": "",
  "coe_type": "",
  "sort_by": "date_request",
  "sort_dir": "desc",
  "per_page": 10,
  "page": 1
}
```

### Available Filter Options

| Filter | Options | Behavior |
|---|---|---|
| Search | Free text | Matches employee ID or purpose text |
| COE Type | Any / 1 / 2 / 3 | Filters by `coe_type` column |
| Sort By | Any column header | Click column header to sort |
| Sort Direction | ASC / DESC | Click same header again to toggle |
| Per Page | 10, 25, 50, 100 | Controls pagination size |

### Clearing Filters
Click the "Clear Filters" button (if visible) or navigate to the page without a `?q=` parameter.

---

## 9. Common Scenarios & Troubleshooting

### "I submitted a request but I can't see it"
- Check that you are on the **Pending** tab (newly submitted records go there)
- If it was already acted on, check the **History** tab
- Your visibility scope may be limited — employees only see their own records

### "I can't generate the COE after it was approved"
- Ensure the record status is **Approved (1)**, not just Generated (2)
- If status is already Generated, you can re-generate from the History tab
- If HRIS data is missing (e.g., no salary data for the employee), the dialog will show an error — HR must correct the HRIS data first

### "The Generate COE dialog shows an error"
Possible causes:
- HRIS API is down or unreachable → Check `SERVICES_HRIS_URL` and network connectivity
- Employee data missing in HRIS (e.g., no salary record for the selected COE type)
- Contact the HRIS system administrator

### "An employee appears as 'Unknown' in the table"
- The system fetches employee names from HRIS via bulk API
- If HRIS is temporarily unreachable, names may show as "Unknown" or the employee ID
- This is a display-only issue; the actual data (employid) is correct

### "The system shows a maintenance page"
- The `system_status` table has `status = 'maintenance'`
- Only a database administrator can restore the system by running:
  ```sql
  UPDATE system_status SET status = 'online' WHERE id = 1;
  ```

### "I'm getting redirected to Unauthorized"
- You may be attempting to access an admin page without being in the `admin_list` table
- Contact a current system admin to grant you access

### "My SSO session expired in the middle of working"
- The Authify SSO token has a timeout period
- You will be redirected to the Authify login page automatically
- After logging in again, you'll be returned to the page you were on

### "A file attachment can't be opened"
- The file may have been moved or deleted from the server filesystem
- Check that the `storage:link` symlink is intact: `php artisan storage:link`
- Verify the file exists in `storage/app/public/coe_attachments/`

---

## 10. Developer Handoff Notes

### Key Files to Know

| Purpose | File Path |
|---|---|
| All COE business logic | `app/Services/CoeRecordService.php` |
| HRIS API calls | `app/Services/HrisApiService.php` |
| Auth middleware | `app/Http/Middleware/AuthMiddleware.php` |
| COE routes | `routes/coe.php` |
| Inertia shared props | `app/Http/Middleware/HandleInertiaRequests.php` |
| Main COE list page | `resources/js/Pages/CoeRecord/Index.jsx` |
| COE request form | `resources/js/Pages/CoeRecord/Create.jsx` |
| COE document templates | `resources/js/Pages/CoeRecord/components/CoeDocument.jsx` |
| Print/generate logic | `resources/js/Pages/CoeRecord/hooks/useGenerateCoe.js` |
| Filter state (Zustand) | `resources/js/Pages/CoeRecord/hooks/useCoeFilter.js` |
| Status helpers | `resources/js/Pages/CoeRecord/utils/statusHelpers.js` |
| COE formatting helpers | `resources/js/Pages/CoeRecord/utils/coeHelpers.js` |

### Adding a New COE Type
1. Add a new `coe_type` value constant in `statusHelpers.js` (`COE_TYPE_MAP`)
2. Create a new document template component in `CoeDocument.jsx`
3. Add routing logic in `GenerateCoeDialog.jsx` to render the new template
4. Update `HrisApiService` if new HRIS data is needed for the type
5. Update `CoeRecordService::getGenerateData()` to fetch and return the new data

### Adding a New Status
1. Add the status value and label in `statusHelpers.js` (`getStatusInfo()`)
2. Update `CoeRecordController::updateStatus()` validation to allow the new value
3. Update UI logic in `RowActions.jsx` and `Index.jsx` for new action availability

### Modifying the Approval Workflow
The status transition logic lives in:
- `CoeRecordService::updateStatus()` — single record
- `CoeRecordService::bulkUpdateStatus()` — batch records
- Both call `CoeRecordRepository::update()` with the new status value

### Adding a New Admin Role
1. Add the role string to `AdminController` validation
2. Add the role to the dropdown UI in the Admin pages
3. Add any role-specific permission checks in `AdminMiddleware` or controllers

### Environment Setup for a New Developer

```bash
# 1. Clone the repository
git clone <repo-url>
cd COE

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file and configure
cp .env.example .env
# Edit .env: set DB_*, MDB_*, ADB_*, SERVICES_HRIS_*, APP_NAME, etc.

# 5. Generate app key
php artisan key:generate

# 6. Run database migrations
php artisan migrate

# 7. Symlink storage for file access
php artisan storage:link

# 8. Start full development environment
composer run dev
# (Starts PHP :8004, queue worker, log stream, and Vite concurrently)
```

### Database Connections Setup

Ensure the following are accessible from the development machine:
- `192.168.1.28` (HRIS masterlist DB) — MySQL, port 3306
- `192.168.2.221` (Authify DB) — MySQL, port 3306
- Authify SSO app must be running at `http://127.0.0.1:8001`
- HRIS API must be running at the URL configured in `SERVICES_HRIS_URL`

### Running Tests

```bash
composer run test
# or directly:
./vendor/bin/pest
```

Tests use Pest framework. Integration tests connect to the real database — no mocking of DB connections.

### Deployment Checklist

- [ ] Run `npm run build` and commit or deploy compiled assets
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
- [ ] Verify all three database connections are reachable
- [ ] Verify HRIS API and Authify SSO are accessible
- [ ] Run `php artisan storage:link` on the production server
- [ ] Run `php artisan migrate` on the production database
- [ ] Set up a queue worker (for any queued jobs)
- [ ] Verify `system_status.status = 'online'` in the primary DB
- [ ] Test login flow end-to-end through Authify SSO

### Known Limitations & Notes

- **HRIS dependency**: COE generation fully depends on HRIS API availability. If HRIS is down, approved requests cannot be generated.
- **SSO dependency**: The system cannot function without the Authify SSO app running. There is no local login fallback.
- **Single attachment per request**: The data model supports one attachment file per COE request.
- **Print-based PDF**: COE documents are generated via browser print (CSS `@media print`). There is no server-side PDF generation — the formatted document is rendered in React and printed to PDF by the browser.
- **Salary at generation time**: Salary data in "With Compensation" COEs reflects the HRIS salary at the moment of printing, not at the time of request.
