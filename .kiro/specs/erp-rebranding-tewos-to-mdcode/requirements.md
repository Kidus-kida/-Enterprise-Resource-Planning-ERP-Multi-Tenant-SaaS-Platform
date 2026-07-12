# Requirements Document

## Introduction

This document specifies requirements for a comprehensive rebranding of the ERP system from "Tewos" to "MD Code Inc." The rebranding affects all visual assets, text content, UI components, metadata, templates, database defaults, and language files throughout the entire system. This is strictly a branding change and must not alter any business logic or user-generated data.

## Glossary

- **System**: The Laravel-based ERP application including all modules, views, and configuration files
- **Branding_Asset**: Any logo, favicon, image, icon, or visual element displaying the "Tewos" brand
- **Text_Reference**: Any string, variable, or content containing "Tewos", "TewosHR", "Tewos ERP", or related terms
- **Template**: Email templates, PDF templates, reports, invoices, certificates, and other document formats
- **Metadata**: Browser titles, manifest files, meta descriptions, Open Graph tags, and SEO-related content
- **Seeder**: Database seeder file that populates default or initial data
- **Central_Database**: The main application database (not tenant-specific databases)
- **Tenant_Database**: Individual client/tenant databases in the multi-tenant architecture
- **MD_Code_Inc**: The new brand name replacing all Tewos references

## Requirements

### Requirement 1: Visual Asset Replacement

**User Story:** As a system administrator, I want all Tewos logos and visual branding replaced with MD Code Inc. branding, so that the application reflects the new company identity.

#### Acceptance Criteria

1. WHEN the System identifies a Branding_Asset containing Tewos branding, THEN the System SHALL replace it with the corresponding MD Code Inc. asset or remove it
2. WHEN no MD Code Inc. replacement asset exists, THEN the System SHALL display clean text "MD Code Inc." instead of broken image links
3. THE System SHALL update all favicon files to reflect MD Code Inc. branding
4. THE System SHALL replace all logo references in the public/ directory
5. THE System SHALL update all image assets in resources/assets/ and resources/images/

### Requirement 2: Text Content Replacement in Application Code

**User Story:** As a developer, I want all text references to "Tewos" replaced with "MD Code Inc." throughout the codebase, so that the application displays consistent branding.

#### Acceptance Criteria

1. WHEN the System encounters "Tewos" in application code, THEN the System SHALL replace it with "MD Code Inc."
2. WHEN the System encounters "TewosHR" in application code, THEN the System SHALL replace it with "MD Code Inc. ERP"
3. WHEN the System encounters "Tewos ERP" in application code, THEN the System SHALL replace it with "MD Code Inc. ERP"
4. WHEN the System encounters "Tewos Technologies" or "Tewos Technology" in application code, THEN the System SHALL replace it with "MD Code Inc."
5. THE System SHALL update all Text_References in PHP files within app/, Modules/, routes/, config/, and database/ directories
6. THE System SHALL update all Text_References in Blade view files within resources/views/ and Modules/*/resources/views/
7. THE System SHALL update all Text_References in JavaScript and TypeScript files
8. THE System SHALL update all Text_References in CSS and SCSS files

### Requirement 3: User Interface Component Updates

**User Story:** As an end user, I want all visible UI components to display "MD Code Inc." branding, so that I experience consistent branding throughout the application.

#### Acceptance Criteria

1. WHEN a user views the login page, THEN the System SHALL display "MD Code Inc. ERP" branding
2. WHEN a user views the dashboard, THEN the System SHALL display "MD Code Inc." branding in headers and titles
3. WHEN a user views the sidebar navigation, THEN the System SHALL display "MD Code Inc." logo or text
4. WHEN a user views the navbar, THEN the System SHALL display "MD Code Inc." branding
5. WHEN a user views the footer, THEN the System SHALL display "© 2026 MD Code Inc. All Rights Reserved."
6. WHEN a user encounters an error page (404, 500, etc.), THEN the System SHALL display "MD Code Inc." branding
7. THE System SHALL update all Livewire component views to display MD Code Inc. branding

### Requirement 4: Metadata and SEO Updates

**User Story:** As a system administrator, I want all browser metadata and SEO content updated to "MD Code Inc.", so that search engines and browser tabs display the correct branding.

#### Acceptance Criteria

1. WHEN a page loads in the browser, THEN the System SHALL display "MD Code Inc. ERP" or appropriate page-specific title in the browser tab
2. THE System SHALL update the application manifest file with MD Code Inc. branding
3. THE System SHALL update all meta description tags to reference MD Code Inc.
4. THE System SHALL update all Open Graph tags (og:title, og:site_name) to reference MD Code Inc.
5. THE System SHALL update Twitter Card metadata to reference MD Code Inc.
6. THE System SHALL update the application name in config/app.php to "MD Code Inc. ERP"

### Requirement 5: Email Template Rebranding

**User Story:** As a system user, I want all email notifications to display "MD Code Inc." branding, so that external communications reflect the new company identity.

#### Acceptance Criteria

1. WHEN the System sends an email notification, THEN the email SHALL display "MD Code Inc." branding in the header
2. WHEN the System sends an email notification, THEN the email SHALL display "© 2026 MD Code Inc. All Rights Reserved." in the footer
3. THE System SHALL update all email template files in resources/views/emails/ and Modules/*/resources/views/emails/
4. THE System SHALL update all Mailable class references to Tewos with MD Code Inc.
5. THE System SHALL replace Tewos logos in email templates with MD Code Inc. branding

### Requirement 6: PDF Template and Report Rebranding

