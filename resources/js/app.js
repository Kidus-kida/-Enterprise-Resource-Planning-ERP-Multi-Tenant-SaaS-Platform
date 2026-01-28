import "./bootstrap";
import $ from 'jquery';
window.jQuery = window.$ = $
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import sort from '@alpinejs/sort'
window.Livewire = Livewire
window.Alpine = Alpine
Alpine.plugin(sort)

// Register odooSearch Alpine component BEFORE Livewire starts
Alpine.data('odooSearch', (action, fields, initialParams, filterOptions = [], groupByOptions = [], targetSelector = '.kanban-cont') => ({
    action: action,
    fields: fields,
    filterOptions: filterOptions,
    groupByOptions: groupByOptions,
    targetSelector: targetSelector,
    searchQuery: '',
    showDropdown: false,
    showFilters: false,
    activeFilters: [],
    mobileSearchOpen: false,

    init() {
        // Initialize active filters from server request params
        Object.keys(initialParams).forEach(key => {
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

    toggleMobileSearch() {
        this.mobileSearchOpen = !this.mobileSearchOpen;
        // Target the specific content container (Kanban or Main Content)
        const content = document.querySelector(this.targetSelector) || document.querySelector('.content-body') || document.querySelector('.page-wrapper > .content');
        
        if (this.mobileSearchOpen) {
             // Push content down
             if (content) {
                 content.style.transition = 'margin-top 0.2s ease';
                 content.style.marginTop = '60px';
             }
             
             setTimeout(() => {
                const input = document.querySelector('.odoo-search-component input');
                if(input) input.focus();
             }, 100);
        } else {
             // Reset content position
             if (content) content.style.marginTop = '';
        }
    },

    addFilter(key, label, value) {
        this.activeFilters.push({ key, label, value });
        this.searchQuery = '';
        this.showDropdown = false;
        this.submitSearch();
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
        }
    },

    submitSearch() {
        const params = new URLSearchParams();
        this.activeFilters.forEach(filter => {
            params.append(filter.key, filter.value);
        });
        
        const url = `${this.action}?${params.toString()}`;
        console.log('Fetching URL:', url);
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(html => {
            console.log('Response HTML length:', html.length);
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            console.log('Searching for selector:', this.targetSelector);
            const newBoard = doc.querySelector(this.targetSelector);
            const currentBoard = document.querySelector(this.targetSelector);
            
            console.log('New board found:', !!newBoard);
            console.log('Current board found:', !!currentBoard);
            
            if (newBoard && currentBoard) {
                console.log('Replacing board content...');
                currentBoard.outerHTML = newBoard.outerHTML;
                
                console.log('Board content replaced successfully');
                
                if (typeof Sortable !== 'undefined') {
                    var taskBoxWrapper = [].slice.call(document.querySelectorAll('.kanban-wrap'));
                    console.log('Reinitializing Sortable for', taskBoxWrapper.length, 'elements');
                    for (var i = 0; i < taskBoxWrapper.length; i++) {
                        new Sortable(taskBoxWrapper[i], {
                            group: 'taskboard',
                            handle: ".kanban-box",
                            draggable: ".panel",
                            animation: 150,
                            fallbackOnBody: true,
                            swapThreshold: 0.65,
                            dataIdAttr: 'data-id'
                        });
                    }
                }
            } else {
                console.error('Could not find kanban board elements');
                if (!newBoard) console.error('New board not found in response');
                if (!currentBoard) console.error('Current board not found on page');
            }
            
            window.history.pushState({}, '', url);
        })
        .catch(error => {
            console.error('Search error:', error);
            alert('Search failed. Please try again or refresh the page.');
        });
    }
}));

Livewire.start()

// DataTables
import 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';

import Select2 from 'select2';
import Sortable from "sortablejs";
import moment from 'moment';
import intlTelInput from "intl-tel-input";
import nProgress from "nprogress";
import Toastify from 'toastify-js'
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
// FullCalendar and PDFMake removed from static imports
import { jsPDF } from "jspdf";

// DataTables
import DataTable from 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import JSZip from 'jszip';
// Lazy Load PDFMake
if (document.querySelectorAll('.table, .datatable, table').length > 0) {
    Promise.all([
        import('pdfmake/build/pdfmake'),
        import('pdfmake/build/vfs_fonts')
    ]).then(([pdfMakeModule, pdfFontsModule]) => {
        const pdfMake = pdfMakeModule.default;
        const pdfFonts = pdfFontsModule.default;

        if (pdfFonts && pdfFonts.pdfMake) {
            pdfMake.vfs = pdfFonts.pdfMake.vfs;
        } else if (pdfFonts && pdfFonts.vfs) {
            pdfMake.vfs = pdfFonts.vfs;
        }
        window.pdfMake = pdfMake;

        // Register with DataTables Buttons if available
        if (DataTable.Buttons && typeof DataTable.Buttons.pdfMake === 'function') {
            DataTable.Buttons.pdfMake(pdfMake);
        }
    });
}
if (DataTable.Buttons && typeof DataTable.Buttons.jszip === 'function') {
    DataTable.Buttons.jszip(JSZip);
}

window.intlTelInput = intlTelInput;
window.NProgress = nProgress;
window.moment = moment;
window.Moment = moment;
window.toastr = toastr;
window.Toastify = Toastify;
// Lazy Load FullCalendar
if (document.querySelector('.calendar') || document.getElementById('calendar')) {
    Promise.all([
        import('@fullcalendar/core'),
        import('@fullcalendar/daygrid'),
        import('@fullcalendar/timegrid'),
        import('@fullcalendar/list')
    ]).then(([core, dayGrid, timeGrid, list]) => {
        window.Calendar = core.Calendar;
        window.dayGridPlugin = dayGrid.default;
        window.timeGridPlugin = timeGrid.default;
        window.listPlugin = list.default;
        
        // Dispatch event if needed, or rely on script execution order
        // Scripts that use Calendar should check if it's available or wait
    });
}
window.Sortable = Sortable
window.jsPDF = jsPDF;
Select2();
const AppAssets = import.meta.glob([
    '../assets/fonts/**',
    '../assets/img/**',
    '../assets/css/**',
    '../assets/js/**',
    '../assets/plugins/**/**',
])
$(document).on("click", ".deleteBtn", function () {
    let title = $(this).data("title");
    let url = $(this).data("route");
    let question = $(this).data("question");
    var id = $(this).data("id");
    if (id != "" && url != "") {
        $("#GeneralDeleteModal .input[name='id']").val(id);
        $("#GeneralDeleteModal form").attr("action", url);
        $("#GeneralDeleteModal .modal_title").html(title);
        $("#GeneralDeleteModal .modal_message").html(question);
        $("#GeneralDeleteModal").modal("show");
    }
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).on('click', 'a[data-ajax-modal="true"], button[data-ajax-modal="true"], div[data-ajax-modal="true"], span[data-ajax-modal="true"]', function () {
    let title = $(this).data("title");
    let style = $(this).data("style");
    let size = $(this).data("size");
    let url = $(this).data('url');
    $.ajax({
        url: url,
        beforeSend: function () {
            $("#loader-wrapper").addClass("d-block");
        },
        success: function (data) {
            if (!$("#generalModalPopup").length) {
                $("body").append(
                    $(
                        `<div class="modal custom-modal ${style ? style : "fade"
                        }" id="generalModalPopup" role="dialog">
                            <div class="modal-dialog modal-dialog-centered ${size ? "modal-" + size : ""
                        }" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        ${title
                            ? '<h5 class="modal-title">' +
                            title +
                            "</h5>"
                            : ""
                        }
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="body"></div>
                                </div>
                            </div>
                        </div>`
                    )
                );
            }
            $("#generalModalPopup .body").html(data);
            $("#generalModalPopup").modal("show");
            $("#loader-wrapper").removeClass("d-block");
            if ($(".select").length > 0) {
                $(".select").each(function () {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>');
                    $this.select2({
                        dropdownAutoWidth: true,
                        width: '100%',
                        dropdownParent: $this.parent()
                    });
                });
            }
            if ($(".datepicker").length > 0) {
                $(".datepicker").each(function () {
                    $(this).datetimepicker({
                        format: "YYYY-MM-DD",
                        icons: {
                            up: "fa fa-angle-up",
                            down: "fa fa-angle-down",
                            next: "fa fa-angle-right",
                            previous: "fa fa-angle-left",
                        },
                    });
                });
            }
            if ($(".datetimepicker").length > 0) {
                $(".datetimepicker").each(function () {
                    $(this).datetimepicker({
                        format: "YYYY-MM-DD H:i",
                        icons: {
                            up: "fa fa-angle-up",
                            down: "fa fa-angle-down",
                            next: "fa fa-angle-right",
                            previous: "fa fa-angle-left",
                        },
                    });
                });
            }
        },
        error: function (xhr) {
            $(".loader-wrapper").addClass('d-none');
            console.log(xhr);
            alert("something went wrong")
        }
    });
});

if ($(".datetimepicker").length > 0 && $.fn.datetimepicker) {
    $(".datetimepicker").each(function () {
        $(this).datetimepicker({
            format: "YYYY-MM-DD H:i",
            icons: {
                up: "fa fa-angle-up",
                down: "fa fa-angle-down",
                next: "fa fa-angle-right",
                previous: "fa fa-angle-left",
            },
        });
    });
}
