# Business Requirements Document

## Pharmaceutical ERP Platform

| Field | Value |
| --- | --- |
| Document status | Draft for pharma ERP implementation readiness |
| Prepared date | 11 July 2026 |
| System | LeafLight Pharma ERP |
| Application type | Web-based pharmaceutical ERP for procurement, inventory, warehouse transfers, batch traceability, and stock analytics |
| Primary jurisdiction | Pharmaceutical operations and inventory governance with stock/expiry visibility |
| Prepared from | Current Laravel codebase, route definitions, controllers, models, migrations, views, and feature regression tests |
| Version | 2.0 |

## 1. Executive Summary

LeafLight Pharma ERP is being evolved from the existing Laravel inventory and stock foundation into a pharmaceutical operations platform focused on procurement, receiving, inventory control, batch/expiry traceability, warehouse movement governance, and management analytics.

The production-ready pharma ERP must provide a reliable, auditable, and operationally controlled environment for businesses that require supplier onboarding, purchase order workflows, goods received notes, stock receipt handling, low-stock visibility, batch expiry tracking, and branch/warehouse transfer approvals.

This BRD defines the business objectives, scope, requirements, acceptance criteria, risks, and production readiness expectations for the current pharma ERP implementation path. It intentionally excludes fiscalisation and POS-only workflows from the implementation baseline.

## 2. Business Objectives

| Objective ID | Objective | Success Measure |
| --- | --- | --- |
| BO-01 | Support supplier and procurement master data management. | Approved suppliers can be created and referenced by purchase order workflows. |
| BO-02 | Create and manage purchase orders with line items and expected delivery dates. | Users can raise purchase orders and persist item level detail reliably. |
| BO-03 | Receive goods into stock through GRNs with batch and expiry capture. | Each GRN line can persist batch number, expiry date, quantity, and unit cost. |
| BO-04 | Maintain inventory quantity changes and stock auditability on receipt and transfer events. | Stock quantity changes are transactional, auditable, and visible through logs. |
| BO-05 | Govern warehouse and branch movement using transfer approval workflows. | Outgoing transfers are checked for stock sufficiency, approved, and reflected in stock and audit history. |
| BO-06 | Give management visibility into low stock and soon-to-expire batches. | Analytics surfaces restock recommendations and expiry watch results for operational action. |
| BO-07 | Support enterprise deployment with repeatable configuration and audit trails. | Production-ready migrations, route ownership, tests, and dashboard controls are stabilised. |

## 3. Scope

### 3.1 In Scope

- Supplier master management.
- Purchase order creation, edit, listing, and line-item persistence.
- Goods received note creation and storage of batch/expiry payloads.
- Inventory stock creation and quantity adjustment on receipt/transfer events.
- Branch and warehouse transfer workflows with approval, rejection, and cancellation states.
- Stock audit logging for receiving, transfer-in, transfer-out, and related operations.
- Analytics for low-stock and batch expiry visibility.
- Admin and authenticated user access with business-role-based visibility.
- Operational controls needed for production readiness: audit logging, route stability, test regression coverage, and deployment-safe configuration.

### 3.2 Out of Scope for Initial Production Release

- Fiscalisation and ZIMRA FDMS workflows.
- Full general ledger and accounting posting.
- Payroll and HR automation.
- Advanced CRM, promotions, or loyalty workflows.
- Offline-first multi-device branch sync beyond the current local web app workflow.
- Native mobile applications.

## 4. Stakeholders

| Stakeholder | Role in System | Primary Interests |
| --- | --- | --- |
| Business owner | Owns product outcomes and ERP operational readiness. | Procurement reliability, stock control, branch movement governance, and analytics visibility. |
| Procurement manager | Oversees supplier setup and purchase orders. | Supplier data quality, purchasing timelines, and order traceability. |
| Warehouse manager | Oversees goods receiving and inventory position. | GRN accuracy, batch entry, expiry visibility, stock movement control. |
| Branch manager | Oversees stock transfers across sites. | Approval workflow, branch availability, transfer auditability. |
| Finance user | Reviews costs, stock value, and operational reporting. | Cost visibility, cost-to-stock reconciliation, and operational KPIs. |
| System administrator | Configures users, routes, and stock-facing settings. | Security, operational stability, and system uptime. |
| Auditor | Reviews receipt, transfer, and stock movement history. | Immutable logs, traceability, and audit exports. |
| Support team | Troubleshoots bugs and operational incidents. | Route health, diagnostics, stable analytics, and reproducible workflows. |

