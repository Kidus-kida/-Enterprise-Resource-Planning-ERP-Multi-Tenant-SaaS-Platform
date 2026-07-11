# Implementation Plan: Settings Sidebar Dropdown

## Overview

This plan implements collapsible dropdown groups in the superadmin settings sidebar using Bootstrap 5's collapse component with localStorage state persistence. The implementation modifies the Blade template structure, adds JavaScript for state management, and updates CSS for styling.

## Tasks

- [ ] 1. Extend SettingCategory class with group configuration
  - Add GROUPS constant array to SettingCategory.php with "basic" group definition
  - Include 'label', 'icon', and 'categories' keys for each group
  - Ensure backward compatibility with existing ALL and LABELS constants
  - _Requirements: 1.2, 7.3, 7.7_

- [ ] 2. Restructure settings layout Blade template
  - [ ] 2.1 Create PHP logic to separate grouped and standalone categories
    - Define dropdown groups array from SettingCategory::GROUPS
    - Build array of standalone categories (not in any group)
    - Pass both arrays to template rendering
    - _Requirements: 1.1, 1.2, 1.3_
  
  - [ ] 2.2 Replace flat navigation with dropdown group markup
    - Implement foreach loop for dropdown groups with Bootstrap collapse structure
    - Add data-bs-toggle="collapse" and aria attributes to group headers
    - Nest category links within collapsible divs with "collapse show" classes
    - Add collapse indicator icon (fa-chevron-down) with ms-auto class
    - Render standalone categories after grouped categories
    - _Requirements: 1.1, 1.2, 1.4, 7.6, 8.2_
  
  - [ ] 2.3 Preserve active state logic for nested categories
    - Apply 'nested' class to category links within groups
    - Maintain active class application based on $category variable
    - Ensure active chevron icon only appears on active items
    - _Requirements: 3.1, 3.4, 5.1_

- [ ] 3. Implement CSS styling for dropdown groups
  - [ ] 3.1 Add dropdown group header styles
    - Create .settings-nav-group class for group containers
    - Style .settings-nav-group-header with hover and active states
    - Add visual distinction from regular .settings-nav-item styling
    - Ensure proper spacing and padding
    - _Requirements: 1.5, 2.5, 7.4_
  
  - [ ] 3.2 Style collapse indicator with rotation animation
    - Add transition for transform property on .collapse-indicator
    - Implement rotation transform when parent has .collapsed class
    - Add prefers-reduced-motion media query for accessibility
    - Ensure smooth 0.2s ease timing
    - _Requirements: 2.2, 2.3, 2.4_
  
  - [ ] 3.3 Add nested category indentation
    - Style .settings-nav-item.nested with left padding/margin
    - Ensure visual hierarchy is clear
    - Maintain existing active state styles
    - _Requirements: 1.1, 1.5_
  
  - [ ] 3.4 Add active parent group highlighting
    - Create .settings-nav-group.has-active class styles
    - Add subtle background or border emphasis for active groups
    - Ensure contrast with nested active category
    - _Requirements: 3.2_

- [ ] 4. Implement JavaScript state management
  - [ ] 4.1 Create SettingsDropdownManager module
    - Define module with STORAGE_KEY constant
    - Implement init() method to set up event listeners
    - Add error handling for localStorage unavailability
    - Initialize on DOMContentLoaded event
    - _Requirements: 4.1, 4.5_
  
  - [ ] 4.2 Implement state persistence methods
    - Create saveState(groupKey, isExpanded) function
    - Create restoreStates() function to read from localStorage
    - Use try-catch for localStorage access with fallback
    - Store states as JSON object with group keys as properties
    - _Requirements: 4.1, 4.2, 4.5_
  
  - [ ] 4.3 Implement auto-expand logic for active category
    - Create autoExpandActiveGroup() function
    - Find active category's parent group
    - Programmatically expand that group using Bootstrap API
    - Override saved collapsed state if group contains active category
    - _Requirements: 3.3_
  
  - [ ] 4.4 Add Bootstrap collapse event listeners
    - Listen for 'shown.bs.collapse' event to save expanded state
    - Listen for 'hidden.bs.collapse' event to save collapsed state
    - Extract group key from event target ID
    - Call saveState() with appropriate boolean value
    - _Requirements: 4.1, 4.4_
  
  - [ ] 4.5 Implement parent group highlighting
    - Create highlightActiveGroup() function
    - Find parent group of active category
    - Add 'has-active' class to parent group header
    - Call on page load after active category detection
    - _Requirements: 3.2_
  
  - [ ] 4.6 Add default expanded state handling
    - Check if localStorage has saved states on load
    - If empty or unavailable, set all groups to expanded
    - Apply appropriate Bootstrap classes (show/remove collapsed)
    - _Requirements: 4.3_

- [ ] 5. Checkpoint - Test core dropdown functionality
  - Ensure all tests pass, ask the user if questions arise
  - Verify dropdowns expand/collapse correctly
  - Check localStorage persistence across page reloads
  - Test auto-expand for active categories
  - Validate parent group highlighting

