# Design Document: ERP Rebranding from Tewos to MD Code Inc.

## Overview

This design outlines the comprehensive rebranding strategy for the Laravel-based ERP system, replacing all "Tewos" references with "MD Code Inc." branding. The rebranding is purely cosmetic and must preserve all business logic, database schema, and functionality. The approach uses a systematic file analysis strategy combined with automated text replacement and manual asset management.

The system uses Laravel 11 with a modular architecture (nwidart/laravel-modules), Livewire 3 for reactive components, multi-tenancy (stancl/tenancy), and a multi-language support system. The rebranding affects approximately 50+ files across core application code, 13 modules, view templates, configuration files, database seeders, and asset directories.

## Architecture

### System Components Affected

```
ERP System
├── Core Application (app/, config/, routes/)
│   ├── Controllers (HTTP, Livewire)
│   ├── Models and Entities
│   ├── Jobs and Services
│   └── Helper Functions
├── Modules (Modules/*/
│   ├── Superadmin (tenant management, settings)
│   ├── Accounting, Sales, Purchase, etc.
│   └── Each with app/, resources/, lang/, database/
├── Views and Templates (resources/views/, Modules/*/resources/views/)
│   ├── Blade Templates
│   ├── Livewire Components
│   ├── Email Templates
│   └── PDF Templates
├── Assets (public/, resources/assets/)
│   ├── Images and Logos
│   ├── Favicons
│   └── CSS/SCSS with branding
├── Language Files (lang/, resources/lang/, Modules/*/lang/)
│   ├── 17+ Language JSON files
│   └── PHP language arrays
├── Configuration (config/, .env.example, .env.production.example)
│   └── Application settings
└── Database Seeders (database/seeders/, Modules/*/database/seeders/)
    └── Default data and initial values
```

### Rebranding Strategy

The rebranding employs a four-phase approach:

1. **Discovery Phase**: Identify all files containing "Tewos" references using recursive search
2. **Classification Phase**: Categorize findings into text content, visual assets, configuration, and templates
3. **Replacement Phase**: Apply systematic replacements with validation
4. **Verification Phase**: Validate all changes and ensure no broken references

### Key Design Decisions

**Decision 1: Preserve Technical References**
- Rationale: Database names, technical schema references, and existing tenant subdomain patterns should remain unchanged to maintain backward compatibility
- Impact: Only user-facing and documentation references will change

**Decision 2: Text Replacement Over Asset Replacement**
- Rationale: No MD Code Inc. logos are provided, so visual branding will use clean text rendering
- Impact: Remove or replace logo image tags with styled text: "MD Code Inc."

**Decision 3: Non-Destructive Database Strategy**
- Rationale: Existing production tenant databases and user data must remain untouched
- Impact: Only seeders and default values for NEW installations will be updated

**Decision 4: Maintain HTTP User-Agent Format**
- Rationale: External API integrations (OpenStreetMap Nominatim) may track user-agents
- Impact: Update user-agent to "MD Code Inc. ERP/1.0" but maintain functional format

## Components and Interfaces

### Component 1: Text Replacement Engine

**Purpose**: Systematically replace all text references to Tewos with MD Code Inc.

**Replacement Rules**:
```
"Tewos" → "MD Code Inc."
"TewosHR" → "MD Code Inc. ERP"
"Tewos ERP" → "MD Code Inc. ERP"
"Tewos Technologies" → "MD Code Inc."
"Tewos Technology" → "MD Code Inc."
"TewosSmartHR" → "MD Code Inc. ERP"
"© Tewos" → "© 2026 MD Code Inc. All Rights Reserved."
"© 2024 Tewos" → "© 2026 MD Code Inc. All Rights Reserved."
"© 2025 Tewos" → "© 2026 MD Code Inc. All Rights Reserved."
```

**File Types to Process**:
- PHP files (.php)
- Blade templates (.blade.php)
- JavaScript/TypeScript files (.js, .ts, .jsx, .tsx)
- CSS/SCSS files (.css, .scss)
- JSON files (language files, config files)
- Markdown files (.md)
- Configuration files (.env.example, .env.production.example)

**Exclusions**:
- Do not modify: vendor/, node_modules/, storage/, bootstrap/cache/
- Do not modify: .git/, compiled assets
- Preserve technical comments that reference database schemas for clarity

### Component 2: Visual Asset Manager

**Purpose**: Handle all logo, favicon, and image asset updates

