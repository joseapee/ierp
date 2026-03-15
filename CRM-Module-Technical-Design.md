CRM Module Technical Design
Module: Customer Relationship Management (CRM)
1. CRM Objectives
The CRM module should allow businesses to:
• Manage leads and prospects
• Convert leads into customers
• Track sales pipelines and opportunities
• Manage customer communication history
• Track customer purchases and behavior
• Assign sales representatives
• Automate sales follow-ups and reminders
• Generate customer analytics and reports
The CRM should tightly integrate with:
Sales
Accounting
Inventory
Marketing
Support
2. Core CRM Modules
The CRM should consist of the following functional modules:
CRM
 ├── Leads Management
 ├── Contacts Management
 ├── Companies / Accounts
 ├── Sales Pipeline
 ├── Activities & Tasks
 ├── Communication History
 ├── Customer 360 Profile
 ├── Customer Segmentation
 ├── Campaign Management
 ├── Customer Support Tickets
 ├── Sales Forecasting
 └── CRM Analytics
3. Leads Management
Leads represent potential customers who have not yet been converted.
Lead Sources
Website form
Social media
Email campaigns
Phone inquiries
Walk-in
Referral
Manual entry

Proposed Lead Data Model
leads
id
tenant_id
lead_name
company_name
email
phone
source
industry
status
assigned_to
estimated_value
notes
created_at
updated_at
Lead Status Pipeline
New Lead
Contacted
Qualified
Proposal Sent
Negotiation
Converted
Lost
Key Features
• Lead assignment to sales agents
• Lead scoring
• Follow-up reminders
• Lead source analytics
• Automatic lead capture from website forms
4. Contacts Management
Contacts represent people associated with customers or leads.
Example:
Customer Company → Multiple Contacts
contacts table
id
tenant_id
first_name
last_name
email
phone
job_title
company_id
customer_id
notes
created_at
Features
• Multiple contacts per company
• Contact activity history
• Contact tagging
• Email and phone tracking
5. Companies / Accounts
Companies represent organizations customers belong to.
Example:
Dangote Ltd
Zenith Bank
Blackrock Furniture
companies table
id
tenant_id
company_name
industry
website
phone
address
city
country
owner_id
created_at
Features
• Multiple contacts
• Sales history
• Opportunities attached
• Customer value tracking
6. Sales Pipeline (Opportunities)
Tracks potential deals.
opportunities table
id
tenant_id
name
company_id
contact_id
stage
expected_value
probability
expected_close_date
assigned_to
notes
created_at
Pipeline Stages
Example:
Prospecting
Qualification
Proposal
Negotiation
Won
Lost
Features
• Drag-and-drop pipeline board
• Deal value tracking
• Win probability
• Forecast revenue
UI Representation
Kanban style board:
Prospecting | Qualification | Proposal | Won
Deal A        Deal C          Deal E
Deal B        Deal D
7. Activities & Tasks
Tracks actions salespeople must perform.
activities table
id
tenant_id
type
subject
related_to_type
related_to_id
assigned_to
due_date
status
notes
created_at
Activity Types
Call
Meeting
Email
Follow-up
Demo
Site Visit
Features
• Activity reminders
• Calendar integration
• Task assignment
• Overdue activity alerts
8. Communication History
Tracks every interaction with a customer.
communications table
id
tenant_id
customer_id
contact_id
type
message
attachment_url
created_by
created_at
Communication Types
Email
Call
SMS
WhatsApp
Meeting
Support ticket
9. Customer 360 Profile
A central dashboard for each customer.
Shows:
Customer Info
Contacts
Orders
Invoices
Payments
Support Tickets
Communication history
Opportunities
Example Customer View
Customer: Blackrock Furniture
Total Revenue: $120,000
Orders: 45
Outstanding Balance: $4,000
Active Opportunities: 3
Support Tickets: 1
10. Customer Segmentation
Allows grouping customers.
segmentation table
id
tenant_id
segment_name
rules
created_at
Example Segments
VIP Customers
Wholesale Buyers
Retail Customers
Inactive Customers
High Value Customers
Use Cases
• targeted marketing
• discount rules
• loyalty programs
11. Campaign Management
CRM should support marketing campaigns.
campaigns table
id
tenant_id
campaign_name
channel
budget
start_date
end_date
status
created_at
Channels
Email
SMS
WhatsApp
Social media
Ad campaigns
Features
• contact targeting
• campaign performance analytics
• campaign ROI tracking
12. Customer Support Tickets
Integrates CRM with customer service.
tickets table
id
tenant_id
customer_id
subject
priority
status
assigned_to
created_at
Ticket Status
Open
In Progress
Waiting
Resolved
Closed

13. CRM Integration with ERP Modules
CRM must tightly integrate with the rest of the ERP.
CRM → Sales
When a deal is won:
Opportunity → Sales Order
CRM → Inventory
Sales team can check:
Product availability
Stock levels
Pricing
CRM → Accounting
Customer financial info.
Outstanding invoices
Payment history
Credit limits
CRM → Support
Customer support history is linked to the CRM.
14. CRM Analytics
CRM dashboards should show:
Lead conversion rate
Sales performance
Top customers
Revenue by customer
Sales pipeline value
Campaign ROI
Example Metrics
Leads this month
Conversion rate
Average deal size
Sales cycle length
15. Multi-Tenant Considerations
Since the ERP is multi-tenant:
Every CRM table must include:
tenant_id
Example:
leads
contacts
companies
opportunities
activities
campaigns
tickets
This ensures tenant data isolation.
16. Permissions Model
CRM should support role-based access.
Role
Access
Admin
Full access
Sales Manager
Pipeline + reports
Sales Agent
Assigned leads
Support Agent
Tickets
Marketing
Campaigns


17. CRM Enhancements (additional Features)
Advanced CRM capabilities.
AI Lead Scoring
Automatically score leads.
High probability leads
Low probability leads
Customer Lifetime Value Prediction
Estimate revenue from customers.
Sales Automation
Automate workflows.
Example:
Lead created → assign to sales rep
Deal won → generate sales order
Invoice overdue → notify sales rep
18. CRM UI Modules
The CRM section of the ERP navigation.
CRM
 ├── Dashboard
 ├── Leads
 ├── Contacts
 ├── Companies
 ├── Opportunities
 ├── Activities
 ├── Campaigns
 ├── Tickets
 └── Reports
Final Architecture Position
CRM sits above operational modules.
CRM
   │
   ├── Sales
   ├── Inventory
   ├── Accounting
   ├── Manufacturing
   └── Support
