# Two-Person Development Action Plan
## Leave Management System Upgrade to Odoo-Style

---

## 👤 PERSON A - Backend Developer
**Focus:** Database, Configuration System, Business Logic & Automation

### Phase 1: Database Foundation (Week 1-2)

**A1. Create New Database Tables**
- Create migration for `leave_accrual_plans` table
- Create migration for `public_holidays` table  
- Create migration for `mandatory_days` table
- Create migration for `leave_allocations` table (replaces anunal_leaves)
- All tables should include proper indexes and foreign keys

**A2. Update Existing Tables**
- Add Odoo-style fields to `leave_types` table (duration type, approval workflow, negative cap, notifications, allocation settings)
- Add payroll fields to `leave_requests` table (payroll status, is_paid, payroll period, processed date)

**A3. Create Models**
- Create `LeaveAccrualPlan` model with relationships and helper methods
- Create `PublicHoliday` model with date checking methods
- Create `MandatoryDay` model with auto-execution methods
- Create `LeaveAllocation` model with balance calculation methods

**A4. Update Existing Models**
- Update `LeaveType` model with new fillable fields, casts, and relationships
- Update `LeaveRequest` model with payroll methods and new relationships

---

### Phase 2: Configuration Controllers (Week 3-4)

**A5. Time Off Types Controller**
- Build CRUD operations for leave types with all Odoo fields
- Implement validation for approval workflows
- Handle notification user selection (HR, Time Off Officers)
- Add color/icon management

**A6. Accrual Plans Controller**
- Build CRUD for accrual plans
- Implement accrual preview functionality (show yearly projection)
- Handle applicable employee groups (all/department/specific)
- Validate accrual frequency and cap settings

**A7. Public Holidays Controller**
- Build CRUD for public holidays
- Implement bulk import from CSV
- Handle weekend adjustment logic
- Add recurring holiday handling

**A8. Mandatory Days Controller**
- Build CRUD for mandatory days
- Implement auto-execution trigger (create leave requests for all employees)
- Handle exempt employee management
- Add notification scheduling

---

### Phase 3: Business Logic Services (Week 5-6)

**A9. Accrual Calculation Service**
- Build service to calculate daily/monthly accruals
- Implement prorating logic for new employees
- Handle tenure-based tiers
- Implement maximum cap checking
- Handle absence exclusion rules

**A10. Balance Calculation Service**
- Build service to calculate available balance considering:
  - Current allocations
  - Used days
  - Public holidays
  - Negative cap limits
  - Carryover amounts
- Implement allocation priority (use current year first, then carryover)

**A11. Carryover Processing Service**
- Build year-end carryover automation
- Implement carryover limit enforcement
- Handle carryover expiry dates
- Create new allocations for carried-over days
- Send expiry warnings

**A12. Holiday Exclusion Service**
- Build service to check if date is public holiday
- Implement working days calculation excluding holidays and weekends
- Handle weekend adjustment rules

---

### Phase 4: Automation Setup (Week 7)

**A13. Laravel Scheduler Configuration**
- Set up daily cron job for accrual calculation
- Set up daily cron job for allocation expiry checks
- Set up year-end cron job for carryover processing
- Set up notification scheduling
- Document cPanel cron setup instructions

**A14. Artisan Commands**
- Create command for manual accrual trigger (testing)
- Create command for manual carryover execution
- Create command for allocation expiry processing
- Add progress indicators and logging

---

### Phase 5: Routes & API (Week 8)

**A15. Configuration Routes**
- Set up all configuration routes under `/leave-management/configuration`
- Group routes by feature (time-off-types, accrual-plans, holidays, mandatory-days)
- Add route middleware for permissions

**A16. API Endpoints**
- Create API endpoints for frontend consumption
- Add endpoints for balance checking
- Add endpoints for holiday checking
- Add endpoints for accrual preview

---

## 👤 PERSON B - Frontend Developer
**Focus:** UI/UX, Navigation, Employee & Manager Features

### Phase 1: Layout & Navigation (Week 1-2)

**B1. Create Leave Management Layout**
- Build specialized layout template with top tab navigation
- Create 5 main tabs: My Time, Overview, Management, Reporting, Configuration
- Add breadcrumb navigation
- Add action buttons area

