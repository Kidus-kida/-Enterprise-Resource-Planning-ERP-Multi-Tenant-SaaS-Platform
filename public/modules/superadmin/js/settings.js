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
    // Live update brand theme swatches
    const colorPickers = document.querySelectorAll('.color-input-picker');
    colorPickers.forEach(picker => {
        const textInput = picker.parentElement.querySelector('.color-input-text');
        
        picker.addEventListener('input', (e) => {
            if (textInput) textInput.value = e.target.value;
            // Update preview swatch color in upper indicator bar
            const key = picker.getAttribute('data-key');
            if (key === 'appearance.primary_color') {
                const badge = document.getElementById('prevPrimary');
                if (badge) badge.style.background = e.target.value;
            }
            if (key === 'appearance.sidebar_color') {
                const badge = document.getElementById('prevSidebar');
                if (badge) badge.style.background = e.target.value;
            }
        });

        if (textInput) {
            textInput.addEventListener('input', (e) => {
                let val = e.target.value;
                if (/^#[0-9a-fA-F]{6}$/.test(val)) {
                    picker.value = val;
                }
            });
        }
    });

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
            }
        });
    });

    // ----------------------------------------------------
    // 5. AJAX Form Saving (Unified)
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

            const section = settingsForm.getAttribute('data-section');
            const actionUrl = window.location.href; // Routes post to current path
            const formData = new FormData(settingsForm);

            fetch(actionUrl, {
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
                showToast(data.message || 'Settings saved successfully.', true);
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
                    data.results.forEach(res => {
                        const item = document.createElement('a');
                        item.className = 'search-result-item';
                        item.href = res.url;
                        
                        // Icons mapping matching category
                        const catIcons = {
                            general: 'fa-gear',
                            company: 'fa-building',
                            appearance: 'fa-palette',
                            authentication: 'fa-shield-halved',
                            localization: 'fa-globe',
                            email: 'fa-envelope',
                            notification: 'fa-bell',
                            storage: 'fa-hard-drive',
                            backup: 'fa-database',
                            security: 'fa-lock',
                            modules: 'fa-cubes',
                            integration: 'fa-plug',
                            maintenance: 'fa-wrench',
                            whitelabel: 'fa-terminal',
                            license: 'fa-key'
                        };
                        const iconClass = catIcons[res.category] || 'fa-sliders';

                        item.innerHTML = `
                            <div class="search-res-icon"><i class="fa-solid ${iconClass}"></i></div>
                            <div class="search-res-details">
                                <span class="search-res-label">${res.label}</span>
                                <span class="search-res-path">${res.key} &bull; ${res.category_label || res.category}</span>
                            </div>
                            <i class="fa-solid fa-chevron-right ms-auto text-muted" style="font-size:10px;"></i>
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