## 5. Current System Overview

The application is a Laravel 11 platform using Blade views, Vite, Tailwind/Bootstrap-based frontend assets, Laravel Breeze-style authentication, queue infrastructure, Laravel Excel exports, and Eloquent-based inventory workflows.

The current pharma ERP baseline includes these major business areas:

- Supplier management: supplier master data, contact details, status, and onboarding.
- Procurement: purchase order creation, line-item persistence, and supplier linkage.
- Goods receiving: goods received note creation, batch and expiry capture, and GRN line persistence.
- Inventory control: stock master management, quantity updates on receiving, and audit trail visibility.
- Warehouse movement: stock transfer creation, approval, rejection, cancellation, and branch movement governance.
- Analytics: low-stock monitoring, restock recommendation visibility, and batch expiry watch.
- Audit history: stock audit logs for inventory events including stock receipt and transfer actions.

## 6. Assumptions

| ID | Assumption |
| --- | --- |
| A-01 | The pharma ERP implementation will focus on procurement, receiving, stock movement, and stock-control analytics rather than fiscalisation. |
| A-02 | Suppliers, stock, branches, and transfer workflows are managed through the existing Laravel/Eloquent architecture. |
| A-03 | Production users will access the system over HTTPS only. |
| A-04 | Administrators are responsible for onboarding users and assigning business roles. |
| A-05 | The system can use the same authenticated web app patterns for supplier, PO, GRN, and transfer workflows. |
| A-06 | Batch expiry and low stock visibility are operational control requirements for pharmaceutical inventory management. |
| A-07 | Existing seed data is for development/testing and must not be treated as production master data. |

## 7. Constraints

| ID | Constraint |
| --- | --- |
| C-01 | Inventory receipts must persist detailed item-level data such as batch number and expiry date when supplied. |
| C-02 | Stock quantity changes must remain auditable and traceable to a receiving or transfer event. |
| C-03 | Branch transfer approval must validate stock sufficiency for outgoing flows before updating stock. |
| C-04 | Supplier, PO, GRN, and transfer records must remain compatible with the current Laravel route and model pattern. |
| C-05 | Production secrets and credential material must not be stored in source control. |
| C-06 | The system must remain deployable with repeatable migrations and environment-safe configuration. |

## 8. User Roles and Permissions

| Role | Description | Core Permissions |
| --- | --- | --- |
| Guest | Unauthenticated visitor. | View login and public pages only. |
| Authenticated user | Standard business user. | Access operational ERP pages including supplier, PO, GRN, and stock-related workflows. |
| Supervisor | Operational manager. | Review approvals, branch movement, and stock-control actions. |
| Finance user | Reporting and reconciliation role. | Review analytics and stock value summaries. |
| Administrator | System owner/admin user. | Manage users, records, and system visibility. |
| Auditor | Read-only compliance role. | View historical records and audit trails without modifying operational inventory state. |

Production RBAC must be implemented as explicit permissions rather than relying only on a numeric `user_type` flag.

## 9. Business Process Requirements

### 9.1 User Access and Authentication

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-001 | The system shall require authenticated users for operational ERP functions. | Must | Unauthenticated users are redirected to login and cannot access supplier, purchasing, GRN, stock transfer, or analytics workflows. |
| BR-002 | The system shall support password login, email verification, password reset, and Google OAuth where enabled. | Must | Users can recover access securely; OAuth can be enabled or disabled by environment. |
| BR-003 | The system shall enforce role-based permissions for authenticated users, supervisors, finance users, administrators, and auditors. | Must | Permission checks are centralized and covered by regression tests. |
| BR-004 | The system shall maintain session security controls. | Must | Sessions expire after configured inactivity, cookies are secure/HTTP-only, and CSRF protection is enforced. |

### 9.2 Supplier and Procurement Management

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-010 | The system shall maintain supplier master records with contact details, TIN, address, and status. | Must | Supplier records can be created, indexed, and linked to purchase orders. |
| BR-011 | The system shall allow purchase orders to be raised with supplier, order date, expected delivery date, note, and line items. | Must | A PO record and its line items persist correctly in the database. |
| BR-012 | The system shall support purchase order listing and display of status and supplier linkage. | Must | Existing purchase orders are listable and visible to authorized users. |

