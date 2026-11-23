# AVO ERP — Enterprise Resource Planning System

A standard, production-ready, full-stack ERP architecture built with a **PHP backend** (MVC + Services + Casbin RBAC + Caching + PHPUnit) and a modern **React + Vite + TypeScript frontend** (shadcn/ui + Tailwind CSS + TanStack Query + Zustand + React Hook Form + Zod + Axios + Recharts + Lucide).

---

## 🏛️ Architecture & Division of Responsibilities

| Functionality | React Frontend (`/client`) | PHP Backend (`/backend`) | Implementation Notes |
| :--- | :---: | :---: | :--- |
| **Form display** | **Yes** | **No** | Accessible forms built with Tailwind CSS & shadcn/ui components |
| **Basic client validation** | **Yes** | **Yes** | Client uses React Hook Form + Zod; PHP validates input types & bounds |
| **Business validation** | **No** | **Yes** | Customer credit limits, positive pricing, and state validation in PHP |
| **Tax calculation** | **Preview only** | **Final calculation** | React calculates live preview; PHP computes authoritative final tax |
| **Stock availability** | **Display** | **Verify** | React shows badges; PHP verifies inventory & decrements stock |
| **Permissions** | **UI visibility** | **Enforce** | UI hides unauthorized actions; Casbin (`casbin/casbin`) enforces RBAC |
| **Approval workflow** | **Display actions** | **Control workflow** | `draft` → `pending_approval` → `approved` / `rejected` → `invoiced` |
| **Invoice generation** | **Display/download** | **Generate authoritative** | PHP generates cryptographic SHA-256 checksummed invoice |
| **Audit logs** | **Display** | **Record** | PHP records every mutation (actor, role, action, entity, diff, IP) |
| **Pagination** | **Display controls** | **Fetch records** | Frontend renders pagination bar; PHP executes `LIMIT / OFFSET` queries |

---

## 🚦 HTTP Status Codes

The backend standardizes on strict RESTful HTTP status codes:

| Status Code | Meaning | Usage in AVO ERP |
| :---: | :--- | :--- |
| **200** | Successful request | Fetched records, updated stock, approved orders, retrieved metrics |
| **201** | Created | Successfully created product, customer, sales order, or invoice |
| **400** | Invalid request | Malformed parameters or invalid workflow state transitions |
| **401** | Unauthorized | Missing or expired JWT authentication token |
| **403** | Permission denied | Casbin RBAC policy violation (e.g. Sales Rep deleting products) |
| **404** | Not found | Requested resource (order, product, customer) does not exist |
| **422** | Validation failed | Business rule failures (stock deficit, credit limit exceeded) |
| **500** | Server error | Unexpected runtime or internal exceptions |

---

## 🛡️ Identity, Roles & Access Control (SS6 — sarsspl.com/avo/identity.html)

Implemented directly from the enterprise authorization specification (`sarsspl.com/avo/identity.html`):

### 1. The Decoupled 3-Dimensional User Model (SS-23, SS-33, SS-35)
$$\text{User Access} = \text{Role Type} + \text{Scope} + \text{Level}$$
- **1. Role Type (10 Core Functions — SS-34):** `Executive`, `Finance`, `Sales`, `Service Field`, `Service Desk`, `Repair`, `Operations/Warehouse`, `Project`, `Admin`, `Customer`.
- **2. Scope (Data Boundary — RA-01):** `own` (self only), `region` (territory hub), `branch` (assigned warehouse/plant), `global` (enterprise-wide).
- **3. Level (Signing Authority — SS-33):** `L1` (standard operational actions), `L2` (regional exceptions & credit overrides), `L3` (CFO / Owner sign-offs & scrap disposal).