**B2. Build Top Tab Navigation Component**
- Create tab switching functionality
- Add active tab indicators
- Implement smooth transitions
- Make responsive for mobile

**B3. Add Configuration Gear Icon**
- Create dropdown component in top-right corner
- Add menu items: Time Off Types, Accrual Plans, Public Holidays, Mandatory Days, Settings
- Implement dropdown toggle

**B4. Update Sidebar Menu**
- Simplify sidebar to single "Leave Management" item
- Remove old menu items (Leave Requests, My Leaves, Leave Types, Annual Leave Settings)
- Add icon for Leave Management

---

### Phase 2: Search & Filter Components (Week 3)

**B5. Build Advanced Search Component**
- Create global search bar (search by employee, leave type, dates)
- Add real-time search with debounce
- Display search results highlighting

**B6. Build Filter Component**
- Create filter dropdowns: Status, Leave Type, Department, Date Range
- Implement active filter tags
- Add "Clear All Filters" button

**B7. Add Group By Functionality**
- Implement grouping options: Employee, Leave Type, Status, Department
- Create grouped table view
- Add expand/collapse for groups

**B8. Build Date Range Picker**
- Integrate date range picker component
- Add preset ranges (This Week, This Month, This Quarter, This Year)
- Highlight public holidays in calendar

---

### Phase 3: My Time Tab (Week 4-5)

**B9. Build My Time Dashboard**
- Create dashboard layout
- Add leave balance cards for each leave type
- Show allocated, used, and remaining days
- Add visual progress bars

**B10. Create Request Time Off Form**
- Build modal form for leave requests
- Add leave type selector with balance display
- Implement date picker with holiday highlighting
- Add half-day/hours options
- Show real-time balance impact
- Add file upload for supporting documents

**B11. Display Pending Requests**
- Create table showing employee's pending requests
- Add status badges (Pending, Waiting Manager, Waiting HR)
- Add cancel request functionality

**B12. Build Leave History View**
- Create table with past leave requests
- Show status, approver, dates
- Add filtering by date range and status
- Add export to PDF/Excel

**B13. Add Upcoming Leaves Display**
- Create calendar-style upcoming leaves widget
- Show approved future leaves
- Add countdown to next leave

---

### Phase 4: Overview Tab (Week 6)

**B14. Build Overview Dashboard (Manager)**
- Create team overview layout
- Show team members on leave today
- Display pending approval requests

**B15. Display Team Leave Calendar**
- Build calendar view showing team members' leaves
- Color-code by leave type
- Add tooltips with employee name and leave details

**B16. Build Pending Approvals List**
- Create "Waiting For Me" section
- Display requests needing manager approval
- Add quick approve/reject buttons

**B17. Create Team Statistics**
- Build cards showing: Team leave utilization, Most used leave type, Average days off per person
- Add charts for visual representation

---

### Phase 5: Management Tab (Week 7-8)

**B18. Build Management Layout**
- Create admin dashboard with sub-tabs
- Add sub-tabs: Time Off Requests, Allocations

**B19. Time Off Requests Management View**
- Display all leave requests (all employees)
- Implement advanced search/filter (from B5-B8)
- Add status change functionality
- Show approval history timeline

**B20. Allocations Management View**
- Display all allocations
- Show pending allocation requests
- Add approve/reject allocation functionality
- Create allocation history view

**B21. Build Bulk Actions**
- Add checkboxes for multi-select
- Implement bulk approve/reject
- Add confirmation dialogs
- Show bulk action results

---

### Phase 6: Allocation Features (Week 9)

**B22. Update Leave Allocation Controller**
- Build allocation request controller methods (store, approve, reject)
- Implement validation rules
- Handle dual approval workflow

**B23. Build Allocation Request Form (Employee)**
- Create modal form for requesting allocations
- Add leave type selector
- Add amount input with justification
- Add validity period selector
- Add file upload for supporting documents

**B24. Build Allocation Approval Interface (Admin)**
- Create approval page with allocation details
- Show employee justification and documents
- Add approve/reject with comments
- Display allocation impact on balance

**B25. Create Allocation History**
- Build table showing all allocations (regular, carryover, bonus, adjustment, employee request)
- Add filters by type and status
- Show expiry dates
- Add expiry warnings

