@props(['fields' => [], 'action' => '', 'filterOptions' => [], 'groupByOptions' => [], 'targetSelector' => '.kanban-cont'])

<div x-data="odooSearch('{{ $action }}', {{ json_encode($fields) }}, {{ json_encode(request()->all()) }}, {{ json_encode($filterOptions) }}, {{ json_encode($groupByOptions) }}, '{{ $targetSelector }}')"
    class="odoo-search-component position-relative"
    @click.away="showFilters = false; showDropdown = false; if(mobileSearchOpen) toggleMobileSearch()">

    <!-- Mobile Toggle Button (Visible only on mobile) -->
    <div class="d-flex d-md-none justify-content-end" x-show="!mobileSearchOpen">
        <button
            class="btn btn-white btn-sm rounded-circle shadow-sm p-2 d-flex align-items-center justify-content-center border"
            style="width: 38px; height: 38px;" @click="toggleMobileSearch()">
            <i class="fa fa-search"></i>
        </button>
    </div>

    <!-- Search Bar Container -->
    <div class="align-items-center border rounded bg-white py-0 px-1"
        :class="mobileSearchOpen ? 'd-flex position-fixed p-2 shadow' : 'd-none d-md-flex'"
        :style="mobileSearchOpen ? 'left: 50%; transform: translateX(-50%); width: 93%; z-index: 9999; max-width: 600px;' : 'min-height: 40px;'">

        <!-- Mobile Close Button -->
        <button class="btn btn-link text-secondary p-0 me-2 d-md-none" x-show="mobileSearchOpen"
            @click="toggleMobileSearch()">
            <i class="fa fa-arrow-left"></i>
        </button>

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
            <input type="text" x-model="searchQuery" @click.stop @input="showDropdown = true; showFilters = false"
                @focus="showFilters = true; showDropdown = false" @keydown.enter.prevent="submitSearch()"
                @keydown.backspace="handleBackspace()" class="form-control border-0 shadow-none py-0 px-1"
                placeholder="Search..." style="min-width: 100px; font-size: 0.9rem; height: 32px;">

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
                class="dropdown-menu show position-absolute top-100 end-0 mt-2 shadow-sm border rounded-0 p-3 odoo-mega-menu"
                style="z-index: 1050; display: none;" :style="showFilters ? 'display: block;' : 'display: none;'">

                <div class="row g-0">
                    <!-- Filters Column -->
                    <div class="col-12 col-md-4 border-end">
                        <h6 class="dropdown-header text-dark fw-bold px-0 mb-2">
                            <i class="fa fa-filter me-2 text-muted"></i> Filters
                        </h6>
                        <ul class="list-unstyled mb-0 font-small">
                            <!-- Helper method to render filters -->
                            <template x-for="option in filterOptions" :key="option.value">
                                <li>
                                    <a href="#" class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light"
                                       @click.prevent="addFilter(option.key || 'filter', option.label, option.value); showFilters = false">
                                        <span x-text="option.label"></span>
                                    </a>
                                </li>
                            </template>
                            <template x-if="filterOptions.length === 0">
                                <li class="text-muted ps-2 py-1 italic">No filters available</li>
                            </template>
                           
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
                            <template x-for="option in groupByOptions" :key="option.value">
                                <li>
                                    <a href="#" class="text-decoration-none text-dark d-block py-1 ps-2 hover-bg-light"
                                       @click.prevent="addFilter(option.key || 'group_by', option.label, option.value); showFilters = false">
                                        <span x-text="option.label"></span>
                                    </a>
                                </li>
                            </template>
                             <template x-if="groupByOptions.length === 0">
                                <li class="text-muted ps-2 py-1 italic">No grouping options</li>
                            </template>

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
        <button
            class="btn btn-white btn-sm rounded shadow-sm p-1 ms-1 border d-flex align-items-center justify-content-center"
            type="button" @click.stop="showFilters = !showFilters; showDropdown = false"
            style="width: 24px; height: 24px;">
            <i class="fa-solid fa-caret-down" style="font-size: 0.8rem;"></i>
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