**Asset Discovery Strategy**:
```
Search locations:
- public/images/
- public/assets/
- resources/assets/img/
- Modules/*/public/
- Modules/*/resources/assets/
```

**Asset Handling Rules**:
1. If Tewos logo file found (logo.png, logo.svg, favicon.ico, etc.):
   - If MD Code Inc. replacement exists: Replace file
   - If no replacement exists: Remove file and update template references to use text
2. Update all `<img>` tags referencing Tewos assets
3. Update CSS background-image properties referencing Tewos assets
4. Verify no broken image links remain after completion

**Text-Based Logo Rendering**:
When no logo asset exists, use styled text in templates:
```blade
<div class="brand-logo">
    <span class="brand-text">MD Code Inc.</span>
</div>
```

### Component 3: Template Processor

**Purpose**: Update all email, PDF, and document templates

**Email Template Updates**:
- Location: resources/views/emails/, Modules/*/resources/views/emails/
- Update header branding sections
- Update footer copyright notices
- Replace logo references with text or new assets
- Update email subject lines containing "Tewos"

**PDF Template Updates**:
- Controllers generating PDFs: PayrollProcessingController, InvoiceController, etc.
- PDF libraries: barryvdh/laravel-snappy, wkhtmltopdf
- Update PDF header/footer templates
- Replace company name in report titles
- Update letterhead sections

**Report Types to Update**:
- Payroll reports
- Invoices and receipts
- Employee certificates
- Payslips
- Purchase orders
- Sales orders
- Financial statements

### Component 4: Configuration Updater

**Purpose**: Update all configuration files and environment templates

**Files to Update**:
```
.env.example
.env.production.example
config/app.php (APP_NAME default)
package.json (name field - optional)
```

**Configuration Changes**:
- APP_NAME: "TewosHR" → "MD Code Inc. ERP"
- Database prefix references: Keep functional, update comments only
- User-Agent strings: "TewosSmartHR/1.0" → "MD Code Inc. ERP/1.0"
- Domain references in comments: Update for clarity

### Component 5: Database Seeder Updater

**Purpose**: Update default values in database seeders without affecting existing data

**Seeders to Update**:
```
database/seeders/
├── BusinessSeeder.php (default business name)
├── BusinessTableSeeder.php (demo business)
└── TenantSetupSeeder.php (tenant defaults)

Modules/Superadmin/database/seeders/
└── (Any super admin default data)
```

**Seeder Update Rules**:
1. Update default business names: "Tewos Support" → "MD Code Inc."
2. Update demo company names: "Tewos Company" → "MD Code Inc."
3. Update tenant default settings: theme name "Tewos HR" → "MD Code Inc. ERP"
4. Update subdomain examples in comments: "tewos-support" → "mdcode-demo"
5. Do NOT create migrations to update existing data
6. Do NOT modify production databases directly

**Superadmin Module Settings**:
Location: `Modules/Superadmin/app/Http/Controllers/TenantManagementController.php`
Update default settings array:
```php
['group' => 'theme', 'name' => 'name', 'payload' => json_encode('MD Code Inc. ERP')]
```

### Component 6: Language File Processor

**Purpose**: Update all translation files across all supported languages

**Language File Locations**:
```
resources/lang/*.json (17 languages)
lang/*/ (PHP language files)
Modules/*/lang/ (Module-specific translations)
```

**Supported Languages** (from analysis):
- English (en), Arabic (ar), Azerbaijani (az), Bengali (bn)
- German (de), Spanish (es), Persian (fa), French (fr)
- Croatian (hr), Italian (it), Japanese (ja), Dutch (nl)
- Polish (pl), Portuguese (pt), Romanian (ro), Russian (ru)
- Turkish (tr)

**Language Update Strategy**:
1. For JSON files: Replace translation values containing "Tewos"
2. For PHP array files: Replace values in lang arrays
3. Preserve translation keys (do not modify keys)
4. Update only branding-related translations
5. Do not modify technical term translations

### Component 7: Metadata and SEO Updater

**Purpose**: Update browser metadata, Open Graph tags, and SEO content

**Locations to Update**:
- resources/views/layouts/app.blade.php (main layout)
- resources/views/layouts/guest.blade.php (auth layout)
- public/manifest.json (if exists)
- Meta tags in individual view files

**Metadata Updates**:
```blade
<title>{{ config('app.name') }} - @yield('title')</title>
<meta name="description" content="MD Code Inc. ERP System">
<meta property="og:site_name" content="MD Code Inc. ERP">
<meta property="og:title" content="@yield('title') - MD Code Inc. ERP">
<meta name="twitter:title" content="MD Code Inc. ERP">
```

### Component 8: Multi-Tenant Configuration Handler

**Purpose**: Update multi-tenant configuration while preserving existing tenants

**Files to Update**:
```
Modules/Superadmin/app/Services/TenantService.php
database/seeders/BusinessSeeder.php (tenant examples)
config/tenancy.php (if it contains branding)
```

**Tenant Update Rules**:
1. Update DEFAULT tenant prefix comments (functional value stays)
2. Update example domain references in comments
3. Update default tenant settings for NEW tenants only
4. Do NOT modify existing tenant database names
5. Do NOT rename existing tenant subdomains

### Component 9: Documentation Updater

**Purpose**: Update all markdown documentation and README files

**Documentation Files**:
```
README.md
implementation_procedure_of_superadmin.md
Leave Management System Upgrade to Odoo-Style Interface.md
tewoshr_VS_erp.ettech.et_VS_Odoo.md
two_person_action_plan.md
LEAVE_IMPLEMENTATION_PROGRESS.md
```

**Documentation Update Strategy**:
1. Replace "TewosHR" → "MD Code Inc. ERP" in titles and body
2. Replace "Tewos Technologies" → "MD Code Inc."
3. Update company references in examples
4. Preserve technical schema references (e.g., "TewosHR schema" can remain in technical context)
5. Update copyright notices

### Component 10: UI Component Updater

**Purpose**: Update all user interface components with new branding

**Livewire Components to Update**:
```
app/Livewire/*.php
resources/views/livewire/*.blade.php
```

**Blade Components to Update**:
```
resources/views/components/*
resources/views/layouts/*
resources/views/partials/*
```

**Key UI Elements**:
- Login page (resources/views/auth/login.blade.php)
- Dashboard header (resources/views/dashboard.blade.php or equivalent)
- Sidebar branding (resources/views/layouts/sidebar.blade.php or partials)
- Navbar branding (resources/views/partials/navbar.blade.php or equivalent)
- Footer copyright (resources/views/partials/footer.blade.php or equivalent)
- Error pages (resources/views/errors/*.blade.php)

### Component 11: HTTP Client User-Agent Updater

**Purpose**: Update User-Agent strings in HTTP client calls

**Locations**:
```
app/Jobs/ResolveAttendanceLocation.php
app/Http/Controllers/EmployeeAttendanceController.php
app/Livewire/EmployeeAttendance.php
```

**Update Pattern**:
```php
// Before
'User-Agent' => 'TewosSmartHR/1.0 (https://smarthr.tewostechsolutions.com)'

// After
'User-Agent' => 'MD Code Inc. ERP/1.0 (https://mdcodeinc.com)'
```

**Important**: Maintain proper User-Agent format for API compliance (OpenStreetMap Nominatim requires valid User-Agent)

## Data Models

### File Reference Model

**Purpose**: Track all files modified during rebranding

```
FileReference {
    path: string          // Relative path from project root
    type: enum            // 'text', 'asset', 'config', 'template', 'seeder', 'lang'
    occurrences: int      // Number of Tewos references found
    status: enum          // 'pending', 'processed', 'verified', 'skipped'
    backupPath: string    // Backup file location (optional)
}
```

### Replacement Log Model

**Purpose**: Track each replacement operation for verification

```
ReplacementLog {
    filePath: string      // File where replacement occurred
    lineNumber: int       // Line number of change
    oldValue: string      // Original text
    newValue: string      // Replacement text
    timestamp: datetime   // When replacement was made
    type: enum            // 'text', 'asset_reference', 'config', 'metadata'
}
```

### Asset Inventory Model

**Purpose**: Track all visual assets and their status

```
AssetInventory {
    originalPath: string    // Original Tewos asset path
    assetType: enum         // 'logo', 'favicon', 'icon', 'image'
    action: enum            // 'replaced', 'removed', 'text_substitution'
    newPath: string?        // New asset path if replaced
    referencingFiles: []    // Files that reference this asset
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Complete Tewos Reference Removal

*For any* file in the project (excluding vendor/, node_modules/, .git/), after rebranding completion, searching for the string "Tewos" (case-insensitive) should return zero occurrences in user-facing content.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4**

### Property 2: Consistent MD Code Inc. Branding

*For any* file containing company name references, all company name references should be either "MD Code Inc." or "MD Code Inc. ERP" (not mixed with legacy Tewos names).

**Validates: Requirements 2.1, 2.2, 2.3**

### Property 3: Copyright Notice Standardization

*For any* file containing a copyright notice (views, templates, documentation), the copyright string should match exactly "© 2026 MD Code Inc. All Rights Reserved."

**Validates: Requirements 14.1, 14.2, 14.3, 14.4, 14.5**

### Property 4: Asset Reference Integrity

*For any* HTML template or view file containing an `<img>` tag or CSS `background-image` property, the referenced asset path should resolve to an existing file or be replaced with text rendering.

**Validates: Requirements 12.1, 12.2, 1.2**

### Property 5: Configuration Consistency

*For any* configuration file (.env.example, config/app.php), all APP_NAME or application name references should contain "MD Code Inc. ERP" and no "Tewos" references.

**Validates: Requirements 4.6, 9.1, 9.2**

### Property 6: Email Template Branding Consistency

*For any* email template file in resources/views/emails/ or Modules/*/resources/views/emails/, the template should contain "MD Code Inc." branding in header sections and "© 2026 MD Code Inc. All Rights Reserved." in footer sections.

