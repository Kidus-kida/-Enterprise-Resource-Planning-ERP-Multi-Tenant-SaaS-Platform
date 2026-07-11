/**
 * System Settings premium javascript engine.
 * Handles AJAX saves, media uploads, interactive UI dependencies, search shortcuts, and Artisan commands.
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Sidebar toggles (Mobile)
    // ----------------------------------------------------
    const toggleBtn = document.getElementById('toggleSettingsSidebar');
    const sidebar = document.getElementById('settingsSidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
        // Click outside closes sidebar
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    }

    // ----------------------------------------------------
    // 2. Global Toast Helper
    // ----------------------------------------------------
    const toast = document.getElementById('settingsToast');
    const showToast = (message, isSuccess = true) => {
        if (!toast) return;
        const msgEl = toast.querySelector('.toast-message');
        const iconEl = toast.querySelector('.toast-icon');
        if (msgEl) msgEl.textContent = message;
        if (iconEl) {
            if (isSuccess) {
                iconEl.className = 'fa-solid fa-circle-check toast-icon text-success';
            } else {
                iconEl.className = 'fa-solid fa-circle-xmark toast-icon text-danger';
            }
        }
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3500);
    };

    // ----------------------------------------------------
    // 3. Dynamic Field Dependencies
    // ----------------------------------------------------
    const resolveFieldDependencies = () => {
        const dependentElements = document.querySelectorAll('[data-depends]');
        dependentElements.forEach(el => {
            const dependency = el.getAttribute('data-depends'); // e.g. "email.mail_driver:smtp" or "appearance.theme:custom"
            if (!dependency) return;

            const [depKey, depValue] = dependency.split(':');
            const formFieldName = depKey.replace(/\./g, '_');
            
            // Find input element for dependency
            const inputElements = document.querySelectorAll(`[name="${formFieldName}"], [name="${formFieldName}[]"]`);
            
            const evaluateDependency = () => {
                let currentVal = '';
                inputElements.forEach(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        if (input.checked) currentVal = input.value;
                    } else {
                        currentVal = input.value;
                    }
                });
                
                // Toggle display
                if (currentVal === depValue || (depValue === '1' && currentVal === 'true') || (depValue === '0' && currentVal === 'false')) {
                    el.style.display = '';
                } else {
                    el.style.display = 'none';
                }
            };

            // Bind listeners
            inputElements.forEach(input => {
                input.addEventListener('change', evaluateDependency);
                input.addEventListener('input', evaluateDependency);
            });

            // Run initial check
            evaluateDependency();
        });
    };
    resolveFieldDependencies();

    // ----------------------------------------------------
    // 4. Live Color and Theme Previews
    // ----------------------------------------------------
    // Live color update — also push directly to CSS variables for instant preview
    const colorPickers = document.querySelectorAll('.color-input-picker');
    const cssVarMap = {
        'appearance.primary_color':   '--primary-color',
        'appearance.secondary_color': '--secondary-color',
        'appearance.accent_color':    '--accent-color',
        'appearance.success_color':   '--success-color',
        'appearance.danger_color':    '--danger-color',
        'appearance.warning_color':   '--warning-color',
        'appearance.info_color':      '--info-color',
        'appearance.sidebar_color':   '--sidebar-bg',
        'appearance.sidebar_text_color': '--sidebar-text',
        'appearance.navbar_color':    '--navbar-bg',
        'appearance.header_color':    '--header-bg',
    };
    colorPickers.forEach(picker => {
        const textInput = picker.parentElement.querySelector('.color-input-text');
        const key = picker.getAttribute('data-key');
        picker.addEventListener('input', (e) => {
            if (textInput) textInput.value = e.target.value;
            // Swatch preview
            if (key === 'appearance.primary_color') {
                const badge = document.getElementById('prevPrimary');
                if (badge) badge.style.background = e.target.value;
            }
            if (key === 'appearance.sidebar_color') {
                const badge = document.getElementById('prevSidebar');
                if (badge) badge.style.background = e.target.value;
            }
            // ── Live CSS Variable injection ──────────────────────────────
            if (cssVarMap[key]) {
                document.documentElement.style.setProperty(cssVarMap[key], e.target.value);
            }
        });
        if (textInput) {
            textInput.addEventListener('input', (e) => {
                let val = e.target.value;
                if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                    picker.value = val;
                    if (cssVarMap[key]) {
                        document.documentElement.style.setProperty(cssVarMap[key], val);
                    }
                }
            });
        }
    });

    // ── Live Customizer: non-color appearance inputs ─────────────────────────
    const appearanceLiveMap = {
        'appearance_font_family':   '--font-family',
        'appearance_font_size':     '--font-size',
        'appearance_border_radius': '--border-radius',
    };
    Object.entries(appearanceLiveMap).forEach(([name, cssVar]) => {
        const el = document.querySelector(`[name="${name}"]`);
        if (!el) return;
        const doPush = () => {
            let val = el.value;
            if (name === 'appearance_border_radius') val += 'px';
            document.documentElement.style.setProperty(cssVar, val);
        };
        el.addEventListener('input', doPush);
        el.addEventListener('change', doPush);
    });

    // Shadow toggle live update
    const shadowToggle = document.querySelector('[name="appearance_shadows"]');
    if (shadowToggle) {
        shadowToggle.addEventListener('change', () => {
            const shadow = shadowToggle.checked ? '0 0.15rem 1.75rem 0 rgba(58,59,69,.15)' : 'none';
            document.documentElement.style.setProperty('--card-shadow', shadow);
        });
    }
    // Animation speed toggle
    const animToggle = document.querySelector('[name="appearance_animations"]');
    if (animToggle) {
        animToggle.addEventListener('change', () => {
            document.documentElement.style.setProperty('--animation-speed', animToggle.checked ? '0.2s' : '0s');
        });
    }

    // Theme selector click preview handling
    const themeCards = document.querySelectorAll('.theme-card');
    themeCards.forEach(card => {
        const radio = card.querySelector('.theme-radio');
        card.addEventListener('click', () => {
            themeCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            if (radio) {
                radio.checked = true;
                // Dispatch change event to trigger dependencies
                radio.dispatchEvent(new Event('change', { bubbles: true }));

                // Instant preview of theme presets
                const val = radio.value;
                const presets = {
                    'default':   { primary: '#ff9b44', sidebar: '#2c3e50' },
                    'blue':      { primary: '#0d6efd', sidebar: '#1a2035' },
                    'dark':      { primary: '#6c757d', sidebar: '#121212' },
                    'corporate': { primary: '#0f4c81', sidebar: '#1a3352' },
                    'green':     { primary: '#198754', sidebar: '#1a3d2b' }
                };
                if (presets[val]) {
                    document.documentElement.style.setProperty('--primary-color', presets[val].primary);
                    document.documentElement.style.setProperty('--sidebar-bg', presets[val].sidebar);
                    
                    const prevPrimary = document.getElementById('prevPrimary');
                    if (prevPrimary) prevPrimary.style.background = presets[val].primary;
                    const prevSidebar = document.getElementById('prevSidebar');
                    if (prevSidebar) prevSidebar.style.background = presets[val].sidebar;
                } else if (val === 'custom') {
                    // Revert to whatever is in the custom color pickers
                    const primaryPicker = document.querySelector('.color-input-picker[data-key="appearance.primary_color"]');
                    const sidebarPicker = document.querySelector('.color-input-picker[data-key="appearance.sidebar_color"]');
                    if (primaryPicker) {
                        document.documentElement.style.setProperty('--primary-color', primaryPicker.value);
                        const prevPrimary = document.getElementById('prevPrimary');
                        if (prevPrimary) prevPrimary.style.background = primaryPicker.value;
                    }
                    if (sidebarPicker) {
                        document.documentElement.style.setProperty('--sidebar-bg', sidebarPicker.value);
                        const prevSidebar = document.getElementById('prevSidebar');
                        if (prevSidebar) prevSidebar.style.background = sidebarPicker.value;
                    }
                }
            }
        });
    });

    // ----------------------------------------------------
    // 5. CSS Variable Flush (zero-delay theme application)
    // ----------------------------------------------------
    const cssVarFlushMap = {
        'appearance_primary_color':      '--primary-color',
        'appearance_secondary_color':    '--secondary-color',
        'appearance_accent_color':       '--accent-color',
        'appearance_success_color':      '--success-color',
        'appearance_danger_color':       '--danger-color',
        'appearance_warning_color':      '--warning-color',
        'appearance_info_color':         '--info-color',
        'appearance_sidebar_color':      '--sidebar-bg',
        'appearance_sidebar_text_color': '--sidebar-text',
        'appearance_navbar_color':       '--navbar-bg',
        'appearance_header_color':       '--header-bg',
    };

    function patchDOMColors(primaryColor, sidebarColor) {
        // Patch sidebar background directly
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebarColor) sidebar.style.background = sidebarColor;

        // Patch two-col-bar sidebar
        const twoColSidebar = document.querySelector('.sidebar-twocol .sidebar-left');
        if (twoColSidebar && sidebarColor) twoColSidebar.style.background = sidebarColor;

        // Patch Bootstrap primary buttons
        if (primaryColor) {
            document.querySelectorAll('.btn-primary').forEach(btn => {
                btn.style.backgroundColor = primaryColor;
                btn.style.borderColor = primaryColor;
            });

            // Patch active nav links
            document.querySelectorAll('.sidebar-menu .active > a').forEach(el => {
                el.style.color = primaryColor;
            });

            // Patch progress bars and badges
            document.querySelectorAll('.bg-primary').forEach(el => {
                el.style.backgroundColor = primaryColor + ' !important';
            });
        }
    }

    function flushCSSVarsFromForm(form) {
        if (!form) return;

        let primaryColor = null;
        let sidebarColor = null;

        // Flush colour pickers
        Object.entries(cssVarFlushMap).forEach(([name, cssVar]) => {
            const el = form.querySelector(`[name="${name}"]`);
            if (el && el.value) {
                document.documentElement.style.setProperty(cssVar, el.value);
                if (name === 'appearance_primary_color') primaryColor = el.value;
                if (name === 'appearance_sidebar_color') sidebarColor = el.value;
            }
        });

        // Flush border-radius
        const br = form.querySelector('[name="appearance_border_radius"]');
        if (br) document.documentElement.style.setProperty('--border-radius', br.value + 'px');

        // Flush font-family
        const ff = form.querySelector('[name="appearance_font_family"]');
        if (ff) document.documentElement.style.setProperty('--font-family', ff.value);

        // Flush font-size
        const fs = form.querySelector('[name="appearance_font_size"]');
        if (fs) document.documentElement.style.setProperty('--font-size', fs.value);

        // Flush shadows
        const sh = form.querySelector('[name="appearance_shadows"]');
        if (sh) document.documentElement.style.setProperty('--card-shadow', sh.checked ? '0 0.15rem 1.75rem 0 rgba(58,59,69,.15)' : 'none');

        // Flush animations
        const anim = form.querySelector('[name="appearance_animations"]');
        if (anim) document.documentElement.style.setProperty('--animation-speed', anim.checked ? '0.2s' : '0s');

        // If a preset theme (not custom) is active, also push preset colors
        const themeRadio = form.querySelector('.theme-radio:checked');
        if (themeRadio && themeRadio.value !== 'custom') {
            const presets = {
                'default':   { primary: '#ff9b44', sidebar: '#2c3e50' },
                'blue':      { primary: '#0d6efd', sidebar: '#1a2035' },
                'dark':      { primary: '#6c757d', sidebar: '#121212' },
                'corporate': { primary: '#0f4c81', sidebar: '#1a3352' },
                'green':     { primary: '#198754', sidebar: '#1a3d2b' },
            };
            const preset = presets[themeRadio.value];
            if (preset) {
                primaryColor = preset.primary;
                sidebarColor = preset.sidebar;
                document.documentElement.style.setProperty('--primary-color', preset.primary);
                document.documentElement.style.setProperty('--sidebar-bg', preset.sidebar);
            }
        }

        // Direct DOM patch for elements that use compiled SCSS (not CSS vars)
        patchDOMColors(primaryColor, sidebarColor);
    }

    // ----------------------------------------------------
    // 6. AJAX Form Saving (Unified)
    // ----------------------------------------------------
    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = settingsForm.querySelector('.btn-save-settings');
            const btnTxt = btn ? btn.querySelector('.btn-text') : null;
            const btnLoading = btn ? btn.querySelector('.btn-loading') : null;

            if (btn) btn.disabled = true;
            if (btnTxt) btnTxt.classList.add('d-none');
            if (btnLoading) btnLoading.classList.remove('d-none');

            const actionUrl = window.location.href;
            const formData = new FormData(settingsForm);

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw err; });
                return res.json();
            })
            .then(data => {
                showToast(data.message || 'Settings saved successfully.', true);
                // ── Zero-delay: instantly push all CSS vars from saved form ──
                flushCSSVarsFromForm(settingsForm);
            })
            .catch(err => {
                console.error(err);
                let msg = 'An error occurred while saving.';
                if (err.errors) {
                    msg = Object.values(err.errors).flatMap(x => x).join(' ');
                } else if (err.message) {
                    msg = err.message;
                }
                showToast(msg, false);
            })
            .finally(() => {
                if (btn) btn.disabled = false;
                if (btnTxt) btnTxt.classList.remove('d-none');
                if (btnLoading) btnLoading.classList.add('d-none');
            });
        });
    }

    // ----------------------------------------------------
    // 6. AJAX Media Upload Handlers for Logo, Favicon, banner etc.
    // ----------------------------------------------------
    const fileInputs = document.querySelectorAll('.image-upload-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;

            const key = input.getAttribute('data-key');
            const wrapper = input.closest('.image-upload-wrapper');
            const uploadBtn = wrapper.querySelector('.btn-upload-trigger');
            const originalBtnHtml = uploadBtn ? uploadBtn.innerHTML : '';
            
            if (uploadBtn) {
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Uploading...';
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('key', key);
            formData.append('_token', CSRF_TOKEN);

            fetch('/superadmin/settings/media/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                showToast(data.message || 'Media uploaded.', true);
                // Update view preview element
                const imgEl = wrapper.querySelector('.image-upload-preview');
                const noImgEl = wrapper.querySelector('.image-upload-no-preview');
                
                if (imgEl) {
                    imgEl.src = data.url;
                    imgEl.style.display = '';
                }
                if (noImgEl) {
                    noImgEl.style.display = 'none';
                }
            })
            .catch(err => {
                console.error(err);
                showToast(err.message || 'Failed to upload image.', false);
            })
            .finally(() => {
                if (uploadBtn) {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalBtnHtml;
                }
                input.value = ''; // Reset input selection
            });
        });
    });

    // ----------------------------------------------------
    // 7. Active Email SMTP Connection Testing
    // ----------------------------------------------------
    const sendTestEmailBtn = document.getElementById('sendTestEmailBtn');
    if (sendTestEmailBtn) {
        sendTestEmailBtn.addEventListener('click', () => {
            const emailInput = document.getElementById('testEmailAddress');
            const resultBox = document.getElementById('testEmailResult');
            const email = emailInput ? emailInput.value.trim() : '';

            if (!email) {
                showToast('Please enter a valid recipient email address first.', false);
                return;
            }

            sendTestEmailBtn.disabled = true;
            const originalText = sendTestEmailBtn.innerHTML;
            sendTestEmailBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending...';

            if (resultBox) {
                resultBox.style.display = 'none';
                resultBox.className = 'test-email-result mt-2';
            }

            fetch('/superadmin/settings/email/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                if (resultBox) {
                    resultBox.style.display = 'block';
                    if (data.success) {
                        resultBox.className = 'test-email-result alert alert-success mt-2';
                        resultBox.innerHTML = `<i class="fa-solid fa-circle-check me-2"></i> ${data.message}`;
                    } else {
                        resultBox.className = 'test-email-result alert alert-danger mt-2';
                        resultBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-2"></i> <strong>Failure details:</strong><br>${data.message}`;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (resultBox) {
                    resultBox.style.display = 'block';
                    resultBox.className = 'test-email-result alert alert-danger mt-2';
                    resultBox.textContent = 'Connection error: failed to communicate with test server.';
                }
            })
            .finally(() => {
                sendTestEmailBtn.disabled = false;
                sendTestEmailBtn.innerHTML = originalText;
            });
        });
    }

    // ----------------------------------------------------
    // 8. Artisan Command AJAX console runner
    // ----------------------------------------------------
    const artisanConsole = document.getElementById('artisanConsole');
    if (artisanConsole) {
        artisanConsole.addEventListener('click', (e) => {
            const btn = e.target.closest('.artisan-btn');
            if (!btn) return;

            const command = btn.getAttribute('data-command');
            const outputPanel = document.getElementById('artisanOutputPanel');
            const outputWrap = outputPanel ? outputPanel.parentElement : null;

            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Running...';

            if (outputWrap) outputWrap.style.display = 'none';

            fetch('/superadmin/settings/maintenance/command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ command: command })
            })
            .then(res => res.json())
            .then(data => {
                if (outputWrap && outputPanel) {
                    outputWrap.style.display = 'block';
                    outputPanel.textContent = data.output || 'Command completed with no output.';
                }
                showToast(data.message || 'Command executed successfully.', data.success);
            })
            .catch(err => {
                console.error(err);
                showToast('Failed to run Artisan command.', false);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }

    // ----------------------------------------------------
    // 9. Premium Settings live Search Overlay
    // ----------------------------------------------------
    const overlay = document.getElementById('settingsSearchOverlay');
    const trigger = document.getElementById('openSettingsSearch');
    const input = document.getElementById('settingsSearchInput');
    const results = document.getElementById('settingsSearchResults');

    const openSearch = () => {
        if (!overlay) return;
        overlay.style.display = 'flex';
        if (input) {
            input.focus();
            input.value = '';
        }
        if (results) {
            results.innerHTML = '<div class="search-hint">Type at least 2 characters to search...</div>';
        }
    };

    const closeSearch = () => {
        if (overlay) overlay.style.display = 'none';
    };

    if (trigger) {
        trigger.addEventListener('click', openSearch);
    }

    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeSearch();
        });
    }

    // Keyboard shortcuts: Ctrl+K / Cmd+K and Esc
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openSearch();
        }
        if (e.key === 'Escape') {
            closeSearch();
        }
    });

    // Handle AJAX filtering inputs
    if (input) {
        let debounceTimer;
        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = input.value.trim();

            if (query.length < 2) {
                if (results) {
                    results.innerHTML = '<div class="search-hint">Type at least 2 characters to search...</div>';
                }
                return;
            }

            if (results) {
                results.innerHTML = '<div class="search-hint"><i class="fa-solid fa-spinner fa-spin me-1"></i> Searching index...</div>';
            }

            debounceTimer = setTimeout(() => {
                fetch(`${SETTINGS_SEARCH_URL}?query=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!results) return;
                    if (!data.results || data.results.length === 0) {
                        results.innerHTML = '<div class="search-hint">No settings found matching your query.</div>';
                        return;
                    }

                    results.innerHTML = '';
                    data.results.forEach(r => {
                        const item = document.createElement('a');
                        item.className = 'search-result-item';
                        item.href = r.url;

                        const typeColors = { setting: '#6366f1', user: '#0ea5e9', role: '#10b981', module: '#f59e0b', menu: '#f97316', command: '#6c757d' };
                        const bgColor = typeColors[r.type] || '#adb5bd';

                        item.innerHTML = `
                            <div class="search-res-icon"><i class="fa-solid ${r.type_icon || 'fa-sliders'}"></i></div>
                            <div class="search-res-details">
                                <span class="search-res-label">${r.label}</span>
                                <span class="search-res-path">${r.description || r.key}</span>
                            </div>
                            <span style="font-size:.65rem;padding:2px 8px;border-radius:20px;background:${bgColor};color:#fff;margin-left:.5rem;flex-shrink:0">${r.type_label || r.type}</span>
                            <i class="fa-solid fa-chevron-right ms-2 text-muted" style="font-size:10px;"></i>
                        `;
                        results.appendChild(item);
                    });
                })
                .catch(err => {
                    console.error(err);
                    if (results) results.innerHTML = '<div class="search-hint text-danger">Failed to retrieve search results.</div>';
                });
            }, 300);
        });
    }
});