**User Story:** As a business user, I want all PDF documents (reports, invoices, payslips, certificates) to display "MD Code Inc." branding, so that printed and downloadable documents reflect the new identity.

#### Acceptance Criteria

1. WHEN the System generates a PDF report, THEN the report SHALL display "MD Code Inc." branding in the header
2. WHEN the System generates an invoice, THEN the invoice SHALL display "MD Code Inc." company information
3. WHEN the System generates a payslip, THEN the payslip SHALL display "MD Code Inc." branding
4. WHEN the System generates a certificate, THEN the certificate SHALL display "MD Code Inc." branding
5. THE System SHALL update all PDF template files to replace Tewos references
6. THE System SHALL update PDF generation controllers to use MD Code Inc. branding (e.g., PayrollProcessingController)

### Requirement 7: Database Seeder and Default Settings Updates

**User Story:** As a system administrator, I want all database default values and seeders updated to "MD Code Inc.", so that new installations and tenants receive correct branding.

#### Acceptance Criteria

1. WHEN the System runs database seeders, THEN the seeders SHALL create records with "MD Code Inc." branding where applicable
2. THE System SHALL update BusinessSeeder to use "MD Code Inc." instead of "Tewos Support"
3. THE System SHALL update BusinessTableSeeder to use "MD Code Inc." instead of "Tewos Company"
4. THE System SHALL update TenantManagementController default settings to use "MD Code Inc. ERP" as the theme name
5. THE System SHALL NOT modify existing user-generated data in production databases
6. THE System SHALL update only default values and seeder templates

### Requirement 8: Language File and Translation Updates

**User Story:** As a multilingual user, I want "MD Code Inc." branding to appear correctly in all supported languages, so that the application maintains consistent branding across locales.

#### Acceptance Criteria

1. WHEN the System displays text in any supported language, THEN the text SHALL use "MD Code Inc." branding where company names appear
2. THE System SHALL update all language files in the lang/ directory
3. THE System SHALL update all language files in Modules/*/lang/ directories
4. THE System SHALL maintain translation keys while updating only the branding values

### Requirement 9: Configuration File Updates

**User Story:** As a system administrator, I want all configuration files updated to use "MD Code Inc." branding, so that environment-specific settings reflect the new identity.

#### Acceptance Criteria

1. THE System SHALL update .env.example to replace "TewosHR" with "MD Code Inc. ERP"
2. THE System SHALL update .env.production.example to replace "TewosHR" with "MD Code Inc. ERP"
3. THE System SHALL update database prefix references where appropriate for clarity
4. THE System SHALL update User-Agent strings in HTTP clients to reference "MD Code Inc." instead of "TewosSmartHR"
5. THE System SHALL maintain functional configuration values (do not break API integrations)

### Requirement 10: Documentation and Comment Updates

**User Story:** As a developer, I want all documentation and code comments updated to reference "MD Code Inc.", so that the codebase documentation is accurate and consistent.

#### Acceptance Criteria

1. THE System SHALL update README.md to reference MD Code Inc.
2. THE System SHALL update markdown documentation files to reference MD Code Inc.
3. THE System SHALL update code comments that reference Tewos company or product names
4. THE System SHALL preserve technical comments that reference database schemas (e.g., "TewosHR schema" in technical context may remain for clarity)

### Requirement 11: Multi-Tenant Configuration Updates

**User Story:** As a system administrator managing multiple tenants, I want tenant configuration and domain references updated appropriately, so that the multi-tenant system reflects the new branding where appropriate.

#### Acceptance Criteria

1. THE System SHALL update tenant domain examples in seeders to use appropriate MD Code Inc. domain references
2. THE System SHALL update TenantService to use appropriate database prefixes
3. THE System SHALL maintain backward compatibility with existing tenant databases
4. THE System SHALL NOT rename existing tenant databases (only update defaults for new tenants)

### Requirement 12: Asset Integrity and Link Validation

**User Story:** As a quality assurance tester, I want all asset links validated after rebranding, so that no broken images or missing resources exist in the application.

#### Acceptance Criteria

1. WHEN the System completes rebranding, THEN the System SHALL verify all image links resolve to valid resources
2. WHEN a Branding_Asset is removed, THEN the System SHALL ensure corresponding HTML/template references are updated
3. THE System SHALL provide a list of all removed Tewos assets
4. THE System SHALL provide a list of all replaced logo files

### Requirement 13: Business Logic Preservation

**User Story:** As a system administrator, I want absolute certainty that no business logic has been modified during rebranding, so that the system continues to function identically after the change.

#### Acceptance Criteria

1. WHEN the System completes rebranding, THEN all business logic SHALL remain unchanged
2. THE System SHALL NOT modify database migrations (only seeders and defaults)
3. THE System SHALL NOT modify authentication logic
4. THE System SHALL NOT modify authorization logic
5. THE System SHALL NOT modify calculation logic (payroll, leave, attendance, etc.)
6. THE System SHALL NOT modify API endpoints or route definitions
7. THE System SHALL NOT modify model relationships or database queries beyond text content

### Requirement 14: Copyright Notice Standardization

**User Story:** As a legal compliance officer, I want all copyright notices standardized to "© 2026 MD Code Inc. All Rights Reserved.", so that the company's intellectual property is properly attributed.

#### Acceptance Criteria

1. WHEN the System displays a copyright notice, THEN the notice SHALL read "© 2026 MD Code Inc. All Rights Reserved."
2. THE System SHALL update copyright notices in footers across all views
3. THE System SHALL update copyright notices in email templates
4. THE System SHALL update copyright notices in PDF templates
5. THE System SHALL update copyright notices in documentation files
