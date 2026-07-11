# Design Document

## Overview

This design implements collapsible dropdown groups in the superadmin settings sidebar to organize 19 setting categories into a hierarchical structure. The implementation uses Bootstrap 5's collapse component combined with custom JavaScript for state persistence and active state highlighting.

The current flat list will be restructured into dropdown groups with "Basic Settings" as the first group containing General Settings, Company, and Appearance categories. The design maintains all existing functionality (navigation, search, export/import) while improving organization and usability.

## Architecture

### Component Structure

The solution consists of four main components:

1. **Blade Template Restructuring** (layout.blade.php)
   - Replace flat navigation array with hierarchical structure
   - Implement Bootstrap collapse markup for dropdown groups
   - Add collapse indicator icons with rotation states
   - Maintain existing mobile sidebar toggle functionality

2. **Category Metadata Extension** (SettingCategory.php)
   - Add optional grouping metadata structure
   - Define dropdown group configurations
   - Maintain backward compatibility with existing constants

3. **JavaScript State Management** (settings.js)
   - Implement localStorage-based state persistence
   - Handle dropdown expand/collapse interactions
   - Auto-expand groups containing active categories
   - Synchronize active state highlighting

4. **CSS Styling** (settings.css)
   - Add dropdown group header styles
   - Style collapse indicators with smooth transitions
   - Implement nested navigation item indentation
   - Maintain visual consistency with existing theme

### Data Flow

```
User clicks dropdown group
     ↓
JavaScript toggles Bootstrap collapse
     ↓
Update collapse indicator rotation
     ↓
Save state to localStorage
     ↓
Update DOM classes for active/highlight state
```

Page load sequence:
```
1. Blade template renders dropdown structure
2. JavaScript initializes on DOMContentLoaded
3. Check localStorage for saved dropdown states
4. Restore expanded/collapsed states
5. Check current active category
6. Auto-expand parent group if category is active
7. Apply active state highlighting
```

## Components and Interfaces

### 1. Dropdown Group Data Structure

Add to SettingCategory.php:

```php
const GROUPS = [
    'basic' => [
        'label' => 'Basic Settings',
        'icon' => 'fa-solid fa-gear',
        'categories' => [
            self::GENERAL,
            self::COMPANY,
            self::APPEARANCE,
        ]
    ],
    // Future groups can be added here
];
```

### 2. Blade Template Structure

Modified navigation markup pattern:

```blade
@foreach($dropdownGroups as $groupKey => $group)
    <div class="settings-nav-group">
        <a class="settings-nav-group-header" 
           data-bs-toggle="collapse" 
           href="#group-{{ $groupKey }}" 
           role="button"
           aria-expanded="true"
           aria-controls="group-{{ $groupKey }}">
            <i class="{{ $group['icon'] }}"></i>
            <span>{{ $group['label'] }}</span>
            <i class="fa-solid fa-chevron-down collapse-indicator ms-auto"></i>
        </a>
        
        <div class="collapse show" id="group-{{ $groupKey }}">
            @foreach($group['categories'] as $cat)
                <a href="{{ route('superadmin.settings.show', $cat) }}"
                   class="settings-nav-item nested {{ $category === $cat ? 'active' : '' }}">
                    <i class="{{ SettingCategory::ICONS[$cat] }}"></i>
                    <span>{{ SettingCategory::LABELS[$cat] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endforeach

{{-- Ungrouped categories --}}
@foreach($standaloneCategories as $cat)
    <a href="{{ route('superadmin.settings.show', $cat) }}"
       class="settings-nav-item {{ $category === $cat ? 'active' : '' }}">
        <i class="{{ SettingCategory::ICONS[$cat] }}"></i>
        <span>{{ SettingCategory::LABELS[$cat] }}</span>
    </a>
@endforeach
```

### 3. JavaScript Module Interface

```javascript
const SettingsDropdownManager = {
    STORAGE_KEY: 'settingsDropdownStates',
    
    init() {
        // Initialize dropdown behavior
    },
    
    restoreStates() {
        // Restore from localStorage
    },
    
    saveState(groupKey, isExpanded) {
        // Persist to localStorage
    },
    
    autoExpandActiveGroup() {
        // Expand group containing active category
    },
    
    highlightActiveGroup(groupKey) {
        // Add visual emphasis to parent group
    }
};
```

## Data Models

### Dropdown State Storage

**LocalStorage Schema:**
```javascript
{
  "settingsDropdownStates": {
    "basic": true,      // expanded
    "advanced": false,  // collapsed
    "system": true      // expanded
  }
}
```

**Default State:**
- All groups expanded on first visit
- State persists across sessions
- Per-group independent state (not global expand/collapse)

### Category Grouping Model

```php
[
    'group_key' => [
        'label' => string,           // Display name
        'icon' => string,            // Font Awesome class
        'categories' => array,       // Array of category constants
        'collapsed_default' => bool  // Optional: default collapsed state
    ]
]
```

### Active State Data