### 9.3 Goods Receiving and Batch Traceability

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-020 | The system shall allow goods received notes to be created with supplier, receipt date, notes, and item payload. | Must | A GRN record can be created and its item lines persisted. |
| BR-021 | The system shall capture batch number and expiry date on GRN line items. | Must | Batch-tracked items keep their batch/expiry values in the permanent GRN item table. |
| BR-022 | The system shall update stock quantity when a GRN is received. | Must | Matching stock records increase by the exact received quantity and remain auditable. |
| BR-023 | The system shall store the received item payload in a durable line-item model. | Must | The posted item array is not replaced by a dummy placeholder record. |

### 9.4 Warehouse Movement Governance

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-030 | The system shall support stock transfer creation with source branch/destination branch, reference document, notes, and item lines. | Must | Transfers can be created, viewed, and approved by the workflow. |
| BR-031 | The system shall prevent outgoing transfers from exceeding current stock availability. | Must | Approval fails cleanly when insufficient stock exists. |
| BR-032 | The system shall update stock quantities after transfer approval. | Must | Approved outgoing transfers deduct stock; approved incoming transfers add stock. |
| BR-033 | The system shall record audit entries for transfer-in and transfer-out events. | Must | Stock audit logs capture before/after quantities and transfer reference links. |
| BR-034 | The system shall support transfer rejection and cancellation for workflow governance. | Should | Invalid or paused transfers can be rejected or cancelled without stock mutation. |

### 9.5 Inventory Visibility and Pharmaceutical Control

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-040 | The system shall expose low stock items in analytics. | Must | The analytics page lists products at or below the low-stock threshold. |
| BR-041 | The system shall calculate a recommended restock quantity for low-stock items. | Must | The UI presents a suggested order quantity aligned to current stock. |
| BR-042 | The system shall expose batches due to expire within the next 90 days. | Must | The expiry watch card lists product code, batch number, expiry date, days left, and received quantity. |
| BR-043 | The system shall allow stock quantity and expected stock risk to be reviewed from the analytics screen. | Should | Management can make procurement and branch replenishment decisions directly from analytics. |

### 9.6 Reporting and Analytics

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-050 | The system shall provide an admin analytics dashboard with operational stock and expiry visibility. | Must | The analytics page loads successfully for approved admin users. |
| BR-051 | The system shall support operational KPI views for revenue, transaction count, and stock value where available in the current dataset. | Should | The dashboard remains stable under the repository test database. |
| BR-052 | The system shall display low-stock and expiry risk information in the same analytics surface. | Must | Users can see both stock-risk signals without leaving the dashboard. |

### 9.7 Administration

| Req ID | Requirement | Priority | Acceptance Criteria |
| --- | --- | --- | --- |
| BR-060 | The system shall provide an admin dashboard for operational management. | Must | Administrator users can navigate to analytics, stock, and inventory operations. |
| BR-061 | The system shall allow authorized admins to manage users and operational records. | Must | Admin flows remain protected by auth and role checks. |
| BR-062 | The system shall provide account settings for user profile updates. | Must | Users can update profile data subject to validation. |

## 10. Integration Requirements

### 10.1 Core Application Integrations

| Req ID | Requirement |
| --- | --- |
| INT-001 | Application configuration must remain environment-safe and not hard-code production-specific operational values. |
| INT-002 | Supplier, PO, GRN, and stock transfer flows must remain connected through the existing Laravel route/controller/model architecture. |
| INT-003 | File and report exports must be permission-controlled and generated from trusted stored data. |
| INT-004 | Integration failures must produce user-safe messages and operational logs for support review. |

### 10.2 Email

| Req ID | Requirement |
| --- | --- |
| INT-010 | Email service must support verification and password reset notifications. |
| INT-011 | Production email credentials must be environment-managed and monitored for delivery failures. |

### 10.3 File Storage

| Req ID | Requirement |
| --- | --- |
| INT-020 | Generated exports, attachments, and operational files must be stored in approved locations. |
| INT-021 | Sensitive files must be protected from public web access unless explicitly intended. |

## 11. Data Requirements

### 11.1 Core Data Entities