### 2. 14 Pre-Configured Organizational Archetype Templates (SS-35)
Every named organizational designation in AVO is configured as a 3-attribute tuple without software changes (Zero-Code Provisioning — SS-36):
1. **Executive / Managing Director:** `Executive` \| `global` \| `L3` (`admin@avoerp.com`)
2. **Chief Financial Officer (CFO):** `Finance` \| `global` \| `L3` (`cfo@avoerp.com`)
3. **Finance & Billing Executive:** `Finance` \| `branch` \| `L1` (`accountant@avoerp.com`)
4. **Regional Sales Head:** `Sales` \| `region` \| `L2` (`manager@avoerp.com`)
5. **Salesperson A (Tender Bidding):** `Sales` \| `own` \| `L1` (`sales@avoerp.com`)
6. **Salesperson B (Competing Integrator):** `Sales` \| `own` \| `L1` (`sales2@avoerp.com`)
7. **Field Service Engineer:** `Service Field` \| `own` \| `L1` (`field@avoerp.com`)
8. **Regional Service Coordinator:** `Service Desk` \| `region` \| `L2` (`coordinator@avoerp.com`)
9. **Central Helpdesk Agent:** `Service Desk` \| `branch` \| `L1` (`helpdesk@avoerp.com`)
10. **Repair Center Technician:** `Repair` \| `branch` \| `L1` (`repair@avoerp.com`)
11. **Repair Workshop Lead:** `Repair` \| `branch` \| `L2` (`repairlead@avoerp.com`)
12. **Warehouse Operator (Blind Scrap):** `Operations/Warehouse` \| `branch` \| `L1` (`clerk@avoerp.com`)
13. **Operations Hub Manager:** `Operations/Warehouse` \| `region` \| `L2` (`ops@avoerp.com`)
14. **Solar Project Engineer:** `Project` \| `region` \| `L2` (`project@avoerp.com`)

### 3. Special Security Gates
- **Chinese-Wall Bidding & Silent RFP Deduplication (RA-04, RA-05):**
  - Competing internal salespeople quoting different System Integrators for the same RFP (e.g., `#2026-NTPC-09`) are completely isolated. Salesperson A sees only Integrator Alpha; Salesperson B sees only Integrator Beta.
  - Background hash indexer detects duplicate RFP numbers and creates a silent link.
  - Surfaced only in Executive / Owner view; the company tender hit-rate counts once.
- **Warehouse Scrap Blind-Entry & Anti-Collusion Gate (RA-07, RA-08):**
  - Warehouse operators can record physical scrap disposal (`POST /api/scrap`).
  - Attempting to read past cumulative scrap ledgers (`GET /api/scrap`) is blocked with **`HTTP 403 Forbidden`** ("Blind Entry: Past scrap ledger masked to prevent collusion").
  - Only Executive / CFO / L3 can view the authoritative scrap recovery ledger and valuations.
- **Configurable Zero-Code Permission Matrix (SS-25, RA-09):**
  - Interactive grid covering Screens, Actions, and Levels saved to the database in real-time.
- **Zero-Trust API Layer Enforcement (SS-24):**
  - Casbin RBAC middleware enforces scope and level checks on every REST request.

---

## 🛡️ Casbin RBAC Roles & Permissions

Casbin model: `backend/config/rbac_model.conf`
Casbin policy: `backend/config/rbac_policy.csv`

| Role | Dashboard | Products | Customers | Orders | Approve Orders | Invoices | Audit Logs |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Admin** (`admin@avoerp.com`) | Full | Full | Full | Full | Yes | Full | Full |
| **Manager** (`manager@avoerp.com`) | Full | Read/Write | Read/Write | Read/Write | Yes | Read/Generate | Read |
| **Accountant** (`accountant@avoerp.com`) | Full | Read | Read | Read | No | Full / Pay | Read |
| **Sales Rep** (`sales@avoerp.com`) | Full | Read | Read/Create | Create / View | No | View | None |
| **Inventory Clerk** (`clerk@avoerp.com`) | None | Stock Adjust | None | View | No | None | None |

> *All demo passwords:* `admin123`, `manager123`, `accountant123`, `sales123`, `clerk123`.
> You can switch between roles directly in the top navigation bar with one click.

---

## 📂 Project Structure