The active category is passed from the controller as `$category` variable:
- Used to highlight the active navigation item
- Used to determine which group should be auto-expanded
- Used to highlight the parent group header

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system - essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: All categories remain accessible

*For any* of the 19 setting categories, the rendered sidebar SHALL contain a navigation link with the correct route to that category's settings page.

**Validates: Requirements 1.3, 5.1**

### Property 2: Dropdown toggle changes collapse state

*For any* dropdown group, clicking the group header SHALL toggle the collapse state from expanded to collapsed or from collapsed to expanded.

**Validates: Requirements 1.4**

### Property 3: Collapse indicators match group state

*For any* dropdown group, the collapse indicator icon SHALL visually indicate the current collapse state (pointing down when expanded, pointing right/rotated when collapsed).

**Validates: Requirements 2.1, 2.2, 2.3**

### Property 4: Active category highlighting

*For any* active setting category within a dropdown group, both the category item AND its parent dropdown group SHALL receive visual highlighting.

**Validates: Requirements 3.1, 3.2**

### Property 5: Auto-expand active group

*For any* setting category that is marked as active, its parent dropdown group (if it has one) SHALL be automatically expanded when the page loads.

**Validates: Requirements 3.3**

### Property 6: Unique active state

*For any* rendered settings sidebar, exactly one setting category SHALL have the active state at any given time.

**Validates: Requirements 3.5**

### Property 7: Dropdown state persistence round-trip

*For any* combination of dropdown group states (expanded/collapsed), saving those states to localStorage then reloading the page SHALL restore the same expanded/collapsed states for all groups.

**Validates: Requirements 4.1, 4.2**

### Property 8: Independent group state

*For any* two different dropdown groups, toggling the collapse state of one group SHALL NOT affect the collapse state of the other group.

**Validates: Requirements 4.4**

### Property 9: ARIA attributes on dropdown groups

*For any* dropdown group, the group header SHALL have aria-expanded and aria-controls attributes that accurately reflect the current collapse state.

**Validates: Requirements 8.2, 8.4**

### Property 10: Backward compatibility

*For any* existing SettingCategory constant or route, the implementation SHALL preserve that constant and route without modification.

**Validates: Requirements 7.7**

## Error Handling

### LocalStorage Unavailability

**Scenario:** Browser blocks localStorage access or throws SecurityError

**Handling:**
```javascript
try {
    const states = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    // Use states
} catch (e) {
    console.warn('Settings dropdown state persistence unavailable:', e);
    // Continue with default expanded states
    return {};
}
```

**Graceful Degradation:**
- Dropdowns function normally without persistence
- All groups default to expanded state
- User can still expand/collapse manually during session
- No error messages shown to user

### Missing or Corrupted State Data

**Scenario:** localStorage contains invalid JSON or malformed data

**Handling:**
```javascript
let savedStates = {};
try {
    savedStates = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    if (typeof savedStates !== 'object') savedStates = {};
} catch (e) {
    savedStates = {};
}
```

**Recovery:**
- Reset to empty state object
- All groups default to expanded
- Next user interaction saves clean state

### Bootstrap Collapse Not Loaded

**Scenario:** Bootstrap JS fails to load or is incompatible

**Detection:**
```javascript
if (typeof bootstrap === 'undefined' || !bootstrap.Collapse) {
    console.error('Bootstrap collapse component not available');
    // Fallback: basic show/hide without animations
}
```

**Fallback:**
- Implement basic toggle with display: none/block
- Skip animation transitions
- Core functionality preserved

### Active Category Not Found

**Scenario:** URL contains category that doesn't exist in navigation

**Handling:**
- No active state highlighting applied
- All groups default to expanded state
- Navigation renders normally
- No JavaScript errors thrown

### Group Contains No Categories

**Scenario:** Configuration error results in empty group

**Handling:**
```php
@if(!empty($group['categories']))
    {{-- Render group --}}
@endif
```

- Don't render empty groups
- Log warning in development mode
- Other groups render normally

## Testing Strategy

### Dual Testing Approach

This feature requires both **unit tests** and **property-based tests** for comprehensive coverage.

**Unit Tests** focus on:
- Specific UI interactions (clicking dropdown headers)
- DOM structure verification (correct Bootstrap attributes)
- Integration points (mobile sidebar toggle interaction)
- Edge cases (empty localStorage, missing Bootstrap)
- Error conditions (localStorage exceptions)

**Property Tests** focus on:
- Universal behaviors across all dropdown groups
- State persistence round-trips with random state combinations
- Active state highlighting for all categories
- Comprehensive input coverage through randomization

Together, unit tests catch concrete implementation bugs while property tests verify general correctness across all possible inputs.

### Property-Based Testing Configuration

**Framework:** fast-check (JavaScript/TypeScript property-based testing library)

**Test Configuration:**
- Minimum 100 iterations per property test
- Each test tagged with design document property reference
- Tag format: `Feature: settings-sidebar-dropdown, Property {N}: {description}`

**Property Test Examples:**