| Entity | Description | Key Data |
| --- | --- | --- |
| User | Person accessing the system. | Name, email, password/OAuth ID, verification status, role, timestamps. |
| Supplier | External purchasing partner. | Name, contact person, phone, email, TIN, address, payment terms, status. |
| PurchaseOrder | Purchase order header. | PO number, supplier, order date, expected delivery date, status, notes. |
| PurchaseOrderItem | Purchase order line item. | Product code, description, quantity, unit cost, status. |
| GoodsReceivedNote | Goods receipt event. | GRN number, supplier, receipt date, PO reference, status, notes. |
| GoodsReceivedNoteItem | Received inventory line. | Product code, description, batch number, expiry date, qty received, unit cost, status. |
| Stock | Inventory master. | Product code, description, buying price, selling price, quantity, tax metadata, HS code. |
| Branch | Warehouse or site location. | Name, code, address, phone, active flag, home flag. |
| StockTransfer | Warehouse movement record. | Transfer number, type, source, destination, status, notes, reference doc. |
| StockAuditLog | Traceability log. | Product code, action, qty before/after, reference type/id, actor, timestamp. |

### 11.2 Data Governance Rules

| ID | Rule |
| --- | --- |
| DG-01 | Inventory records, transfer records, and audit logs must be retained according to approved operational policy. |
| DG-02 | Stock movement changes must be traceable to receiving or transfer events and must not be silently overwritten. |
| DG-03 | Personally identifiable user data must be minimized, protected, and retained only as required for business use. |
| DG-04 | Production secrets and credentials must be stored outside the repository using a secure secrets manager or encrypted storage. |
| DG-05 | All exports containing operational or personal data must be permission-controlled and audit-logged. |
| DG-06 | Backup copies must be encrypted and periodically restored in non-production to prove recoverability. |

## 12. Non-Functional Requirements

### 12.1 Availability and Reliability

| Req ID | Requirement | Target |
| --- | --- | --- |
| NFR-001 | Production uptime during business hours. | 99.5% minimum, excluding approved maintenance. |
| NFR-002 | Recovery Time Objective. | 4 hours or better for standard deployment. |
| NFR-003 | Recovery Point Objective. | 15 minutes or better for database-backed transactions. |
| NFR-004 | Inventory operation resilience. | Receiving and transfer failures are contained, auditable, and retry-safe within approved workflows. |
| NFR-005 | Concurrency protection. | Stock-related operations must remain transaction-safe under concurrent requests. |

### 12.2 Performance

| Req ID | Requirement | Target |
| --- | --- | --- |
| NFR-010 | Dashboard and analytics load time. | Under 3 seconds for typical operating data. |
| NFR-011 | Stock and product lookup response. | Under 500 ms for indexed product lookup under normal load. |
| NFR-012 | GRN and transfer write requests. | User-visible response under 10 seconds under normal load. |
| NFR-013 | Analytics and reporting generation. | Dashboard and operational exports remain responsive for normal transaction volumes. |

### 12.3 Security

| Req ID | Requirement |
| --- | --- |
| NFR-020 | Enforce HTTPS in production. |
| NFR-021 | Use CSRF protection for browser forms and secure session handling. |
| NFR-022 | Store passwords using strong one-way hashing. |
| NFR-023 | Encrypt or securely store credentials, tokens, and environment-sensitive values. |
| NFR-024 | Apply least-privilege RBAC for every module. |
| NFR-025 | Protect against unauthorized stock movement or administrative actions. |
| NFR-026 | Log security events including login failures, admin actions, permission denials, and export generation. |
| NFR-027 | Run dependency vulnerability scanning before production release and regularly after release. |

### 12.4 Auditability

| Req ID | Requirement |
| --- | --- |
| NFR-030 | Every stock movement event must be traceable to user, timestamp, reference record, and before/after quantity where practical. |
| NFR-031 | Admin actions must be audit-logged with actor, target, timestamp, IP, and before/after values where practical. |
| NFR-032 | Analytics and export views must be permission-controlled. |
| NFR-033 | Inventory edits and stock quantity changes must be controlled through approved workflows and audit notes. |

### 12.5 Maintainability

| Req ID | Requirement |
| --- | --- |
| NFR-040 | Business rules for stock movement, supplier, and GRN handling must be centralized and configurable. |
| NFR-041 | OpenAPI/Swagger documentation must be maintained for API endpoints where applicable. |
| NFR-042 | Automated tests must cover auth, RBAC, supplier workflows, GRN receipt flows, stock movement, and analytics visibility. |
| NFR-043 | Environment-specific configuration must be isolated in environment variables or managed config. |
| NFR-044 | Production deployments must be repeatable with migrations, build steps, cache optimization, and rollback process. |

### 12.6 Usability

| Req ID | Requirement |
| --- | --- |
| NFR-050 | ERP workflows must use clear validation, concise errors, and minimal required clicks. |
| NFR-051 | Operational errors must be translated into user-safe messages with clear remediation guidance. |
| NFR-052 | Admin and reporting screens must support filtering, pagination, and export controls. |
| NFR-053 | Analytics and operational views must remain readable and actionable for management and warehouse staff. |

