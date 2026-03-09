Product Creation UI Flow Directive
Module: Product Management
Applies To: Manufacturing, Retail, Restaurant, and General Inventory Products
1. UI Design Goals
The Product Creation interface must:
• Be simple and guided
• Avoid overwhelming users with too many fields
• Support multiple product types
• Support manufactured and non-manufactured products
• Allow step-by-step configuration
The system will use a multi-stage wizard interface.
2. Product Creation Wizard Overview
The product creation interface should be divided into 5 progressive stages.
Users can navigate forward/backward.
Stage 1 → Product Type
Stage 2 → Basic Product Information
Stage 3 → Inventory Configuration
Stage 4 → Manufacturing Configuration (conditional)
Stage 5 → Pricing Configuration
Stage 6 → Review & Save

Stages appear as a progress indicator.
Example:
[1 Type] → [2 Details] → [3 Inventory] → [4 Manufacturing] → [5 Pricing] → [6 Review]
3. Stage 1 — Product Type Selection
This is the entry point.
Purpose: Determine what fields appear in later stages.
UI Layout
Display large selectable cards.
What type of product do you want to create?

Options:
◻ Purchased Product
◻ Manufactured Product
◻ Service
◻ Bundle / Kit
Option Descriptions
Purchased Product
Products bought from suppliers and sold.
Example:
Groceries
Electronics
Clothing
Manufactured Product
Products produced internally using raw materials.
Example:
Furniture
Fashion
Restaurant Meals
Bakery Products
Service
Non-inventory service.
Example:
Repair
Consultation
Delivery
Bundle / Kit
Group of multiple products sold together.
Example:
Gift sets
Furniture sets
Combo meals
User Action
User selects a product type and clicks:
Next →
4. Stage 2 — Basic Product Information
This stage captures the core product identity.
Fields
Product Name
Product Category
Product Description
Product SKU
Barcode (optional)
Brand (optional)
Unit of Measurement
UI Layout
Product Name: __________________
Category:
[Dropdown]
Unit:
[Piece | Meter | Kg | Litre | etc]
SKU:
[Auto-generate] or [Manual]
Barcode:
[Optional]
Smart UI Feature
SKU can be:
Auto-generated
Example:
BAG-0001
CHAIR-0004
5. Stage 3 — Inventory Configuration
Defines how the product behaves in inventory.
Fields
Track Inventory? (Yes/No)
Minimum Stock Level
Maximum Stock Level
Reorder Point
Storage Location
Warehouse
Example UI
Track Inventory
☑ Yes
Minimum Stock Level
[ 10 ]
Reorder Point
[ 15 ]
Warehouse
[ Main Warehouse ]
Conditional Behavior
If Track Inventory = No
Inventory fields disappear.
Useful for services.
6. Stage 4 — Manufacturing Configuration
(Visible only if product type = Manufactured)
This stage configures the Bill of Materials and production settings.
Section A — Bill of Materials (BOM)
Users define materials required.
UI Table:
Material | Quantity | Unit | Cost
---------------------------------
Leather  | 1.5      | m    | $20
Zipper   | 1        | pc   | $3
Thread   | 1        | roll | $1
Actions
+ Add Material
Remove Material
Edit Quantity
Smart Features
Auto-calculate:
Estimated Material Cost

Display:
Total Material Cost: $34
Section B — Production Settings
Fields:
Production Mode
Options:
◉ Deduct materials during production
○ Deduct materials when production completes
Production Workflow Template
Dropdown:
Fashion
Furniture
Restaurant
Custom
Selecting a template auto-loads production stages.
Example:
Cutting → Sewing → Finishing → Ready
7. Stage 5 — Pricing Configuration
Allows flexible pricing strategies.
Pricing Mode Selection
Options:
◉ Automatic Pricing
○ Manual Pricing
If Automatic Pricing
User selects rule.
Pricing Method
◉ Percentage Markup
○ Fixed Profit
Percentage Markup
Markup Percentage

[ 50 % ]

Preview:
Estimated Selling Price = Cost × 1.5
Fixed Profit
Profit Amount

[ $20 ]
Preview:
Selling Price = Cost + $20
Manual Pricing
User enters price.
Selling Price

$ [_______]
8. Stage 6 — Review & Save
Shows summary before creation.
Review Page Layout
Product Summary
--------------------------

Product Name: Luxury Leather Bag
Category: Fashion
Type: Manufactured
Inventory Settings
--------------------------
Track Inventory: Yes
Warehouse: Main Warehouse
Manufacturing
--------------------------
Materials:
Leather (1.5m)
Zipper (1)
Thread (1)
Estimated Cost: $34
Pricing
--------------------------
Markup: 50%

Estimated Selling Price: $51
Actions
← Back
Save Product
Save & Create Production Order
9. UX Enhancements
To improve usability.
Inline Cost Calculation
Whenever materials are added:
Material Cost Updates Instantly
AI Suggestions (Future)
Suggest BOM materials based on similar products.
Example:
Suggested materials for "Leather Bag"
Leather
Zipper
Lining
Thread
Template Products
Users can create products using templates.
Example:
Fashion Template
Furniture Template
Restaurant Meal Template
10. Error Prevention
System should validate:
Missing product name
Missing BOM materials
Invalid markup
Duplicate SKU
Before final submission.
11. Permissions
Role
Ability
Admin
Full access
Inventory Manager
Create products
Production Manager
Add BOM
Accountant
View costing

12. Expected UX Outcome
The user experience should allow a fashion designer to create a product in under 2 minutes.
Example flow:
Select Manufactured Product
Enter Product Name
Add Materials
Set Markup
Save
System automatically calculates:
• cost
• selling price
• inventory behavior

13. Mobile Considerations
Wizard converts into vertical step flow.
Example:
Step 1 of 6
Product Type
Large buttons for easy selection.