```
avo_erp/
├── backend/
│   ├── composer.json               # Casbin & PHPUnit dependencies
│   ├── phpunit.xml                 # PHPUnit test suite config
│   ├── config/
│   │   ├── rbac_model.conf         # Casbin RBAC model definition
│   │   └── rbac_policy.csv         # Casbin role-to-resource policies
│   ├── database/
│   │   └── erp.sqlite              # Default SQLite database (MySQL supported)
│   ├── src/
│   │   ├── Core/
│   │   │   ├── Database.php        # PDO connection (SQLite / MySQL)
│   │   │   ├── Request.php         # HTTP request parser & Bearer token extractor
│   │   │   ├── Response.php        # Standard HTTP response helper (200, 201, 400, 401, 403, 404, 422, 500)
│   │   │   ├── Router.php          # RESTful routing engine with middleware support
│   │   │   ├── Auth/
│   │   │   │   ├── JwtService.php  # RFC 7519 HMAC-SHA256 JWT implementation
│   │   │   │   └── CasbinEnforcer.php # Casbin RBAC integration
│   │   │   ├── Cache/
│   │   │   │   ├── CacheInterface.php
│   │   │   │   ├── FileCache.php   # TTL file caching system
│   │   │   │   └── CacheManager.php
│   │   │   └── Middleware/
│   │   │       ├── CorsMiddleware.php
│   │   │       ├── AuthMiddleware.php
│   │   │       └── CasbinMiddleware.php
│   │   ├── Models/                 # User, Product, Customer, Order, OrderItem, Invoice, AuditLog
│   │   ├── Services/               # AuthService, ProductService, CustomerService, OrderService, InvoiceService, DashboardService
│   │   ├── Controllers/            # BaseController, AuthController, ProductController, CustomerController, OrderController, InvoiceController, AuditLogController, DashboardController
│   │   └── Database/
│   │       └── DatabaseSeeder.php  # Seed data for users, products, customers, orders, invoices
│   ├── tests/
│   │   └── Unit/
│   │       ├── TaxCalculationTest.php
│   │       ├── StockVerificationTest.php
│   │       ├── ApprovalWorkflowTest.php
│   │       ├── CasbinRbacTest.php
│   │       └── InvoiceGenerationTest.php
│   └── public/
│       ├── index.php               # Front controller
│       └── .htaccess               # Apache URL rewriting
└── client/
    ├── package.json
    ├── vite.config.ts              # Vite proxy to backend + Tailwind
    ├── tsconfig.json
    └── src/
        ├── api/                    # Axios client, interceptors & API services
        ├── components/ui/          # shadcn/ui components (Button, Badge, Card, Table, Dialog, Pagination, Input, Label)
        ├── layouts/                # DashboardLayout, Sidebar, Navbar (with 1-click Role Switcher)
        ├── lib/
        │   ├── utils.ts            # Formatting helpers & cn merge
        │   └── taxPreview.ts       # React client live tax preview
        ├── pages/
        │   ├── Login.tsx           # React Hook Form + Zod login
        │   ├── Dashboard.tsx       # Recharts analytics & KPI metrics
        │   ├── Products.tsx        # Inventory catalog & stock adjustment
        │   ├── Customers.tsx       # Customer accounts & credit limit display
        │   ├── Orders.tsx          # Sales order pipeline & approval workflow
        │   ├── OrderCreate.tsx     # Order creation with live tax & stock preview
        │   ├── Invoices.tsx        # Authoritative invoice viewer with checksum & PDF print
        │   └── AuditLogs.tsx       # State diff audit trail
        ├── store/
        │   └── authStore.ts        # Zustand auth state & role permission checkers
        ├── types/                  # TypeScript definitions
        ├── App.tsx                 # React Router v7 routes
        └── main.tsx
```

---

## 🚀 Running the Project

### 1. Start the PHP Backend Server
```bash
cd backend
php -S 127.0.0.1:8000 -t public
```

### 2. Run PHP Backend Unit Tests
```bash
cd backend
.\vendor\bin\phpunit
```

### 3. Start the React Frontend
```bash
cd client
npm run dev
```
Open [http://localhost:5173](http://localhost:5173) in your browser.

