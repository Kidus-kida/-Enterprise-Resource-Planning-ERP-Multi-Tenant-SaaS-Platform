@props(['fields' => [], 'action' => ''])

<div x-data="odooSearch('{{ $action }}', {{ json_encode($fields) }}, {{ json_encode(request()->all()) }})"
    class="odoo-search-component position-relative w-100"
    @click.away="showFilters = false; showDropdown = false">

    <!-- Search Bar Container -->
    <div class="d-flex flex-wrap align-items-center border rounded bg-white py-0 px-1" style="min-height: 38px;">

        <!-- Active Filter Chips -->
        <template x-for="(filter, index) in activeFilters" :key="index">
            <div class="badge bg-light text-dark border me-1 d-flex align-items-center my-1">
                <span class="fw-normal text-muted me-1" x-text="filter.label + ':'"></span>
                <span class="fw-bold" x-text="filter.value"></span>
                <button type="button" class="btn-close ms-2" style="width: 0.5em; height: 0.5em;"
                    @click="removeFilter(index)"></button>
            </div>
        </template>

        <!-- Search Icon (Left) -->
        <button class="btn btn-sm btn-link text-secondary p-0 me-1" type="button" style="line-height: 1;">
            <i class="fa fa-search"></i>
        </button>

        <!-- Input Field -->
        <div class="flex-grow-1 position-relative d-flex align-items-center" style="min-width: 150px;">
            <input type="text" x-model="searchQuery" 
                   @click.stop
                   @input="showDropdown = true; showFilters = false" 
                   @focus="showFilters = true; showDropdown = false"
                   @keydown.enter.prevent="submitSearch()" 
                   @keydown.backspace="handleBackspace()"
                   class="form-control border-0 shadow-none py-0 px-1" 
                   placeholder="Search..." 
                   style="min-width: 100px; font-size: 0.9rem; height: 32px;">

            <!-- Search Suggestions Dropdown -->
            <div x-show="showDropdown && searchQuery.length > 0"
                class="dropdown-menu show w-100 position-absolute top-100 start-0 mt-1 shadow-sm border-0"
                style="z-index: 1050;">
                <!-- ... suggestions content ... -->
                <a href="#" class="dropdown-item" @click.prevent="addFilter('search', 'Search', searchQuery)">
                    Search for: <strong x-text="searchQuery"></strong>
                </a>
                <div class="dropdown-divider"></div>
                <template x-for="field in fields" :key="field.key">
                    <a href="#" class="dropdown-item" @click.prevent="selectField(field)">
                        Search <span x-text="field.label"></span> for: <strong x-text="searchQuery"></strong>
                    </a>
                </template>
            </div>

            <!-- Odoo Mega Menu (Filters / Group By / Favorites) -->
            <div x-show="showFilters" @click.stop
                class="dropdown-menu show position-absolute top-100 end-0 mt-1 shadow-sm border rounded-0 p-3 odoo-mega-menu"
                style="z-index: 1050; display: none;" :style="showFilters ? 'display: block;' : 'display: none;'">

                <div class="row g-0">
                    <!-- Filters Column -->
                    <div class="col-12 col-md-4 border-end">
                        <h6 class="dropdown-header text-dark fw-bold px-0 mb-2">
                            <i class="fa fa-filter me-2 text-muted"></i> Filters
                        </h6>
                        <ul class="list-unstyled mb-0 font-small">
                            <li><a href="#" class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light"
                                    @click.prevent="addFilter('preset', 'My Tasks', 'my_tasks'); showFilters = false">My
                                    Tasks</a></li>
                            <li><a href="#" class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light"
                                    @click.prevent="addFilter('preset', 'Unassigned', 'unassigned'); showFilters = false">Unassigned</a>
                            </li>
                            <li class="dropdown-divider my-2"></li>
                            <li>
                                <a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light d-flex justify-content-between align-items-center">
                                    Creation Date <i class="fa fa-caret-right text-muted"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light d-flex justify-content-between align-items-center">
                                    Deadline <i class="fa fa-caret-right text-muted"></i>
                                </a>
                            </li>
                            <li class="dropdown-divider my-2"></li>
                            <li><a href="#" class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light"
                                    @click.prevent="addFilter('preset', 'Open', 'open'); showFilters = false">Open</a>
                            </li>
                            <li><a href="#" class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light"
                                    @click.prevent="addFilter('preset', 'Closed', 'closed'); showFilters = false">Closed</a>
                            </li>
                            <li class="dropdown-divider my-2"></li>
                            <li><a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light">Custom
                                    Filter...</a></li>
                        </ul>
                    </div>

                    <!-- Group By Column -->
                    <div class="col-12 col-md-4 border-end">
                        <h6 class="dropdown-header text-dark fw-bold px-0 mb-2 mt-3 mt-md-0">
                            <i class="fa fa-layer-group me-2 text-muted"></i> Group By
                        </h6>
                        <ul class="list-unstyled mb-0 font-small">
                            <li><a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light">Assignees</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light">Stage</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light">Project</a>
                            </li>
                            <li><a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light">Priority</a>
                            </li>
                            <li class="dropdown-divider my-2"></li>
                            <li>
                                <a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light d-flex justify-content-between align-items-center">
                                    Creation Date <i class="fa fa-caret-right text-muted"></i>
                                </a>
                            </li>
                            <li class="dropdown-divider my-2"></li>
                            <li><a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light">Custom
                                    Group...</a></li>
                        </ul>
                    </div>

                    <!-- Favorites Column -->
                    <div class="col-12 col-md-4">
                        <h6 class="dropdown-header text-dark fw-bold px-0 mb-2 mt-3 mt-md-0">
                            <i class="fa fa-star me-2 text-muted"></i> Favorites
                        </h6>
                        <ul class="list-unstyled mb-0 font-small">
                            <li>
                                <a href="#"
                                    class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light d-flex justify-content-between align-items-center">
                                    Save current search <i class="fa fa-caret-right text-muted"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dropdown Icon (Right) -->
        <button class="btn btn-sm btn-link text-dark text-decoration-none p-0 ms-1" type="button"
            @click.stop="showFilters = !showFilters; showDropdown = false" style="line-height: 1;">
            <i class="fa-solid fa-caret-down"></i>
        </button>
    </div>

    <style>
        .font-small {
            font-size: 0.9rem;
        }

        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }

        .odoo-search-component .dropdown-header {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive Mega Menu Width */
        .odoo-mega-menu {
            min-width: 700px;
        }

        @media (max-width: 992px) {
            .odoo-mega-menu {
                min-width: 300px !important;
                width: 100% !important;
                left: 0 !important;
                right: auto !important;
                position: absolute !important;
                max-height: 80vh;
                overflow-y: auto;
            }
        }
    </style>

    <!-- Hidden Form for submission -->
    <form x-ref="searchForm" :action="action" method="GET" class="d-none">
        <template x-for="filter in activeFilters" :key="filter.key + filter.value">
            <input type="hidden" :name="filter.key" :value="filter.value">
        </template>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('odooSearch', (action, fields, initialParams) => ({
            action: action,
            fields: fields,
            searchQuery: '',
            showDropdown: false,
            showFilters: false,
            activeFilters: [],

            init() {
                // Initialize active filters from server request params
                Object.keys(initialParams).forEach(key => {
                    // Skip internal params or unknown ones if strict
                    const field = this.fields.find(f => f.key === key);
                    if (field) {
                        this.activeFilters.push({
                            key: key,
                            label: field.label,
                            value: initialParams[key]
                        });
                    } else if (key === 'search') {
                        this.activeFilters.push({
                            key: 'search',
                            label: 'Search',
                            value: initialParams[key]
                        });
                    }
                });
            },

            addFilter(key, label, value) {
                this.activeFilters.push({ key, label, value });
                this.searchQuery = '';
                this.showDropdown = false;
                this.submitSearch(); // Auto-submit on select? Or wait for user? Odoo auto-submits.
            },

            selectField(field) {
                this.addFilter(field.key, field.label, this.searchQuery);
            },

            removeFilter(index) {
                this.activeFilters.splice(index, 1);
                this.submitSearch();
            },

            handleBackspace() {
                if (this.searchQuery === '' && this.activeFilters.length > 0) {
                    this.activeFilters.pop();
                    // this.submitSearch(); // Maybe don't auto-submit on backspace removal immediately to avoid jarring reloads
                }
            },

            submitSearch() {
                // We can either update the URL via JS or submit the form. 
                // Submitting form is easier for standard Laravel controllers.
                // We need to wait for Vue/Alpine to render the hidden inputs.
                this.$nextTick(() => {
                    this.$refs.searchForm.submit();
                });
            }
        }));
    });
</script>