**Property 1: All categories remain accessible**
```javascript
// Feature: settings-sidebar-dropdown, Property 1: All categories remain accessible
fc.assert(
  fc.property(
    fc.constantFrom(...ALL_CATEGORIES),
    (category) => {
      const sidebar = renderSidebar({ activeCategory: category });
      const link = sidebar.querySelector(`[href*="${category}"]`);
      return link !== null && link.href.includes(category);
    }
  ),
  { numRuns: 100 }
);
```

**Property 7: Dropdown state persistence round-trip**
```javascript
// Feature: settings-sidebar-dropdown, Property 7: Dropdown state persistence round-trip
fc.assert(
  fc.property(
    fc.record({
      basic: fc.boolean(),
      advanced: fc.boolean(),
      system: fc.boolean(),
    }),
    (states) => {
      // Save states
      localStorage.setItem(STORAGE_KEY, JSON.stringify(states));
      
      // Reload and restore
      const restored = restoreDropdownStates();
      
      // Verify round-trip
      return JSON.stringify(states) === JSON.stringify(restored);
    }
  ),
  { numRuns: 100 }
);
```

### Unit Testing Coverage

**DOM Structure Tests:**
- Verify Bootstrap collapse markup is correctly generated
- Check data-bs-toggle and data-bs-target attributes
- Verify ARIA attributes (aria-expanded, aria-controls)
- Check CSS classes follow naming convention

**Interaction Tests:**
- Click dropdown header toggles collapse state
- Keyboard (Enter/Space) triggers collapse toggle
- Mobile sidebar toggle shows dropdowns in correct state
- Category selection closes mobile sidebar

**State Management Tests:**
- localStorage save on toggle
- State restoration on page load
- Default to expanded when localStorage is empty
- Graceful degradation when localStorage unavailable

**Integration Tests:**
- Search functionality still works with new structure
- Export/Import buttons remain functional
- Breadcrumb displays correct active category
- "Restore Defaults" button works for each category

**Edge Case Tests:**
- Empty localStorage
- Corrupted JSON in localStorage
- localStorage blocked by browser
- Bootstrap not loaded
- Empty group configurations
- Missing category constants

### Test Environment

**Testing Framework:** Jest (for JavaScript) + Laravel Dusk (for browser testing)

**Required Setup:**
- Mock localStorage for Node.js tests
- Bootstrap 5 test environment
- Sample category configuration data
- Mock route helpers for Blade rendering tests

### Acceptance Criteria Mapping

Unit tests will verify the following specific examples identified in prework:

- **Requirement 1.2:** Test that "Basic Settings" group exists with correct children
- **Requirement 2.5:** Test that group headers have distinct CSS classes
- **Requirement 3.4:** Test that active styling uses existing CSS classes
- **Requirement 5.2-5.8:** Test each preserved feature individually
- **Requirement 6.1-6.5:** Mobile responsive behavior tests
- **Requirement 7.4:** CSS naming convention tests
- **Requirement 7.6:** Bootstrap attribute tests
- **Requirement 8.1-8.5:** Accessibility tests

## Implementation Notes

### Bootstrap Collapse Integration

The implementation uses Bootstrap 5's native collapse component with data attributes:

```html
<a data-bs-toggle="collapse" 
   href="#groupId"
   aria-expanded="true"
   aria-controls="groupId">
</a>
<div class="collapse show" id="groupId">
  <!-- categories -->
</div>
```

**Benefits:**
- Built-in ARIA support
- Smooth CSS transitions
- Event system for state tracking
- Mobile-friendly touch interactions
- No custom animation code needed

### State Persistence Strategy

**localStorage Key:** `settingsDropdownStates`

**Data Structure:** JSON object mapping group keys to boolean states

**Timing:**
- Save: On Bootstrap collapse `hidden.bs.collapse` and `shown.bs.collapse` events
- Restore: On page load before Bootstrap initialization
- Default: All groups expanded (`true`)

### CSS Animation Approach

Collapse indicator rotation uses CSS transitions:

```css
.collapse-indicator {
    transition: transform 0.2s ease;
}

.collapsed .collapse-indicator {
    transform: rotate(-90deg);
}
```

**Performance:**
- GPU-accelerated transform
- Minimal repainting
- Smooth 60fps animation

### Mobile Considerations

- Touch target size: minimum 44x44px for group headers
- Sidebar overlay: existing mobile toggle continues to work
- Auto-close on category select: preserved behavior
- Viewport meta tag: already configured in layout

### Accessibility Implementation

**Keyboard Navigation:**
- Group headers focusable (Bootstrap default)
- Enter/Space trigger collapse (Bootstrap default)
- Logical tab order (DOM order)

**Screen Reader Support:**
- aria-expanded attribute updated by Bootstrap
- aria-controls links header to content
- Visual focus indicators via CSS :focus

**Reduced Motion:**
```css
@media (prefers-reduced-motion: reduce) {
    .collapse-indicator {
        transition: none;
    }
}
```