## 13. Reporting Requirements

| Report | Audience | Filters | Output |
| --- | --- | --- | --- |
| Supplier list | Procurement, admin | Status, name, TIN | Web, CSV/Excel |
| Purchase order report | Procurement, finance | Supplier, date range, status | Web, CSV/Excel |
| GRN summary | Warehouse, finance | Supplier, date range, batch | Web, CSV/Excel |
| Stock valuation | Finance, manager | Product, branch, date | Web, CSV/Excel |
| Low-stock / restock report | Warehouse, manager | Branch, product, threshold | Web, CSV/Excel |
| Expiry watch report | Warehouse, quality, manager | Days remaining, batch, supplier | Web, CSV/Excel |
| Stock transfer audit report | Auditor, admin | Branch, status, date range | Web, CSV |

## 14. Compliance and Control Requirements

| Area | Requirement |
| --- | --- |
| Procurement traceability | Supplier, PO, and GRN records must be traceable to one another and remain auditable. |
| Batch traceability | Goods receipt batches must retain expiry date and batch number information for quality and recall governance. |
| Inventory movement integrity | Transfers must use approval workflows and stock audit logs to preserve movement evidence. |
| Data retention | Inventory, receiving, and movement records must meet approved operational retention policy. |
| Access control | Only authorized roles may create, approve, or export operational inventory records. |
| Secrets | Credentials and environment-sensitive secrets must be protected and rotated according to policy. |
| Privacy | User and business contact data must be collected and retained only for business/operational need. |

## 15. Production Readiness Requirements

| ID | Requirement | Go-Live Gate |
| --- | --- | --- |
| PR-001 | Keep environment-specific configuration outside the source tree and use managed config for deployment differentiators. | Required |
| PR-002 | Remove sensitive credentials, `.env` risk, and exposed secrets from source control. | Required |
| PR-003 | Use secure storage or managed secret reference for credentials and certificate material where required. | Required |
| PR-004 | Protect stock receipt and transfer write paths with transactional database patterns. | Required |
| PR-005 | Maintain immutable operational evidence through audit logs and controlled workflow states. | Required |
| PR-006 | Provide stable analytics visibility for low stock and expiry watch. | Required |
| PR-007 | Add persistent audit trail coverage for business-critical inventory actions. | Required |
| PR-008 | Add monitoring and alerting for failed imports, failed stock actions, and major operational exceptions. | Required |
| PR-009 | Add automated tests for auth, RBAC, supplier, procurement, GRN, transfer, and analytics workflows. | Required |
| PR-010 | Define backup, restore, retention, and disaster recovery runbooks. | Required |
| PR-011 | Configure production logging with correlation IDs and no sensitive payload leakage. | Required |
| PR-012 | Review legacy/demo code paths not intended for production use. | Required |
| PR-013 | Implement deployment checklist for migrations, queue workers, scheduler, storage links, caches, and asset builds. | Required |

## 16. Acceptance Criteria for Production Release

The system is production-ready when all the following are true:

1. A user can log in, view supplier, purchase order, and GRN workflows, and create a valid procurement/receiving record.
2. A warehouse user can receive goods into stock with batch and expiry data, and the matching stock quantity increases correctly.
3. A supervisor can create and approve a branch transfer with sufficient stock, and the stock and audit log update accordingly.
4. Management can view low-stock and expiry-control recommendations in analytics without an operational error.
5. Unauthorized users cannot access sensitive procurement, stock movement, or admin analytics functions.
6. Stock movement operations remain transaction-safe and auditable during concurrent or repeated testing.
7. Production secrets and credentials are outside source control and protected at rest.
8. Inventory and operational events are traceable through audit logs and controlled workflows.
9. Failed operational requests and import/export issues are visible to support and operations.
10. Database backups can be restored successfully within the agreed RTO/RPO.
11. Critical workflows have automated tests and UAT evidence.
12. Business owner, finance, technical owner, and operations approver sign off.

## 17. Risks and Mitigations