**Validates: Requirements 5.1, 5.2, 5.3**

### Property 7: PDF Template Branding Consistency

*For any* PDF generation controller method, the generated PDF output should contain "MD Code Inc." or "MD Code Inc. ERP" in the document header and no "Tewos" references.

**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

### Property 8: Seeder Default Value Update

*For any* database seeder file in database/seeders/ or Modules/*/database/seeders/, default company name values should be "MD Code Inc." and not "Tewos Support" or "Tewos Company".

**Validates: Requirements 7.2, 7.3, 7.4**

### Property 9: Language File Translation Consistency

*For any* language JSON file in resources/lang/*.json, if a translation value originally contained "Tewos", it should now contain "MD Code Inc." or "MD Code Inc. ERP".

**Validates: Requirements 8.1, 8.2, 8.3**

### Property 10: Business Logic Preservation

*For any* PHP class file containing business logic (controllers, models, services), the method signatures, class names (unless branding-related), and logic flow should remain unchanged after rebranding.

**Validates: Requirements 13.1, 13.3, 13.4, 13.5, 13.6, 13.7**

### Property 11: UI Component Branding

*For any* key UI component (login page, dashboard, sidebar, navbar, footer, error pages), the rendered HTML should display "MD Code Inc." or "MD Code Inc. ERP" branding and no "Tewos" text.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**

### Property 12: Metadata Branding Consistency

*For any* HTML page layout containing `<meta>` tags or `<title>` tags, the content should reference "MD Code Inc. ERP" and not "Tewos" or "TewosHR".

**Validates: Requirements 4.1, 4.3, 4.4, 4.5**

## Error Handling

### Error Scenarios and Handling

**Scenario 1: File Not Found During Processing**
- **Cause**: File was deleted or moved during rebranding
- **Handling**: Log warning, skip file, continue with remaining files
- **Recovery**: Review log after completion, manually verify if file was intentionally removed

**Scenario 2: Permission Denied on File Write**
- **Cause**: Insufficient file permissions
- **Handling**: Log error with file path, skip file, continue with remaining files
- **Recovery**: Fix permissions and re-run rebranding on affected files

**Scenario 3: Binary File Encountered**
- **Cause**: Text replacement attempted on binary file (image, compiled asset)
- **Handling**: Skip file with log entry, do not modify binary files
- **Prevention**: Filter file types before processing (only process text-based files)

**Scenario 4: Asset Reference Without Replacement**
- **Cause**: Tewos logo referenced in template but no MD Code Inc. replacement exists
- **Handling**: Replace `<img>` tag with styled text div, log the change
- **Validation**: Verify no broken image links in final output

**Scenario 5: Regex Replacement Ambiguity**
- **Cause**: Text pattern matches unintended string (e.g., "TewosHR schema" in comment)
- **Handling**: Use precise replacement rules, manual review for ambiguous cases
- **Prevention**: Implement dry-run mode to preview changes before applying

**Scenario 6: Database Seeder with Existing Data**
- **Cause**: Seeder already executed in production, data exists
- **Handling**: Seeders only affect NEW installations, document that existing data stays unchanged
- **Communication**: Clearly document that production data is not modified

**Scenario 7: Multi-Tenant Database Naming Conflict**
- **Cause**: Tenant database prefix change could break existing tenants
- **Handling**: Do NOT change functional prefix value, only update comments
- **Validation**: Ensure existing tenant connections still work

**Scenario 8: Language File JSON Parse Error**
- **Cause**: Malformed JSON after replacement
- **Handling**: Validate JSON after replacement, rollback file if invalid
- **Prevention**: Use JSON-aware replacement (parse → modify → encode)

**Scenario 9: Git Merge Conflicts**
- **Cause**: Rebranding changes conflict with ongoing development
- **Handling**: Perform rebranding in isolated branch, coordinate with team
- **Prevention**: Freeze other development during rebranding, or coordinate timing

**Scenario 10: Broken HTTP User-Agent Format**
- **Cause**: Incorrect User-Agent format breaks API calls
- **Handling**: Maintain proper User-Agent format: "ProductName/Version (URL)"
- **Validation**: Test API calls after User-Agent change

## Testing Strategy

### Dual Testing Approach

The testing strategy employs both unit tests and property-based tests to ensure comprehensive coverage:

- **Unit tests**: Verify specific examples, edge cases, and error conditions
- **Property tests**: Verify universal properties across all inputs using randomization

Both approaches are complementary and necessary. Unit tests catch concrete bugs and validate specific scenarios, while property-based tests verify general correctness across a wide range of inputs.

### Property-Based Testing

**Property Testing Library**: Use **PHPUnit** with **Eris** (PHP property-based testing library) for property tests.

**Configuration**:
- Minimum 100 iterations per property test
- Each test must reference its design document property
- Tag format: `@test Feature: erp-rebranding-tewos-to-mdcode, Property {number}: {property_text}`

**Property Test Scope**:
- File content verification (Properties 1, 2, 3)
- Asset integrity (Property 4)
- Template consistency (Properties 6, 7, 11, 12)
- Configuration validation (Property 5)
- Business logic preservation (Property 10)

### Unit Testing

**Unit Test Scope**:
- Specific file replacements (test exact before/after on sample files)
- Edge cases: empty files, files with only comments, mixed case variations
- Error conditions: file not found, permission denied, binary file handling
- Integration: verify login page renders correctly, dashboard shows new branding
- Seeder validation: run seeder and check default values match "MD Code Inc."

**Unit Test Examples**:
1. Test login page contains "MD Code Inc. ERP" in title
2. Test footer contains exact copyright text
3. Test email template header contains MD Code Inc. branding
4. Test PDF generation includes correct company name
5. Test config/app.php default APP_NAME is correct
6. Test no broken image links exist after asset removal

### Manual Testing Checklist

After automated testing:
1. **Visual Inspection**: Load application, navigate key pages (login, dashboard, settings)
2. **Email Test**: Trigger email notification, verify branding
3. **PDF Test**: Generate report/invoice, verify branding
4. **Multi-Language Test**: Switch languages, verify branding consistency
5. **Error Page Test**: Trigger 404/500 error, verify branding
6. **Multi-Tenant Test**: Access tenant subdomain, verify branding

### Verification Procedure

1. **Pre-Rebranding Audit**:
   - Run grep search for all "Tewos" occurrences
   - Document count and file locations
   - Create baseline inventory

2. **Post-Rebranding Verification**:
   - Run grep search again (should find zero user-facing occurrences)
   - Run property tests (all should pass)
   - Run unit tests (all should pass)
   - Execute manual testing checklist
   - Compare before/after counts

3. **Deliverables**:
   - List of all modified files (from FileReference model)
   - List of removed assets (from AssetInventory model)
   - List of replaced logos (from AssetInventory model)
   - Replacement log (from ReplacementLog model)
   - Test results (property tests + unit tests)
   - Confirmation: No "Tewos" in user-facing content
   - Confirmation: "MD Code Inc." branding throughout
   - Confirmation: No business logic changes

### Rollback Strategy

In case of issues:
1. **Git-Based Rollback**: If using version control, revert to pre-rebranding commit
2. **Backup-Based Rollback**: Restore files from backup directory
3. **Selective Rollback**: Revert specific files if only partial issues found
4. **Database Rollback**: Re-run original seeders if needed (only affects new installations)

### Success Criteria

Rebranding is complete and successful when:
- ✅ All property tests pass (11 properties)
- ✅ All unit tests pass
- ✅ Manual testing checklist complete
- ✅ Zero "Tewos" references in user-facing content (verified by grep)
- ✅ All UI components display MD Code Inc. branding
- ✅ All templates (email, PDF) display MD Code Inc. branding
- ✅ No broken asset links
- ✅ Application functions identically to pre-rebranding state
- ✅ All deliverables provided (file lists, logs, confirmation reports)
