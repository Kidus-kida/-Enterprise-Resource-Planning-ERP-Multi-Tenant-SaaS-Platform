# Leave Management System Upgrade to Odoo-Style Interface

## Overview

Transform the current TewosHR leave management system to match Odoo's Time Off module interface and functionality. This includes restructuring the UI/UX, adding new features (Allocation Requests, Configuration, Negative Cap, Payroll integration), optimizing the sidebar menu with a settings gear icon, and implementing advanced search/filter capabilities similar to the `/employees` page.

![Odoo Time Off Interface](file:///C:/Users/HP-ef2126wm_2021/.gemini/antigravity/brain/f7610bf5-661e-4e93-92de-1e99eb6a9b94/uploaded_media_1769159108700.png)

---

## User Review Required

> [!IMPORTANT]
> **Major UI/UX Restructuring**
> - The sidebar menu will be significantly simplified. Current menu items (`Leave Requests`, `My Leaves`, `Leave Types`, `Annual Leave Settings`) will be reorganized into a top tab navigation (`My Time`, `Overview`, `Management`, `Reporting`, `Configuration`)
> - Settings like `Leave Types` and `Annual Leave Settings` will be moved under a gear icon in the Configuration section
> - This approach provides a cleaner, more professional interface similar to Odoo

> [!IMPORTANT]
> **New Business Capabilities**
> - **Accrual Plans**: Employees will earn leave days automatically based on configured rules (e.g., 1.67 days per month)
> - **Public Holidays**: System-wide holidays that are excluded from leave calculations
> - **Mandatory Days**: Company-defined mandatory days off (e.g., year-end shutdown)
> - **Allocation Workflow**: Employees can request additional leave allocations, subject to approval
> - **Negative Balance**: Allow employees to go into negative balance based on leave type settings

> [!WARNING]
> **Business Process Changes**
> - Current manual annual leave allocation will be replaced by automated accrual plans
> - Leave balance calculation will factor in public holidays and mandatory days
> - Approval workflows will be configurable per leave type (no approval, manager only, HR only, or dual approval)
> - This changes how employees interact with the leave system and requires user training

---

## Complete Odoo Time Off System Structure

### Main Navigation Tabs

1. **My Time** - Employee's personal leave dashboard
2. **Overview** - Team/department view (for managers)
3. **Management** - Administrative functions (Time Off Requests, Allocations)
4. **Reporting** - Analytics and reports
5. **Configuration** - System settings (accessed via gear icon)

### Configuration Section (via Gear Icon)

1. **Time Off Types** - Define different types of leave (Paid Time Off, Sick Leave, etc.)
2. **Accrual Plans** - Set rules for how leave accrues over time
3. **Public Holidays** - Define company-wide holidays
4. **Mandatory Days** - Define mandatory days off (e.g., shutdown periods)

### Management Section

1. **Time Off** - Manage leave requests
2. **Allocations** - Manage leave allocations

---

## Business Logic & Workflow Design

### 1. Time Off Types Workflow

**Purpose**: Define various types of leave with specific rules and approval workflows

**Business Logic:**

```
Time Off Type Configuration includes:

├── Basic Information
│   ├── Name (e.g., "Paid Time Off", "Sick Leave", "Unpaid Leave")
│   ├── Color (for visual identification in calendar)
│   ├── Icon (optional)
│   └── Status (Active/Inactive)
│
├── Duration Settings
│   ├── Duration Type: Day / Half Day / Hours
│   ├── Count As: Absence (deducted from work time) / Attendance (counted as work time)
│   └── Display in Calendar: Yes/No
│
├── Notifications
│   ├── Notify HR: Yes/No (if yes, select HR users)
│   ├── Notify Time Off Officer: Yes/No (if yes, select officers)
│   └── Email templates for notifications
│
├── Time Off Requests Approval
│   ├── No Validation (auto-approved)
│   ├── By Time Off Officer only
│   ├── By Employee's Approver (manager) only
│   └── By Employee's Approver AND Time Off Officer (dual approval)
│
├── Allocation Settings
│   ├── Requires Allocation: Yes/No
│   │   └── If Yes: Leave can only be requested if allocation exists
│   ├── Allow Employee Allocation Requests: Yes/No
│   │   └── If Yes: Employees can request additional allocations
│   └── Allocation Approval Workflow (same 4 options as above)
│
├── Negative Balance (Negative Cap)
│   ├── Allow Negative Balance: Yes/No
│   ├── Maximum Negative Limit (e.g., -5 days)
│   └── Alert Threshold (e.g., warn when reaching -3 days)
│
└── Payroll Integration
    ├── Is Paid Leave: Yes/No
    ├── Payroll Category (for salary calculation)
    └── Deduction Rules (if unpaid)
```

**TewosHR Implementation Workflow:**
1. Admin/HR navigates to Configuration → Time Off Types
2. Creates/edits leave type with all above settings
3. Sets appropriate approval workflow based on company policy
4. Configures whether allocation is required
5. Defines negative balance rules if applicable
6. Links to payroll category
7. System validates and saves configuration
8. Leave type becomes available for employees to request

---

### 2. Accrual Plans Workflow

**Purpose**: Automatically allocate leave days to employees based on tenure, employment type, or time-based rules

**Business Logic:**

```
Accrual Plan includes:

├── Plan Information
│   ├── Name (e.g., "Annual Leave Accrual - Full Time")
│   ├── Leave Type (link to Time Off Type)
│   ├── Status (Active/Inactive)
│   └── Applicable To (All employees / Specific department / Specific employees)
│
├── Accrual Rules
│   ├── Accrual Frequency
│   │   ├── Daily (e.g., 0.08 days per day worked)
│   │   ├── Weekly (e.g., 0.4 days per week)
│   │   ├── Monthly (e.g., 1.67 days per month)
│   │   ├── Quarterly (e.g., 5 days per quarter)
│   │   └── Yearly (e.g., 20 days per year)
│   │
│   ├── Accrual Start Date
│   │   ├── From hire date
│   │   ├── After probation period (e.g., after 3 months)
│   │   └── Specific date (e.g., January 1st each year)
│   │
│   ├── First Accrual Behavior
│   │   ├── Prorated (based on start date)
│   │   └── Full amount immediately
│   │
│   └── Accrual Calculation
│       ├── Based on working days
│       ├── Based on calendar days
│       └── Exclude absences from accrual calculation
│
├── Caps and Limits
│   ├── Maximum Accrual (cap at X days, e.g., 30 days)
│   ├── Carryover Rules
│   │   ├── No carryover (use it or lose it)
│   │   ├── Full carryover to next year
│   │   ├── Partial carryover (max X days, e.g., 5 days)
│   │   └── Carryover expiry (carried days expire after X months)
│   │
│   └── Waiting Period After Allocation
│       └── Employee must wait X days before using accrued leave
│
└── Advanced Settings
    ├── Tenure-Based Tiers (increase accrual after X years of service)
    │   └── Example: 0-2 years: 15 days/year, 3-5 years: 20 days/year, 5+ years: 25 days/year
    └── Employment Type Based (different rules for full-time, part-time, contract)
```

**TewosHR Implementation Workflow:**

**Setup Phase:**
1. HR creates accrual plan in Configuration → Accrual Plans
2. Defines accrual frequency (e.g., 1.67 days per month for 20 annual days)
3. Sets carryover rules (e.g., max 5 days can carry forward)
4. Defines maximum cap (e.g., cannot exceed 30 days total)
5. Applies plan to employee groups (all full-time employees)
6. Activates the plan

**Automated Execution (System Cron Job):**
1. **Daily/Monthly Cron Job** runs automatically
2. For each active accrual plan:
   - Identify eligible employees
   - Calculate accrual amount based on period
   - Check if employee has absences that affect accrual
   - Add accrued days to employee's balance
   - Check against maximum cap
   - Create allocation record
   - Send notification to employee (optional)
3. Log all accruals for audit trail

**Example Scenario:**
- Employee hired on June 15, 2026
- Accrual plan: 20 days/year = 1.67 days/month
- July 1: System accrues 0.83 days (prorated for half of June)
- August 1: System accrues 1.67 days
- September 1: System accrues 1.67 days
- Employee balance grows automatically each month
- By June 2027 (1 year), employee has ~20 days accrued

**Carryover Logic (Year-End):**
1. December 31st cron job runs
2. For each employee with accrued leave:
   - Check unused balance
   - Apply carryover rules (e.g., max 5 days)
   - Excess days beyond carryover limit are forfeited
   - Create new allocation for carried-over days
   - New year accrual starts fresh
   - Notify employees of carryover status

---

### 3. Public Holidays Workflow

**Purpose**: Define company-wide holidays that don't count against leave balance and are excluded from leave calculations

**Business Logic:**

```
Public Holiday includes:

├── Holiday Information
│   ├── Name (e.g., "Christmas Day", "Independence Day")
│   ├── Date (specific date or recurring rule)
│   ├── Year (if one-time) or Recurring (if annual)
│   ├── Duration (Full Day / Half Day)
│   └── Color (for calendar display)
│
├── Applicability
│   ├── All Employees
│   ├── Specific Departments
│   ├── Specific Locations/Branches
│   └── Specific Employee Groups
│
├── Business Rules
│   ├── Exclude from leave calculations: Yes/No
│   ├── If falls on weekend, move to: Monday / Friday / No adjustment
│   ├── Paid or Unpaid
│   └── Mandatory attendance exception (for critical services)
│
└── Calendar Integration
    ├── Display on company calendar
    ├── Block leave requests on this day (optional)
    └── Auto-reject conflicting leave requests
```

**TewosHR Implementation Workflow:**

**Setup Phase:**
1. Admin navigates to Configuration → Public Holidays
2. Creates holiday (e.g., "New Year's Day - January 1, 2026")
3. Sets applicability (all employees or specific departments)
4. Configures weekend adjustment rules if needed
5. Sets as paid/unpaid
6. Saves and publishes

**Automatic Impact on Leave Requests:**

**Scenario 1: Employee requests leave including a public holiday**
```
Employee Request: June 1-5, 2026 (5 days)
Public Holiday: June 3, 2026
Calculation Logic:
├── Total days requested: 5 days
├── Exclude weekend (Sat-Sun): 0 days (assume all weekdays)
├── Exclude public holidays: 1 day (June 3)
└── Actual leave days deducted: 4 days

System Message: "Your request spans 5 calendar days, but only 4 days will be deducted 
from your balance (June 3 is a public holiday)."
```

**Scenario 2: Public holiday falls on weekend**
```
Public Holiday: Independence Day (Saturday, July 4, 2026)
Company Rule: If Saturday, observe on Friday
System Action:
├── Move holiday to Friday, July 3, 2026
├── Update calendar
├── Notify employees
└── Block leave requests for July 3 (optional)
```

**Calendar Display:**
- Public holidays shown in distinct color
- Hover shows holiday name
- Visual indicator that it doesn't count against leave

---

### 4. Mandatory Days Workflow

**Purpose**: Define company-mandated days when all employees must take leave (e.g., annual shutdown, training days)

**Business Logic:**

```
Mandatory Day includes:

├── Basic Information
│   ├── Name (e.g., "Year-End Shutdown", "Company Training Day")
│   ├── Start Date
│   ├── End Date (for multi-day shutdowns)
│   ├── Type: Shutdown / Training / Other
│   └── Status (Planned / Confirmed / Cancelled)
│
├── Leave Type Assignment
│   ├── Which leave type to use (e.g., Annual Leave, Unpaid Leave)
│   ├── Deduct from balance: Yes/No
│   │   └── If Yes: Auto-create leave request for all employees
│   └── If No balance: Allow negative balance or use unpaid leave
│
├── Employee Coverage
│   ├── All employees (company-wide shutdown)
│   ├── Specific departments only
│   ├── Exclude critical staff (e.g., security, IT support)
│   └── Voluntary vs Mandatory
│
└── Automation
    ├── Auto-create leave requests for all affected employees
    ├── Notification timing (X days before)
    ├── Deadline for exemption requests
    └── Approval workflow (if exemptions allowed)
```

**TewosHR Implementation Workflow:**

**Planning Phase (HR):**
1. HR navigates to Configuration → Mandatory Days
2. Creates mandatory shutdown (e.g., "Year-End Shutdown: Dec 25-31, 2026")
3. Selects which leave type to use (e.g., Annual Leave)
4. Chooses whether to deduct from balance
5. Excludes critical staff if needed
6. Sets notification date (e.g., notify employees 30 days before)
7. Saves as "Planned"

**Notification Phase (System Automation):**
1. 30 days before shutdown: System sends notification to all affected employees
2. Email includes: Dates, leave type used, balance impact, exemption process
3. Employees can request exemption if allowed (with business justification)
4. Manager reviews exemption requests

**Execution Phase (System Automation):**
1. 7 days before shutdown or on specified date:
   - System auto-creates leave requests for all affected employees
   - Leave type: Selected type (e.g., Annual Leave)
   - Status: Auto-approved (since mandatory)
   - Deducts from balance if configured
   - If insufficient balance:
     * Allow negative balance (if leave type permits), OR
     * Create additional allocation, OR
     * Switch to unpaid leave
2. Calendar updated to show all employees on mandatory leave
3. Employees see leave reflected in their "My Time" dashboard

**Example Scenario:**
```
Company announces year-end shutdown: December 25-31, 2026 (5 working days)
Configuration:
├── Leave Type: Annual Leave
├── Deduct from balance: Yes
├── Allow negative balance: No
└── If insufficient: Create special allocation

Employee A: Has 8 days balance → 5 days deducted, remains with 3 days
Employee B: Has 2 days balance → System creates additional 3-day allocation to cover shortage
Employee C: Critical IT staff → Exempt from mandatory day, no leave created
```

---

### 5. Time Off Requests Workflow

**Purpose**: Unified workflow for employees to request leave and for managers to approve/reject

**Business Logic:**

```
Time Off Request Process:

├── Request Initiation (Employee)
│   ├── Select leave type
│   ├── Check available balance
│   │   ├── If sufficient: Proceed
│   │   ├── If insufficient but negative cap allowed: Show warning, allow to proceed
│   │   └── If insufficient and no negative cap: Block request, suggest allocation request
│   │
│   ├── Select dates
│   │   ├── System calculates: Total days, working days, excludes weekends/holidays
│   │   ├── Shows impact on balance (current vs after approval)
│   │   └── Highlights any public holidays in range
│   │
│   ├── Select duration type (if leave type allows)
│   │   ├── Full days
│   │   ├── Half day (morning/afternoon)
│   │   └── Hours
│   │
│   ├── Enter reason (optional/mandatory based on leave type)
│   ├── Attach documents (e.g., medical certificate for sick leave)
│   └── Submit request
│
├── Validation (System)
│   ├── Check balance availability
│   ├── Verify no conflicting requests exist
│   ├── Check leave type status (active/inactive)
│   ├── Validate dates (start <= end, not in past)
│   ├── Check negative cap limits
│   ├── Check team overlap (if configured - e.g., max 2 people on leave per team)
│   └── If all valid: Create request with status "Pending"
│
├── Approval Workflow (Based on Leave Type Configuration)
│   │
│   ├── Option 1: No Validation
│   │   └── Request auto-approved immediately → Deduct balance → Notify employee
│   │
│   ├── Option 2: By Time Off Officer
│   │   ├── Notify Time Off Officer(s)
│   │   ├── Officer reviews request
│   │   ├── Officer approves/rejects with optional comments
│   │   └── Update status → Deduct balance (if approved) → Notify employee
│   │
│   ├── Option 3: By Employee's Approver (Manager)
│   │   ├── Notify employee's manager
│   │   ├── Manager reviews request
│   │   ├── Manager approves/rejects with optional comments
│   │   └── Update status → Deduct balance (if approved) → Notify employee
│   │
│   └── Option 4: By Employee's Approver AND Time Off Officer (Dual Approval)
│       ├── Stage 1: Employee's Manager
│       │   ├── Notify manager
│       │   ├── Manager reviews and approves/rejects
│       │   ├── If rejected: Process ends → Notify employee
│       │   └── If approved: Move to Stage 2
│       │
│       └── Stage 2: Time Off Officer
│           ├── Notify Time Off Officer
│           ├── Officer reviews and approves/rejects
│           ├── Final decision
│           └── Update status → Deduct balance → Notify employee
│
├── Balance Deduction (Upon Final Approval)
│   ├── If Allocation System:
│   │   ├── Deduct from current year allocation first
│   │   ├── Then from carried-over allocation
│   │   └── If insufficient, use negative balance (if allowed)
│   │
│   └── Update remaining balance
│       └── Log transaction for audit trail
│
└── Status Updates & Notifications
    ├── Status Options: Pending → Approved → Rejected → Cancelled
    ├── Employee notifications: Submission, approval, rejection
    ├── Manager notifications: New request, pending approval
    └── HR notifications: All requests (for tracking)
```

**TewosHR Implementation Example:**

**Example 1: Simple Request (No Validation)**
```
Employee Action:
1. Goes to "My Time" → Click "Request Time Off"
2. Selects "Unpaid Leave" (configured with No Validation)
3. Selects June 10-12 (3 days)
4. Enters reason: "Personal matter"
5. Submits

System Action:
1. Validates dates
2. Creates request
3. AUTO-APPROVES immediately (no validation required)
4. Deducts 3 days from unpaid leave balance
5. Sends confirmation email to employee
6. Updates calendar
```

**Example 2: Complex Request (Dual Approval)**
```
Employee Action:
1. Requests "Paid Time Off" June 1-10 (10 days)
2. Leave type configured: Dual approval required
3. Current balance: 12 days
4. Attaches reason, submits

System Action (Stage 1 - Manager):
1. Creates request with status "Pending - Manager Approval"
2. Sends email to manager
3. Manager sees request in "Overview" → "Waiting For Me"
4. Manager reviews:
   - Checks team coverage
   - Reviews reason
   - Approves
5. Status changes to "Pending - HR Approval"

System Action (Stage 2 - HR):
1. Sends email to Time Off Officer (HR)
2. HR sees request in "Management" → "Pending Approvals"
3. HR reviews:
   - Checks compliance
   - Reviews overall leave pattern
   - Approves
4. Status changes to "Approved"
5. Deducts 10 days from employee balance (12 → 2 days)
6. Sends confirmation to employee, manager, and HR
7. Updates company calendar
```

**Example 3: Negative Balance Request**
```
Employee Action:
1. Requests "Sick Leave" 5 days
2. Current balance: 2 days
3. Sick leave allows negative cap up to -5 days

System Action:
1. Shows warning: "You have only 2 days. Approval will put you at -3 days balance."
2. Employee confirms and submits
3. Manager receives request with balance warning
4. Manager approves
5. Balance updated: 2 → -3 days
6. System tracks negative balance
7. Future accruals will first recover negative balance
```

---

### 6. Allocation Requests Workflow

**Purpose**: Employees can request additional leave allocations beyond standard accrual, subject to approval

**Business Logic:**

```
Allocation Request Process:

├── Request Initiation (Employee)
│   ├── Check if leave type allows employee allocation requests
│   │   └── If not allowed: Option is hidden/disabled
│   │
│   ├── Select leave type
│   ├── Enter requested allocation amount (number of days/hours)
│   ├── Select validity period (From date - To date)
│   ├── Provide justification (e.g., "Expecting new child", "Family emergency")
│   ├── Attach supporting documents (optional)
│   └── Submit request
│
├── Validation (System)
│   ├── Verify leave type allows allocation requests
│   ├── Check if employee has pending allocation request for same type (prevent duplicates)
│   ├── Validate requested amount (reasonable limits)
│   ├── Validate validity period
│   └── If valid: Create allocation request with status "Pending"
│
├── Approval Workflow (Based on Leave Type's Allocation Approval Configuration)
│   │
│   ├── Option 1: No Validation
│   │   └── Auto-approve → Add allocation → Notify employee
│   │
│   ├── Option 2: By Time Off Officer
│   │   ├── Notify Time Off Officer(s)
│   │   ├── Officer reviews request and justification
│   │   ├── Officer approves/rejects with comments
│   │   └── Update status → Add allocation (if approved) → Notify employee
│   │
│   ├── Option 3: By Employee's Approver (Manager)
│   │   ├── Notify employee's manager
│   │   ├── Manager reviews request
│   │   ├── Manager approves/rejects
│   │   └── Update status → Add allocation → Notify employee
│   │
│   └── Option 4: By Employee's Approver AND Time Off Officer (Dual Approval)
│       ├── Stage 1: Manager approval
│       ├── Stage 2: Time Off Officer approval
│       └── Both must approve for allocation to be granted
│
├── Allocation Addition (Upon Approval)
│   ├── Create new allocation record
│   │   ├── Employee: [Employee Name]
│   │   ├── Leave Type: [Selected Type]
│   │   ├── Allocated Days: [Requested Amount]
│   │   ├── Validity: [From Date] - [To Date]
│   │   ├── Type: Bonus/Adjustment/Special
│   │   └── Status: Active
│   │
│   ├── Add to employee's available balance
│   ├── Set expiry date (allocation only valid for specified period)
│   └── Log transaction
│
└── Allocation Management
    ├── Allocation Types:
    │   ├── Regular (from accrual plan - automatic)
    │   ├── Carry Forward (from previous year - automatic)
    │   ├── Bonus (company reward - admin created)
    │   ├── Adjustment (corrections - admin created)
    │   └── Employee Request (requested and approved)
    │
    ├── Expiry Logic:
    │   ├── If validity period ends: Unused portion expires
    │   ├── Send warning 30 days before expiry
    │   └── After expiry: Deduct unused portion, update balance
    │
    └── Tracking:
        ├── View all allocations (current and expired)
        ├── View allocated vs used
        └── Audit trail of all allocation changes
```

**TewosHR Implementation Example:**

**Example: Employee Requests Additional Paternity Leave**
```
Current Situation:
- Employee: John Doe
- Leave Type: Paternity Leave
- Current Balance: 0 days (already used standard 5 days)
- Reason: Wife expecting twins, needs additional time

Employee Action:
1. Goes to "My Time" → "Request Allocation"
2. Selects "Paternity Leave"
3. Requests: 5 additional days
4. Validity: Jan 1, 2026 - Dec 31, 2026
5. Justification: "Wife expecting twins, need extra support time"
6. Attaches: Ultrasound report as proof
7. Submits

Manager Approval (Stage 1):
1. Manager receives notification
2. Reviews request in "Overview" → "Allocation Requests"
3. Considers:
   - Legitimacy of request
   - Impact on team
   - Company policy
4. Approves with comment: "Approved - exceptional circumstances"

HR Approval (Stage 2):
1. HR Officer receives notification
2. Reviews in "Management" → "Allocations" → "Pending"
3. Verifies:
   - Company policy allows additional paternity leave for multiples
   - Documentation is valid
   - Budget/policy compliance
4. Approves with comment: "Approved per company policy for multiple births"

System Final Action:
1. Status: Pending → Approved
2. Creates new allocation record:
   - Employee: John Doe
   - Leave Type: Paternity Leave
   - Allocated: 5 days
   - Used: 0 days
   - Remaining: 5 days
   - Validity: Jan 1 - Dec 31, 2026
   - Type: Employee Request (Special Circumstances)
3. Updates John's balance: 0 → 5 days
4. Sends confirmation email to John, manager, and HR
5. John can now request paternity leave using the new allocation
```

**Admin-Created Allocation (Bonus Example):**
```
Scenario: Company gives all employees 2 bonus days for good performance

HR Action:
1. Goes to "Management" → "Allocations" → "Create Allocation"
2. Select: All employees (bulk action)
3. Leave Type: Paid Time Off
4. Amount: 2 days
5. Validity: Jan 1 - Dec 31, 2026 (must use within year)
6. Type: Bonus
7. Reason: "Q4 2025 Performance Bonus"
8. Submits

System Action:
1. Creates allocation for all employees
2. Updates everyone's balance (+2 days)
3. Sends notification to all employees
4. Logs all transactions
5. Sets expiry reminder for Nov 30, 2026
```

---

## Alignment with TewosHR Current Structure

### Current System
```
Sidebar Menu:
├── Leave Management
    ├── Leave Requests
    ├── My Leaves
    ├── Leave Types
    └── Annual Leave Settings

Database Tables:
├── leave_types (basic fields only)
├── leave_requests (basic workflow)
└── anunal_leaves (manual balance tracking)
```

### Proposed Odoo-Style System
```
Simplified Sidebar:
└── Leave Management (single item, opens module)

New Top Tab Navigation:
├── My Time (employee personal view)
├── Overview (manager/team view)
├── Management (admin view)
│   ├── Time Off Requests
│   └── Allocations
├── Reporting (analytics)
└── Configuration (gear icon)
    ├── Time Off Types
    ├── Accrual Plans (NEW)
    ├── Public Holidays (NEW)
    └── Mandatory Days (NEW)

Enhanced Database:
├── leave_types (enhanced with all Odoo fields)
├── leave_requests (enhanced approval workflow)
├── leave_allocations (NEW - replaces anunal_leaves)
├── leave_accrual_plans (NEW)
├── public_holidays (NEW)
└── mandatory_days (NEW)
```

---

## Key Business Benefits

### 1. **Automated Leave Accrual**
- **Current**: HR manually creates annual leave records each year
- **Proposed**: System automatically accrues leave based on configured rules
- **Benefit**: Saves HR time, reduces errors, employees see real-time accrual

### 2. **Flexible Approval Workflows**
- **Current**: Fixed approval process for all leave types
- **Proposed**: Configurable per leave type (no approval, single, or dual)
- **Benefit**: Different policies for different leave types (e.g., unpaid = no approval, annual = manager approval, sick >3 days = dual approval)

### 3. **Accurate Leave Calculations**
- **Current**: Manual consideration of holidays
- **Proposed**: Automatic exclusion of public holidays and weekends
- **Benefit**: Employees charged exact working days, no disputes

### 4. **Negative Balance Control**
- **Current**: System blocks requests if insufficient balance
- **Proposed**: Configurable negative cap per leave type
- **Benefit**: Flexibility for emergency situations while maintaining control

### 5. **Transparent Allocation Management**
- **Current**: HR manually adjusts balances
- **Proposed**: Formal allocation request and approval process
- **Benefit**: Audit trail, employee empowerment, clear justification

### 6. **Payroll Integration**
- **Current**: Manual tracking of paid/unpaid leaves for payroll
- **Proposed**: Automatic categorization and export to payroll
- **Benefit**: Accurate salary calculation, less manual work

---

## Implementation Approach

### Phase-by-Phase Rollout

**Phase 1: Foundation (Database & Models)**
- Set up new database tables
- Migrate current data to new structure
- Test data integrity

**Phase 2: Core Workflows (Time Off Types & Requests)**
- Implement configurable time off types
- Enhance approval workflows
- Test with sample leave types

**Phase 3: Allocations & Accruals**
- Build allocation management
- Implement accrual plans
- Set up automated cron jobs

**Phase 4: Supporting Features**
- Add public holidays
- Add mandatory days
- Integrate payroll

**Phase 5: UI/UX Transformation**
- Rebuild navigation (tabs instead of sidebar)
- Create employee dashboard (My Time)
- Create manager dashboard (Overview)
- Create admin interface (Management)

**Phase 6: Configuration Interface**
- Build gear icon menu
- Create configuration pages for each feature
- Add advanced search/filter

**Phase 7: Testing & Training**
- Comprehensive testing of all workflows
- User training materials
- Staged rollout to users

---

## Success Criteria

✅ Simplified sidebar with single "Leave Management" entry
✅ Top tab navigation functional (5 tabs)
✅ Configuration accessible via gear icon
✅ Automated accrual plans running correctly
✅ Public holidays automatically excluded from calculations
✅ Mandatory days can be created and auto-assigned
✅ Allocation requests workflow operational
✅ Flexible approval workflows per leave type
✅ Negative balance working as configured
✅ Payroll integration accurate
✅ Advanced search/filter matching `/employees` functionality
✅ System performance acceptable with new features
✅ All existing leave data successfully migrated
✅ User acceptance and satisfaction