| Risk | Impact | Mitigation |
| --- | --- | --- |
| Inaccurate GRN receipt data. | Inventory mismatch and batch-risk exposure. | Validate item payloads and persist batch/expiry directly from the posted form. |
| Stock quantity not updating on receipt. | Operational inventory drift. | Apply stock increment on successful GRN receipt and audit it persistently. |
| Branch transfer approves without stock coverage. | Illogical stock movement and branch stock-outs. | Pre-validate availability and reject insufficient outgoing transfers. |
| Inadequate batch/expiry visibility. | Product quality and expiry control risk. | Surface expiry watch and restock recommendation cards in analytics. |
| Weak audit trail. | Poor investigation and compliance readiness. | Use stock audit logs for receiving and transfer operations. |
| Secrets committed or exposed. | Security breach and credential compromise. | Remove secrets from source control and use managed encrypted storage. |
| Broken analytics under test DB. | Operational dashboard instability. | Use SQLite-compatible SQL patterns and keep analytics regression coverage. |

## 18. Implementation Roadmap

### Phase 1 - Procurement and Receiving Foundation

- Supplier CRUD and master data management.
- Purchase order creation and item persistence.
- GRN intake with batch/expiry fields.
- Stock quantity update on receiving.
- Audit logging for receiving events.

### Phase 2 - Warehouse Governance and Visibility

- Stock transfer lifecycle and approval workflow.
- Branch movement audit and stock deduction rules.
- Low-stock analytics and restock recommendation view.
- Expiry-watch analytics and batch control visibility.

### Phase 3 - Enterprise Enhancements

- Multi-branch operational consolidation.
- Advanced stock movement analytics and branch comparisons.
- ERP integrations for finance and purchasing operations.
- Forecasting, replenishment automation, and quality controls.

## 19. Open Questions

| ID | Question | Owner |
| --- | --- | --- |
| Q-01 | What branch/warehouse operating model should be enabled at go-live? | Business owner |
| Q-02 | What statutory retention period should be configured for inventory, receiving, transfer, and audit records? | Finance/legal |
| Q-03 | Which procurement approval roles should exist beyond admin and standard user? | Business owner |
| Q-04 | Is a formal batch recall workflow required in the first ERP release? | Quality/operations |
| Q-05 | Should low-stock recommendations be fixed at a threshold or be configurable per product family? | Operations |
| Q-06 | Should expiry-watch alerts be email or dashboard-only in the first release? | Operations |

## 20. Glossary

| Term | Meaning |
| --- | --- |
| BRD | Business Requirements Document. |
| GRN | Goods Received Note. |
| PO | Purchase Order. |
| ERP | Enterprise Resource Planning. |
| Supplier | External party that provides stock items. |
| Stock transfer | Controlled movement of stock between branches or warehouses. |
| Batch number | Unique production/receipt batch identifier for traceability. |
| Expiry date | Product shelf-life date used for expiry and quality control. |
| Audit log | Chronological evidence of inventory movement or operational action. |
| RTO | Recovery Time Objective. |
| RPO | Recovery Point Objective. |

## 21. Sign-Off

| Role | Name | Decision | Date |
| --- | --- | --- | --- |
| Business owner | TBD | Pending | TBD |
| Finance/compliance owner | TBD | Pending | TBD |
| Technical owner | TBD | Pending | TBD |
| Operations/support owner | TBD | Pending | TBD |
| Security owner | TBD | Pending | TBD |

| Term | Meaning |
| --- | --- |
| BRD | Business Requirements Document. |
| FDMS | Fiscal Device Management System. |
| ZIMRA | Zimbabwe Revenue Authority. |
| Fiscal invoice | Primary fiscal receipt issued for a sale. |
| Credit note | Fiscal adjustment reducing or cancelling a prior fiscal invoice. |
| Debit note | Fiscal adjustment increasing or correcting a prior fiscal invoice. |
| Fiscal day | Regulated day/session during which fiscal receipts are issued for a device. |
| Receipt counter | Sequential number for receipts within the applicable fiscal context. |
| Receipt global number | Unique global receipt sequence number. |
| Hash chain | Cryptographic linkage between receipts using previous receipt signature hash. |
| QR URL | Fiscal verification URL encoded into a receipt QR code. |
| HS code | Harmonized System code used for product/tax classification. |
| RTO | Recovery Time Objective. |
| RPO | Recovery Point Objective. |

## 21. Sign-Off

| Role | Name | Decision | Date |
| --- | --- | --- | --- |
| Business owner | TBD | Pending | TBD |
| Finance/compliance owner | TBD | Pending | TBD |
| Technical owner | TBD | Pending | TBD |
| Operations/support owner | TBD | Pending | TBD |
| Security owner | TBD | Pending | TBD |

