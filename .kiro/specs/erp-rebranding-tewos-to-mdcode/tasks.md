# Implementation Plan: ERP Rebranding from Tewos to MD Code Inc.

## Overview

This implementation plan provides a systematic approach to rebrand the entire ERP system from "Tewos" to "MD Code Inc." The tasks are organized to minimize risk by starting with analysis, followed by incremental replacements in isolated areas, and concluding with verification. Each task builds on previous work to ensure comprehensive coverage without breaking functionality.

## Tasks

- [ ] 1. Initial analysis and discovery
  - [x] 1.1 Create comprehensive file inventory of all Tewos references
    - Use recursive grep to search entire codebase (excluding vendor/, node_modules/, .git/)
    - Generate detailed report with file paths, line numbers, and occurrence counts
    - Categorize findings: code, views, assets, config, seeders, docs
    - Save inventory to `.kiro/specs/erp-rebranding-tewos-to-mdcode/tewos-inventory.json`
    - _Requirements: 2.5, 2.6, 2.7, 2.8, 13.1_

  - [x] 1.2 Create visual asset inventory
    - Search for all logo files in public/, resources/assets/, Modules/*/public/
    - Identify all favicon files and icon assets
    - Document all image references in templates that point to Tewos assets
    - Create asset list in `.kiro/specs/erp-rebranding-tewos-to-mdcode/asset-inventory.json`
    - _Requirements: 1.1, 1.4, 1.5, 12.3_

  - [x] 1.3 Analyze business logic files for preservation verification
    - Identify all controller, model, service, and job files
    - Document method signatures and class structures
    - Create baseline checksum or snapshot for critical business logic files
    - This ensures we can verify no business logic changes post-rebranding
    - _Requirements: 13.1, 13.3, 13.4, 13.5, 13.6, 13.7_

- [ ] 2. Update configuration files
  - [x] 2.1 Update environment template files
    - Update `.env.example`: Change APP_NAME from "TewosHR" to "MD Code Inc. ERP"
    - Update `.env.production.example`: Change APP_NAME to "MD Code Inc. ERP"
    - Update database prefix comments for clarity (keep functional values)
    - Update any domain references in comments
    - _Requirements: 9.1, 9.2, 9.3_

  - [x] 2.2 Update config/app.php
    - Change default APP_NAME from 'Laravel' to 'MD Code Inc. ERP'
    - Verify no other Tewos references in config files
    - _Requirements: 4.6, 9.1_

  - [x] 2.3 Update package.json name field (optional)
    - Change name from "tewoshr" to "mdcode-erp" if desired
    - This is cosmetic for npm package identification
    - _Requirements: 2.8_

- [ ] 3. Update HTTP User-Agent strings
  - [x] 3.1 Update User-Agent in Jobs
    - Update `app/Jobs/ResolveAttendanceLocation.php`
    - Change User-Agent from "TewosSmartHR/1.0" to "MD Code Inc. ERP/1.0"
    - Update URL reference if applicable
    - _Requirements: 9.4, 9.5_

  - [x] 3.2 Update User-Agent in Controllers
    - Update `app/Http/Controllers/EmployeeAttendanceController.php`
    - Change User-Agent string to "MD Code Inc. ERP/1.0"
    - _Requirements: 9.4, 9.5_

  - [x] 3.3 Update User-Agent in Livewire Components
    - Update `app/Livewire/EmployeeAttendance.php`
    - Change User-Agent string to "MD Code Inc. ERP/1.0"
    - _Requirements: 9.4, 9.5_

- [ ] 4. Update database seeders and default values
  - [x] 4.1 Update BusinessSeeder.php
    - Change 'name' from 'Tewos Support' to 'MD Code Inc.'
    - Update 'subdomain' from 'tewos-support' to 'mdcode-demo' or appropriate value
    - Update tenant domain examples to use MD Code Inc. domain
    - _Requirements: 7.2, 11.1_

  - [x] 4.2 Update BusinessTableSeeder.php
    - Change default business name from 'Tewos Company' to 'MD Code Inc.'
    - _Requirements: 7.3_

  - [x] 4.3 Update TenantSetupSeeder.php
    - Review and update any Tewos references in comments or data
    - Update database name references in comments (keep functional names)
    - _Requirements: 7.1, 11.3_

  - [x] 4.4 Update Superadmin TenantManagementController default settings
    - Update `Modules/Superadmin/app/Http/Controllers/TenantManagementController.php`
    - Change theme name setting from 'Tewos HR' to 'MD Code Inc. ERP'
    - Update the payload in the settings array
    - _Requirements: 7.4_

  - [x] 4.5 Update TenantService database prefix handling
    - Update `Modules/Superadmin/app/Services/TenantService.php`
    - Update comments regarding database prefix (functional value stays unchanged)
    - _Requirements: 11.2_

- [ ] 5. Checkpoint - Verify configuration and seeder changes
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Update core application code (app/ directory)
  - [x] 6.1 Update PHP files in app/
    - Search all .php files in app/ directory
    - Replace all text occurrences using replacement rules from design
    - Preserve business logic, only change text/comments
    - Update any Tewos references in comments where appropriate
    - _Requirements: 2.5, 13.1_

  - [x] 6.2 Update specific files identified in analysis
    - `app/AccountGroup.php` (comment update)
    - `app/Http/Controllers/PayrollProcessingController.php` (PDF report title)
    - Any other files from the inventory with Tewos references
    - _Requirements: 2.5, 6.6_

- [ ] 7. Update view templates (resources/views/)
  - [x] 7.1 Update authentication views
    - Update `resources/views/auth/login.blade.php` and related auth views
    - Replace logos, update titles to "MD Code Inc."
    - Update any welcome text or branding elements
    - _Requirements: 3.1_

  - [x] 7.2 Update layout files
    - Update `resources/views/layouts/app.blade.php`
    - Update `resources/views/layouts/guest.blade.php`
    - Update any other layout files in resources/views/layouts/
    - Replace branding in headers, update page titles
    - _Requirements: 3.2, 4.1_

  - [x] 7.3 Update partial files (sidebar, navbar, footer)
    - Update `resources/views/partials/sidebar.blade.php` (if exists)
    - Update `resources/views/partials/navbar.blade.php` (if exists)
    - Update `resources/views/partials/footer.blade.php` (if exists)
    - Replace logo references with text or new assets
    - Update copyright to "© 2026 MD Code Inc. All Rights Reserved."
    - _Requirements: 3.3, 3.4, 3.5, 14.2_

  - [x] 7.4 Update error pages
    - Update all files in `resources/views/errors/`
    - Update 404.blade.php, 500.blade.php, and any other error templates
    - Replace Tewos branding with MD Code Inc.
    - _Requirements: 3.6_

  - [x] 7.5 Update all other view files in resources/views/
    - Process remaining subdirectories: apps/, company/, emails/, etc.
    - Replace all Tewos text references
    - Update any remaining logo references
    - _Requirements: 2.6, 3.7_

- [ ] 8. Update email templates
  - [x] 8.1 Update email template views
    - Update all files in `resources/views/emails/`
    - Replace header branding with MD Code Inc.
    - Update footer copyright to "© 2026 MD Code Inc. All Rights Reserved."
    - Replace any logo references
    - _Requirements: 5.1, 5.2, 5.3, 14.3_

  - [x] 8.2 Update email Mailable classes
    - Search for Mailable classes in app/Mail/
    - Update any subject lines containing "Tewos"
    - Update any hardcoded company references in mail data
    - _Requirements: 5.4_

- [ ] 9. Update PDF templates and controllers
  - [x] 9.1 Update PDF generation controllers
    - Update `app/Http/Controllers/PayrollProcessingController.php`
    - Change report title from "TEWOS HR - Payroll Report" to "MD Code Inc. -  Payroll Report"
    - Search for other controllers generating PDFs (Invoice, Certificate, etc.)
    - _Requirements: 6.1, 6.6_

  - [x] 9.2 Update PDF template views (if separate files exist)
    - Search for PDF-specific templates in resources/views/
    - Update letterheads, headers, footers with MD Code Inc. branding
    - Update copyright notices in PDF templates
    - _Requirements: 6.2, 6.3, 6.4, 6.5, 14.4_

- [ ] 10. Checkpoint - Verify core app and template changes
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Update Livewire components
  - [x] 11.1 Update Livewire component classes
    - Update all files in `app/Livewire/`
    - Replace Tewos references in class properties, methods, comments
    - _Requirements: 2.5_

  - [x] 11.2 Update Livewire component views
    - Update all files in `resources/views/livewire/`
    - Replace branding text and logo references
    - _Requirements: 3.7_

- [ ] 12. Update all Modules (Modules/* directory)
  - [x] 12.1 Update Superadmin module
    - Update PHP files in `Modules/Superadmin/app/`
    - Update views in `Modules/Superadmin/resources/views/`
    - Update language files in `Modules/Superadmin/lang/`
    - Update any module-specific configuration
    - _Requirements: 2.5, 2.6, 8.2_

  - [x] 12.2 Update remaining modules (Accounting, Sales, Purchase, etc.)
    - For each module in Modules/ (except Superadmin, already done):
      - Update PHP files in app/ subdirectory
      - Update views in resources/views/ subdirectory
      - Update language files in lang/ subdirectory
      - Update any seeders in database/seeders/
    - Modules to process: Accounting, Contacts, Crm, Logistics, ProductCatalogue, Products, Project, Purchase, Roles, Sales, StockAdjustment, Whiteboard
    - _Requirements: 2.5, 2.6, 8.2_

- [ ] 13. Update language files (internationalization)
  - [x] 13.1 Update root language JSON files
    - Update all JSON files in `resources/lang/*.json`
    - Parse JSON, replace Tewos in translation values, encode back
    - Validate JSON integrity after replacement
    - Languages: ar, az, bn, de, en, es, fa, fr, hr, it, ja, nl, pl, pt, ro, ru, tr
    - _Requirements: 8.1, 8.2_

  - [x] 13.2 Update root language PHP array files
    - Update all PHP files in `resources/lang/*/` subdirectories
    - Replace Tewos in translation array values
    - Maintain translation keys unchanged
    - _Requirements: 8.1, 8.3_

  - [x] 13.3 Update module language files
    - Already covered in task 12 (module updates)
    - Verify all module lang/ directories processed
    - _Requirements: 8.2_

- [ ] 14. Update metadata and SEO elements
  - [x] 14.1 Update meta tags in layout files
    - Update meta description tags in layouts
    - Update Open Graph tags (og:site_name, og:title)
    - Update Twitter Card metadata
    - _Requirements: 4.3, 4.4, 4.5_

  - [x] 14.2 Update manifest file (if exists)
    - Check for `public/manifest.json` or `public/site.webmanifest`
    - Update name and short_name fields to "MD Code Inc. ERP"
    - _Requirements: 4.2_

  - [x] 14.3 Update page titles throughout application
    - Verify all blade templates use config('app.name') or update hardcoded titles
    - Ensure consistency across all pages
    - _Requirements: 4.1_

- [ ] 15. Update and manage visual assets
  - [x] 15.1 Remove or replace Tewos logo files
    - Process each asset in the asset inventory (from task 1.2)
    - For each Tewos logo: remove file or replace with MD Code Inc. logo (if provided)
    - Document all removed assets
    - _Requirements: 1.1, 12.3_

  - [x] 15.2 Update template references to removed assets
    - For each removed logo, find all template references (from asset inventory)
    - Replace `<img>` tags with styled text: `<span class="brand-text">MD Code Inc.</span>`
    - Add CSS styling if needed for text-based branding
    - _Requirements: 1.2, 12.2_

  - [x] 15.3 Update favicon files
    - Replace favicon.ico, apple-touch-icon.png, and other icon files
    - If no MD Code Inc. icons provided, generate simple text-based favicon
    - _Requirements: 1.3_

  - [ ] 15.4 Update CSS/SCSS files referencing assets
    - Search for background-image properties in CSS/SCSS files
    - Update or remove references to Tewos logo assets
    - _Requirements: 2.8_

- [ ] 16. Update documentation files
  - [x] 16.1 Update README.md
    - Replace "TewosHR" with "MD Code Inc. ERP"
    - Replace company references with MD Code Inc.
    - Update any installation or setup instructions
    - _Requirements: 10.1_

  - [x] 16.2 Update markdown documentation files
    - Update `implementation_procedure_of_superadmin.md`
    - Update `Leave Management System Upgrade to Odoo-Style Interface.md`
    - Update `tewoshr_VS_erp.ettech.et_VS_Odoo.md`
    - Update `two_person_action_plan.md`
    - Update `LEAVE_IMPLEMENTATION_PROGRESS.md`
    - Replace Tewos references with MD Code Inc. where appropriate
    - _Requirements: 10.2_

  - [x] 16.3 Update code comments
    - Review and update inline code comments containing Tewos references
    - Preserve technical comments that reference schemas for clarity
    - _Requirements: 10.3, 10.4_

- [ ] 17. Update JavaScript and CSS files
  - [x] 17.1 Update JavaScript files
    - Search for Tewos references in `resources/js/` directory
    - Search for Tewos references in module JavaScript files
    - Replace any hardcoded strings or comments
    - _Requirements: 2.7_

  - [x] 17.2 Update CSS and SCSS files
    - Search for Tewos references in `resources/css/` and `resources/assets/scss/`
    - Update any CSS class names or comments (if applicable)
    - Update background-image URLs if needed (already covered in 15.4)
    - _Requirements: 2.8_

- [ ] 18. Checkpoint - Verify all file changes complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 19. Final verification and validation
  - [ ] 19.1 Run comprehensive Tewos reference search
    - Execute grep search across entire codebase (excluding vendor/, node_modules/)
    - Verify zero occurrences of "Tewos" in user-facing content
    - Document any remaining technical references (should be minimal)
    - Generate final verification report
    - _Requirements: 12.1_

  - [ ] 19.2 Validate asset reference integrity
    - Check all image src attributes in templates resolve to existing files
    - Verify no broken image links (404s)
    - Confirm all text-based branding renders correctly
    - _Requirements: 12.1, 12.2_

  - [ ] 19.3 Verify business logic preservation
    - Compare current method signatures with baseline from task 1.3
    - Run checksum comparison on critical business logic files
    - Confirm no structural changes to controllers, models, services
    - _Requirements: 13.1, 13.7_

  - [ ] 19.4 Generate deliverable reports
    - Create list of all modified files (from FileReference model)
    - Create list of removed assets (from AssetInventory model)
    - Create list of replaced assets (from AssetInventory model)
    - Create summary confirmation report
    - Save reports to `.kiro/specs/erp-rebranding-tewos-to-mdcode/reports/`
    - _Requirements: 12.3, 12.4_

  - [ ] 19.5 Manual UI verification checklist
    - Load application and navigate to login page - verify MD Code Inc. branding
    - Navigate to dashboard - verify header and sidebar branding
    - Check footer copyright notice on multiple pages
    - Trigger test email - verify email branding
    - Generate test PDF report - verify PDF branding
    - Switch to different language - verify branding consistency
    - Trigger 404 error - verify error page branding
    - Document all manual verification results
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 5.1, 6.1_

  - [ ] 19.6 Multi-tenant verification (if applicable)
    - Access tenant subdomain - verify branding
    - Verify new tenant creation uses MD Code Inc. defaults
    - Confirm existing tenant data unchanged
    - _Requirements: 11.4_

- [ ] 20. Create final confirmation documentation
  - [ ] 20.1 Document rebranding completion
    - Confirm complete Tewos removal from user-facing content
    - Confirm MD Code Inc. branding throughout application
    - Confirm no business logic changes
    - Provide file modification statistics
    - Provide asset removal/replacement statistics
    - _Requirements: All requirements_

  - [ ] 20.2 Create rollback documentation (optional)
    - Document git commit hash before rebranding
    - Provide rollback instructions if needed
    - Document any special considerations for reverting changes

## Notes

- Tasks are organized to minimize risk: configuration → code → templates → assets → verification
- Each checkpoint allows for validation before proceeding
- Asset management (task 15) is near the end to avoid broken references during development
- Final verification (task 19) is comprehensive and includes manual UI testing
- All file modifications should preserve business logic and functionality
- No tasks modify production databases - only seeders for new installations
- Multi-tenant existing data remains unchanged