---

### Phase 7: Leave Request Features (Week 10)

**B26. Update Leave Request Controller**
- Implement new approval workflows (no validation, single, dual)
- Add negative balance handling
- Integrate public holiday checking
- Add payroll status tracking

**B27. Build Multi-Step Approval Interface**
- Create approval page showing current approval stage
- Display approval history timeline
- Add stage-specific approve/reject buttons
- Show next approver information

**B28. Add Negative Balance Warnings**
- Display warning when insufficient balance
- Show negative cap limit
- Add confirmation for negative balance requests
- Display current and future balance

**B29. Integrate Holiday Detection**
- Highlight public holidays in date picker
- Show holiday names on hover
- Display final working days calculation
- Show excluded days message

---

### Phase 8: Configuration Views (Week 11)

**B30. Time Off Types Configuration Page**
- Build form with all Odoo fields organized in sections:
  - Basic Information
  - Duration Settings
  - Notifications
  - Time Off Requests Approval
  - Allocation Settings
  - Negative Cap
  - Payroll Integration
- Add user multi-select for HR and Time Off Officers
- Implement radio buttons for approval workflows

**B31. Accrual Plans Configuration Page**
- Build form with accrual plan fields
- Add frequency selector with dynamic fields
- Create preview panel showing yearly projection
- Add carryover settings section

**B32. Public Holidays Configuration Page**
- Build holiday list with add/edit/delete
- Add bulk import CSV functionality
- Create recurring holiday toggle
- Add weekend adjustment selector

**B33. Mandatory Days Configuration Page**
- Build mandatory day form
- Add employee exemption selector
- Create notification schedule settings
- Add execution trigger button

---

### Phase 9: Assets & Styling (Week 12)

**B34. Create leave-management.css**
- Implement Odoo-style dark theme colors
- Create card styles for balance display
- Add status badge styles (pending: yellow, approved: green, rejected: red)
- Style tab navigation
- Create gear icon dropdown styles
- Ensure responsive design

**B35. Create leave-management.js**
- Implement tab switching logic
- Add filter and search handlers
- Create AJAX functions for approve/reject
- Add real-time balance update scripts
- Implement form validations
- Add loading indicators

**B36. Implement Responsive Design**
- Test and adjust for mobile devices
- Collapse tabs to dropdown on small screens
- Make tables scrollable on mobile
- Ensure forms are mobile-friendly

**B37. Add Animations & Transitions**
- Add smooth transitions between tabs
- Create loading animations
- Add success/error toast notifications
- Implement smooth scroll

---

## 🔄 Coordination Points

### Minimal Dependency Handoffs:
1. **Week 2**: Person A completes migrations → Person B can test with sample data
2. **Week 4**: Person A completes configuration controllers → Person B can connect configuration forms
3. **Week 6**: Person A completes balance service → Person B can integrate real-time balance display
4. **Week 8**: Person A completes routes → Person B can finalize all API calls

### Merge Strategy:
- **Person A** works primarily in: `database/`, `app/Models/`, `app/Http/Controllers/` (config controllers), `app/Services/`, `app/Console/`
- **Person B** works primarily in: `resources/views/`, `resources/assets/`, `app/Http/Controllers/` (request/allocation controllers), `routes/web.php`
- **Minimal file conflicts** - Different directories most of the time

### Communication Checkpoints:
- **Week 2**: Review database structure together
- **Week 4**: Review API endpoint structure
- **Week 7**: Integration testing session
- **Week 10**: Full system testing
- **Week 12**: Final review and deployment prep

---

## 📅 Calendar View (Both - Week 13, Optional)

**Saved for last - Both developers work together:**
- Full calendar integration with leave visualization
- Drag-and-drop leave requests
- Team calendar with color-coded leaves
- Calendar export (iCal, Google Calendar)

---

## ✅ Success Metrics

- All 6 new database tables created and populated
- 4 configuration features fully functional (Time Off Types, Accrual Plans, Public Holidays, Mandatory Days)
- 5 navigation tabs operational
- Automated accrual cron job running
- Advanced search/filter working
- Negative balance handling functional
- Dual approval workflows operational
- Payroll integration ready
- Mobile-responsive design
- All existing leave data migrated successfully
