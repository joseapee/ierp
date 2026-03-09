Manufacturing Engine
Technical Design Document (TDD)
1. Purpose
The Manufacturing Engine provides the capability to manage the transformation of raw materials into finished goods while automatically handling:
Bill of Materials (BOM)
Raw material consumption
Production workflow tracking
Cost calculation
Work-in-Progress accounting
Finished goods inventory updates
Automatic selling price generation
This engine integrates with:
Inventory Module
Accounting Module
HRM Module
Pricing Engine
2. System Goals
The manufacturing engine must:
Automatically calculate product cost based on materials used
Track material consumption in real time
Support work-in-progress (WIP) production
Generate finished products inventory
Provide visual production workflow tracking
Automatically integrate with accounting
Allow flexible pricing strategies
Support multiple manufacturing industries
3. Core Concepts
The manufacturing engine is built around these concepts.
Raw Materials
Items used to manufacture products.
Examples:
Leather
Wood
Fabric
Cheese
Flour
Stored in inventory.
Finished Goods
Products created after manufacturing.
Examples:
Leather bag
Wooden table
Pizza
Dress
Stored in inventory.
Bill of Materials (BOM)
Defines materials required to produce a product. These materials are fetched from the inventory
Example:
Product: Leather Bag
Material
Quantity
Leather
1.5 meters
Zipper
1
Thread
1 roll

Work In Progress (WIP)
Intermediate production state where materials have been consumed but production is not yet complete.
Flow:
Raw Materials → WIP → Finished Goods


Production Order
Represents a manufacturing job.
Example:
Produce 10 Leather Bags


4. Manufacturing Workflow
Standard Workflow
Create Product
        ↓
Define Bill of Materials
        ↓
Create Production Order
        ↓
Start Production
        ↓
Consume Materials
        ↓
Move Through Production Stages
        ↓
Complete Production
        ↓
Finished Goods Added to Inventory


5. Production Board (Visual Manufacturing Workflow)
The system includes a visual Kanban production board to track manufacturing stages.
Example:
-----------------------------------------------------
| Cutting | Sewing | Finishing | Quality | Ready |
-----------------------------------------------------
| Bag #1  | Bag #3 |           |         |      |
| Bag #2  |        |           |         |      |
-----------------------------------------------------

Each card represents a production task.

Default Stage Templates
Fashion
Design
Cutting
Sewing
Finishing
Ready

Furniture
Material Prep
Cutting
Assembly
Polishing
Ready

Restaurant
Prep
Cooking
Plating
Ready

Users may customize stages.

6. Selling Price Strategy
Manufactured products support three pricing modes.
1. Percentage Markup
Selling Price = Cost × (1 + Markup%)

Example:
Cost = $36
Markup = 50%
Selling Price = $54

2. Fixed Profit Addition
Selling Price = Cost + Fixed Profit

Example:
Cost = $36
Profit = $20
Selling Price = $56

3. Manual Price
User directly sets selling price.

Pricing Configuration Levels
Product Level
Category Level
Manual Override

7. Cost Calculation
Product cost is calculated using the BOM.
Total Cost = Σ(Material Quantity × Material Unit Cost)

Example:
Material
Qty
Unit Cost
Total
Leather
1.5
$20
$30
Zipper
1
$3
$3
Thread
1
$1
$1

Total Cost = $34

Supported Cost Methods
FIFO
LIFO
Weighted Average Cost (Default)

8. Material Consumption Model
The system supports two consumption modes.

Mode A: Deduct at Production Completion
Materials deducted when production completes.
Workflow:
Production Completed
        ↓
Materials Deducted
        ↓
Finished Goods Added


Mode B: Deduct During Production (Recommended)
Materials deducted as they are used.
Workflow:
Start Production
        ↓
Materials Consumed
        ↓
WIP Updated
        ↓
Production Completed
        ↓
Finished Goods Created


9. Inventory Flow
Raw Materials Inventory
        ↓
Material Consumption
        ↓
Work In Progress
        ↓
Production Completion
        ↓
Finished Goods Inventory

This ensures no double deduction of materials.

10. Accounting Integration
Manufacturing automatically generates accounting entries.

Material Consumption Entry
Dr Work In Progress
   Cr Raw Material Inventory


Production Completion Entry
Dr Finished Goods Inventory
   Cr Work In Progress


11. Production Card Structure
Each card on the production board contains:
Product Name
Production Order ID
Quantity
Production Cost
Assigned Worker
Due Date
Current Stage

Example:
Product: Luxury Bag
Order: #1004
Quantity: 5
Cost: $170
Assigned: Maria
Due: March 12
12. Data Model
Product
product
--------
id
name
category_id
type (manufactured/purchased)
pricing_rule_id
manual_price
Raw Materials
material
---------
id
name
unit
current_cost
stock_quantity
Bill of Materials
bom
-------
id
product_id
version
created_at
BOM Items
bom_items
-----------
id
bom_id
material_id
quantity_required
Production Orders
production_order
------------------
id
product_id
quantity
status
created_at
completed_at
Production Tasks
production_task
---------------
id
production_order_id
product_id
current_stage_id
assigned_worker
status
started_at
completed_at
Production Stages
production_stage
------------------
id
name
sequence
is_final_stage
industry_type
Material Consumption
material_consumption
---------------------
id
production_order_id
material_id
quantity_used
cost_at_time
timestamp

Work In Progress
wip_inventory
--------------
id
production_order_id
material_cost
labor_cost
overhead_cost
total_cost
status
13. Stage Transition Algorithm
When a production task moves to a new stage:
moveProductionTask(task_id, stage_id)

Steps:
Validate stage transition
Update stage
Trigger stage automation
Log activity
Example:
if stage == MATERIAL_STAGE:
    consumeMaterials()

if stage == FINAL_STAGE:
    completeProduction()
14. Production Completion Algorithm
completeProduction(order_id):

1. calculate total WIP cost
2. move WIP cost to finished goods
3. update finished goods inventory
4. close production order
5. generate accounting entries
15. Worker Assignment
Workers can be assigned to production tasks.
Integration with HR module allows:
worker productivity tracking
labor cost calculation
performance analytics
16. Labor Cost Calculation (Optional)
Labor Cost = Worker Hourly Rate × Hours Worked

Final product cost:
Total Cost =
Material Cost
+ Labor Cost
+ Overhead
17. Production Updates
Factory workers can update production status.
Example:
Scan Production QR/Barcode
Update Stage → Sewing Completed
Production board updates in real time.
18. Permissions
Role
Permission
Admin
Configure workflow
Supervisor
Move production cards
Worker
Update task status
Accountant
View cost data

19. AI Enhancements (Future)
Smart BOM Suggestion
AI suggests BOM based on historical products.
Production Bottleneck Detection
AI identifies slow production stages.
Production Forecasting
AI predicts manufacturing demand based on:
sales trends
seasonality
historical production
20. Benefits
This manufacturing engine enables:
Automated cost calculation
Accurate inventory tracking
Visual production workflow
Flexible pricing strategies
Real-time production monitoring
Seamless accounting integration
It supports industries including:
Fashion manufacturing
Furniture production
Restaurants and kitchens
Bakeries
Custom manufacturing
General product assembly
