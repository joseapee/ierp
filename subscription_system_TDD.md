Technical Design Document
Subscription, Billing & Onboarding System
System: Multi-Tenant Inventory / Manufacturing / CRM ERP
Architecture: Shared Database – Shared Schema (SDSS)
Billing Cycles: Monthly and Annual Only
Payment Gateway: Paystack
1. System Purpose
This subsystem manages:
Tenant registration
Workspace creation
Subscription plans
Billing and payments
Subscription lifecycle
Feature access enforcement
Guided onboarding
The subsystem controls platform access for all tenants using the ERP.

2. Architectural Context
The ERP platform uses Shared Database – Shared Schema architecture.
All tenants share the same database and are logically isolated using tenant_id.
Platform Database
│
├── tenants
├── users
├── plans
├── subscriptions
├── payments
├── invoices
└── usage_metrics

Application Modules
│
├── Inventory
├── Manufacturing
├── CRM
├── Accounting
└── HRM

Each module must include:
tenant_id

in all tenant-scoped tables.
3. Core Concepts
Tenant
Represents a business workspace using the ERP.
Example:
Blackrock Furniture
ABC Fashion
Elite Restaurant

Each tenant has:
Owner/Admin
Users
Subscription
ERP data
Plan
Defines a subscription package.
A plan determines:
Available modules
Feature access
Usage limits
Billing price
Example plans:
Starter
Business
Professional
Enterprise
Subscription
Represents an active or inactive billing agreement between a tenant and the platform.
A subscription contains:
tenant_id
plan_id
billing_cycle
start_date
end_date
status
4. Billing Cycles
The system supports two billing cycles only.
Monthly
Billing interval = 30 days
Example pricing:
Starter: NGN25,000/month
Business: NGN50,000/month
Professional: NGN70,000/month
Annual
Billing interval = 365 days
Example pricing:
Starter: NGN250,000/year
Business: NGN500,000/year
Professional: NGN700,000/year

Annual plans provide a discount incentive.
5. Subscription Lifecycle
A subscription moves through defined states.
TRIAL
ACTIVE
PAST_DUE
GRACE_PERIOD
SUSPENDED
CANCELLED

Lifecycle Flow
**Signup**
↓
Trial Activated
↓
Subscription Purchased
↓
Active
↓
Renewal
↓
Expiration

Failure scenario:
Active
↓
Past Due
↓
Grace Period
↓
Suspended
6. Grace Period
When subscription expires:
Grace Period = 7 days

During grace:
System remains usable
Renewal warnings shown
After grace:
Tenant status = suspended

Operations are blocked.
7. Tenant Lock Behaviour
When subscription is invalid:
Allowed:
Login
Access billing page
View data
Blocked:
Create inventory
Create invoices
Add products
Create manufacturing orders

User is redirected to:
/billing
8. Paystack Payment Integration
Payments are processed using Paystack Checkout.
Payment Flow
User selects plan
↓
System generates payment session
↓
Redirect to Paystack
↓
User completes payment
↓
Paystack sends webhook
↓
System verifies payment -> Tokenize Card payment
↓
Subscription activated
Required Webhook Events
charge.success
invoice.payment_failed
subscription.create
subscription.disable
9. Subscription Renewal
Renewal can occur through:
Automatic Renewal
If:
auto_renew = true
System attempts payment:
3 days before expiry
Success:
Subscription extended

Failure:
Status = PAST_DUE
Manual Renewal
Tenant admin renews from billing page.
Billing Page
↓
Select Plan
↓
Pay
↓
Subscription Extended
10. Plan Feature Control
Plans define feature flags and usage limits.
Important: Plans and their features are configurable
Example feature configuration:
Feature         Starter         Business        Professional
Users           3               10              Unlimited
Products        500             5000            Unlimited
Warehouses      1               3               10
Manufacturing   Disabled        Enabled         Enabled
CRM             Enabled         Enabled         Enabled

Feature enforcement must occur via middleware and service layer checks.
11. Usage Limits
Usage limits are enforced per tenant.
Examples:
max_users
max_products
max_warehouses
max_monthly_transactions

Example rule:
IF tenant_users >= plan.max_users
THEN block user creation

12. User Signup Flow
Data collection should be broken down into stages with not more than two form field to show at a time for better UX.

- Step 1 — Registration
 - Authentication Info: (Email & password or Social Logins: Google, Apple)

 - Personal Information: name, phone, country, email, address

- Step 2 — Email Verification ( for none social login)

User receives verification email
↓
Clicks verification link
↓
Account activated

- Step 3 — Tenant Creation
System creates:
tenant
admin user
default workspace settings

Example tenant slug:
blackrock

Workspace URL:
blackrock.appdomain.com

Step 4 — Plan Selection
User chooses:
plan
billing_cycle
Example:
Professional
Annual
Step 5 — Trial Activation
Default trial period:
14 days

During trial:
status = trial
Step 6 — Onboarding Wizard
User is redirected to:
/onboarding
13. Onboarding Wizard
The onboarding wizard helps configure the ERP.
**Important**
The onboarding process should be a guided process with each data collection limited to one field at a time for better UX and simplify ERP setup.

**Step 1 — Business Information**
User provides:
Business name
Industry
Business address
Currency
Timezone
Country
City

**Step 2 — Warehouse Setup**
User creates first warehouse.
Example:
Main Warehouse
Production Store
Retail Outlet

**Step 3 — Team Setup**
User invites employees.
Roles:
Admin
Manager
Accountant
Sales Staff
Store Keeper

**Step 4 — Product Setup**
User adds first inventory items.
Example:
Raw Materials
Finished Products
Services

**Step 5 — Accounting Setup**
Configuration:
Tax rules
Payment methods
Chart of accounts
Step 6 — Manufacturing Setup
If plan supports manufacturing:
User configures:
Bill of Materials
Production workflow
Step 7 — Completion
User redirected to:
/dashboard

ERP is fully usable.
14. Subscription Management (Tenant Side)
Tenant admin can manage subscription.
Billing dashboard shows:
Current Plan
Billing Cycle
Subscription Status
Next Billing Date
Payment History

Available actions:
Upgrade Plan
Change Billing Cycle
Enable Auto Renewal
Download Invoice
Cancel Subscription

15. Plan Upgrade
Upgrade occurs instantly.
Example:
Starter → Professional

Flow:
Calculate prorated amount
↓
User pays difference
↓
Subscription updated

16. Plan Downgrade
Downgrades take effect next billing cycle.
Reason:
Avoid immediate feature loss.
Example:
Professional → Business
Effective date:
Next renewal date

17. Admin Management Panel
Platform administrators manage:
Plans
Tenants
Subscriptions
Payments
Invoices
Trials

Admin capabilities:
Create plan
Edit plan
Suspend tenant
Activate tenant
Extend trial
Manual subscription override
Refund payments