- [ ]* 6. Write property tests for correctness properties
  - [ ]* 6.1 Write property test for all categories accessible
    - **Property 1: All categories remain accessible**
    - **Validates: Requirements 1.3, 5.1**
    - Generate tests for all 19 categories
    - Verify each has correct route link in rendered sidebar
  
  - [ ]* 6.2 Write property test for dropdown toggle behavior
    - **Property 2: Dropdown toggle changes collapse state**
    - **Validates: Requirements 1.4**
    - Test toggle for any dropdown group
    - Verify state changes on click
  
  - [ ]* 6.3 Write property test for collapse indicator states
    - **Property 3: Collapse indicators match group state**
    - **Validates: Requirements 2.1, 2.2, 2.3**
    - Test indicator rotation for any group state
    - Verify visual indication matches actual state
  
  - [ ]* 6.4 Write property test for active category highlighting
    - **Property 4: Active category highlighting**
    - **Validates: Requirements 3.1, 3.2**
    - Test for any active category in any group
    - Verify both category and parent group are highlighted
  
  - [ ]* 6.5 Write property test for auto-expand active group
    - **Property 5: Auto-expand active group**
    - **Validates: Requirements 3.3**
    - Test for any active category
    - Verify parent group is expanded on load
  
  - [ ]* 6.6 Write property test for unique active state
    - **Property 6: Unique active state**
    - **Validates: Requirements 3.5**
    - Test that exactly one category has active class
    - Verify across all possible active category states
  
  - [ ]* 6.7 Write property test for state persistence round-trip
    - **Property 7: Dropdown state persistence round-trip**
    - **Validates: Requirements 4.1, 4.2**
    - Generate random combinations of group states
    - Test save → reload → restore produces same states
  
  - [ ]* 6.8 Write property test for independent group state
    - **Property 8: Independent group state**
    - **Validates: Requirements 4.4**
    - Test that toggling one group doesn't affect others
    - Verify for all group pairs
  
  - [ ]* 6.9 Write property test for ARIA attributes
    - **Property 9: ARIA attributes on dropdown groups**
    - **Validates: Requirements 8.2, 8.4**
    - Test that all groups have aria-expanded and aria-controls
    - Verify attributes match actual collapse state
  
  - [ ]* 6.10 Write property test for backward compatibility
    - **Property 10: Backward compatibility**
    - **Validates: Requirements 7.7**
    - Test all SettingCategory constants still exist
    - Verify all routes remain unchanged

- [ ]* 7. Write unit tests for specific scenarios
  - [ ]* 7.1 Test "Basic Settings" group structure
    - Verify group exists in rendered sidebar
    - Check it contains exactly General, Company, Appearance
    - _Requirements: 1.2_
  
  - [ ]* 7.2 Test Bootstrap attribute correctness
    - Verify data-bs-toggle="collapse" on group headers
    - Check data-bs-target matches collapse div IDs
    - Validate aria-controls matches target IDs
    - _Requirements: 7.6_
  
  - [ ]* 7.3 Test mobile sidebar interaction
    - Verify sidebar toggle shows dropdowns in correct state
    - Test category click closes sidebar on mobile
    - _Requirements: 6.2, 6.5_
  
  - [ ]* 7.4 Test localStorage edge cases
    - Test with empty localStorage (default expanded)
    - Test with corrupted JSON (graceful fallback)
    - Test with localStorage blocked (no errors)
    - _Requirements: 4.3, 4.5_
  
  - [ ]* 7.5 Test keyboard accessibility
    - Verify Enter key toggles dropdown
    - Verify Space key toggles dropdown
    - Test tab order is logical
    - _Requirements: 8.1, 8.5_
  
  - [ ]* 7.6 Test existing feature preservation
    - Test search functionality (Ctrl+K) still works
    - Test export/import buttons are functional
    - Test breadcrumb displays correct category
    - Test "Restore Defaults" button works
    - _Requirements: 5.2, 5.3, 5.5, 5.6_
  
  - [ ]* 7.7 Test CSS naming conventions
    - Verify all new classes follow settings-* pattern
    - Check consistency with existing styles
    - _Requirements: 7.4_
  
  - [ ]* 7.8 Test focus indicators
    - Verify visible focus ring on group headers
    - Check focus styles meet WCAG contrast requirements
    - _Requirements: 8.3_

- [ ] 8. Final integration and polish
  - [ ] 8.1 Test across different browsers
    - Verify Chrome, Firefox, Safari, Edge compatibility
    - Test mobile browsers (iOS Safari, Chrome Mobile)
    - _Requirements: 6.1_
  
  - [ ] 8.2 Review and optimize animations
    - Ensure smooth transitions at 60fps
    - Verify prefers-reduced-motion works correctly
    - _Requirements: 2.4_
  
  - [ ] 8.3 Final accessibility audit
    - Run automated accessibility checker
    - Verify ARIA attributes are correct
    - Test with screen reader (NVDA or JAWS)
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_
  
  - [ ] 8.4 Code cleanup and documentation
    - Add JSDoc comments to JavaScript functions
    - Add PHP docblocks to new constants
    - Remove any debug console.log statements
    - Ensure CSS is properly organized
    - _Requirements: 7.2, 7.5_

- [ ] 9. Final checkpoint - Comprehensive testing
  - Ensure all tests pass, ask the user if questions arise
  - Run full test suite (unit + property tests)
  - Test all 19 setting categories for navigation
  - Verify localStorage persistence in various scenarios
  - Confirm mobile responsiveness
  - Validate accessibility compliance
  - Check that all existing features work unchanged

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Bootstrap 5 collapse component provides built-in accessibility
- fast-check library required for property-based tests
- Each property test should run minimum 100 iterations
- Mobile testing can use browser DevTools device simulation
- Consider adding additional dropdown groups in future iterations

