import "./bootstrap";
import $ from 'jquery';
window.jQuery = window.$ = $
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import sort from '@alpinejs/sort'
window.Livewire = Livewire
window.Alpine = Alpine
Alpine.plugin(sort)

// Register odooSearch Alpine component
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
                
                const newBoard = doc.querySelector('.kanban-cont');
                const currentBoard = document.querySelector('.kanban-cont');
                
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
});

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
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import { jsPDF } from "jspdf";

// DataTables
import DataTable from 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import JSZip from 'jszip';
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

// Assign to global
window.JSZip = JSZip;
if (pdfFonts && pdfFonts.pdfMake) {
    pdfMake.vfs = pdfFonts.pdfMake.vfs;
} else if (pdfFonts && pdfFonts.vfs) {
    pdfMake.vfs = pdfFonts.vfs;
}
window.pdfMake = pdfMake;
if (DataTable.Buttons) {
    if (typeof DataTable.Buttons.jszip === 'function') DataTable.Buttons.jszip(JSZip);
    if (typeof DataTable.Buttons.pdfMake === 'function') DataTable.Buttons.pdfMake(pdfMake);
}

window.intlTelInput = intlTelInput;
window.NProgress = nProgress;
window.moment = moment;
window.Moment = moment;
window.toastr = toastr;
window.Toastify = Toastify;
window.Calendar = Calendar
window.dayGridPlugin = dayGridPlugin
window.timeGridPlugin = timeGridPlugin
window.listPlugin = listPlugin
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
