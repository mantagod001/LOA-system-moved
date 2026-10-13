document.addEventListener('DOMContentLoaded', () => {
    // ── Mobile nav toggle ────────────────────────────────────────────────────
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navPanel  = document.querySelector('[data-nav-panel]');
    if (navToggle && navPanel) {
        navToggle.addEventListener('click', () => navPanel.classList.toggle('hidden'));
    }

    // ── Role → department field visibility (register form) ───────────────────
    const roleSelect      = document.querySelector('[data-role-select]');
    const departmentWrap  = document.querySelector('[data-department-wrap]');
    if (roleSelect && departmentWrap) {
        const syncDepartment = () => {
            const needs = roleSelect.value === 'department_head';
            departmentWrap.classList.toggle('hidden', !needs);
            const sel = departmentWrap.querySelector('select');
            if (sel) {
                sel.required = needs;
                if (!needs) sel.value = '';
            }
        };
        roleSelect.addEventListener('change', syncDepartment);
        syncDepartment();
    }

    // ── Password show/hide ───────────────────────────────────────────────────
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrap  = button.closest('[data-password-wrap]');
            const input = wrap?.querySelector('[data-password-input]');
            if (!input) return;
            const showing  = input.type === 'text';
            input.type     = showing ? 'password' : 'text';
            button.textContent = showing ? 'Show' : 'Hide';
        });
    });

    // ── Department → Program → Year level cascading selects ─────────────────
    const departmentFilter = document.querySelector('[data-department-filter]');
    const programSelect    = document.querySelector('[data-program-select]');
    const yearSelect       = document.querySelector('[data-year-select]');
    const programsJson     = document.querySelector('[data-programs-json]');
    const yearSaved        = document.getElementById('year_level_saved');

    if (departmentFilter && programSelect && programsJson) {
        const programs        = JSON.parse(programsJson.textContent || '[]');
        const selectedProgram = programSelect.dataset.selectedProgram || '';
        const savedYear       = yearSaved ? yearSaved.value : '';

        const fillYears = (programCode) => {
            if (!yearSelect) return;
            yearSelect.innerHTML = '';
            if (!programCode) {
                yearSelect.append(new Option('Select program first', ''));
                return;
            }
            const years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
            yearSelect.append(new Option('Select year level', ''));
            years.forEach((label, i) => {
                const val = `${programCode}-${label.replace(' Year', '')}`;
                const opt = new Option(`${programCode} — ${label}`, val);
                yearSelect.append(opt);
            });
            if (savedYear && [...yearSelect.options].some(o => o.value === savedYear)) {
                yearSelect.value = savedYear;
            }
        };

        const fillPrograms = () => {
            const departmentId = departmentFilter.value;
            const matches = programs.filter(
                (p) => String(p.department_id) === String(departmentId)
            );
            programSelect.innerHTML = '';

            if (!departmentId) {
                programSelect.append(new Option('Select department first', ''));
                fillYears('');
                return;
            }
            if (matches.length === 0) {
                programSelect.append(new Option('No programs found', ''));
                fillYears('');
                return;
            }
            if (matches.length > 1) {
                programSelect.append(new Option('Select program', ''));
            }
            matches.forEach((p) => programSelect.append(new Option(p.name, p.id)));

            if (matches.length === 1) {
                programSelect.value = String(matches[0].id);
            } else if (
                selectedProgram &&
                matches.some((p) => String(p.id) === String(selectedProgram))
            ) {
                programSelect.value = String(selectedProgram);
            }

            // Fill years based on current program selection
            const chosen = matches.find(p => String(p.id) === String(programSelect.value));
            fillYears(chosen ? chosen.code : '');
        };

        programSelect.addEventListener('change', () => {
            const chosen = programs.find(p => String(p.id) === String(programSelect.value));
            fillYears(chosen ? chosen.code : '');
        });

        departmentFilter.addEventListener('change', fillPrograms);
        fillPrograms();
    }

    // ── Student ID input — format as YYYY-NNNNN ──────────────────────────────
    const studentIdInput = document.querySelector('[data-student-id-input]');
    if (studentIdInput) {
        studentIdInput.addEventListener('input', () => {
            let val = studentIdInput.value.replace(/[^\d]/g, ''); // digits only
            if (val.length > 4) {
                val = val.slice(0, 4) + '-' + val.slice(4, 10);
            }
            studentIdInput.value = val;
        });

        studentIdInput.addEventListener('paste', (e) => {
            e.preventDefault();
            let pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^\d]/g, '');
            if (pasted.length > 4) {
                pasted = pasted.slice(0, 4) + '-' + pasted.slice(4, 10);
            }
            studentIdInput.value = pasted;
        });
    }

    // ── +63 phone input ──────────────────────────────────────────────────────
    // Accepts: 09171234567 | 9171234567 | +639171234567
    // Displays: 917-123-4567  (formatted, 10 digits after +63)
    // Stored:   +639171234567 (normalised server-side)
    const phoneInput = document.querySelector('[data-phone-input]');
    if (phoneInput) {
        // Pull out exactly 10 raw digits regardless of how the user typed them
        const extractDigits = (val) => {
            let d = val.replace(/\D/g, '');
            if (d.startsWith('0'))              d = d.slice(1);   // 09xx → 9xx
            if (d.startsWith('63') && d.length > 10) d = d.slice(2); // +639xx → 9xx
            return d.slice(0, 10);
        };

        // Format 10 digits as 9xx-xxx-xxxx
        const fmt = (d) => {
            if (d.length <= 3) return d;
            if (d.length <= 6) return d.slice(0, 3) + '-' + d.slice(3);
            return d.slice(0, 3) + '-' + d.slice(3, 6) + '-' + d.slice(6);
        };

        phoneInput.addEventListener('input', () => {
            const digits   = extractDigits(phoneInput.value);
            const formatted = fmt(digits);
            phoneInput.value = formatted;
        });

        phoneInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            phoneInput.value = fmt(extractDigits(pasted));
        });
    }

    // ── Multi-file attachments accumulator ───────────────────────────────────
    const attachmentsInput = document.querySelector('[data-attachments-input]');
    const attachmentsContainer = document.querySelector('[data-attachments-container]');
    const attachmentsList = document.querySelector('[data-attachments-list]');
    const attachmentsCount = document.querySelector('[data-attachments-count]');
    const attachmentsError = document.querySelector('[data-attachments-error]');

    if (attachmentsInput && attachmentsContainer && attachmentsList) {
        let dt = new DataTransfer();

        const formatSize = (bytes) => {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        };

        const showError = (msg) => {
            if (!attachmentsError) return;
            attachmentsError.textContent = msg;
            attachmentsError.classList.remove('hidden');
        };

        const clearError = () => {
            if (!attachmentsError) return;
            attachmentsError.textContent = '';
            attachmentsError.classList.add('hidden');
        };

        const renderList = () => {
            attachmentsList.innerHTML = '';
            const files = Array.from(dt.files);

            if (files.length === 0) {
                attachmentsContainer.classList.add('hidden');
                return;
            }

            attachmentsContainer.classList.remove('hidden');
            if (attachmentsCount) {
                attachmentsCount.textContent = `Selected files (${files.length} of 10)`;
            }

            files.forEach((file, index) => {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between rounded-lg border border-maroon-900/15 bg-white px-3 py-2 text-sm shadow-xs';

                const left = document.createElement('div');
                left.className = 'flex items-center gap-2 min-w-0 pr-2';

                // Paperclip icon SVG
                left.innerHTML = `
                    <svg class="h-4 w-4 shrink-0 text-maroon-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                `;

                const nameSpan = document.createElement('span');
                nameSpan.className = 'truncate font-medium text-maroon-950';
                nameSpan.textContent = file.name;

                const sizeSpan = document.createElement('span');
                sizeSpan.className = 'shrink-0 text-xs text-maroon-800/60';
                sizeSpan.textContent = `(${formatSize(file.size)})`;

                left.appendChild(nameSpan);
                left.appendChild(sizeSpan);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'shrink-0 rounded p-1 text-maroon-600 hover:bg-maroon-900/10 hover:text-maroon-900 focus:outline-none';
                removeBtn.setAttribute('aria-label', `Remove ${file.name}`);
                removeBtn.title = 'Remove file';
                removeBtn.innerHTML = `
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;

                removeBtn.addEventListener('click', () => {
                    removeFile(index);
                });

                row.appendChild(left);
                row.appendChild(removeBtn);
                attachmentsList.appendChild(row);
            });
        };

        const removeFile = (indexToRemove) => {
            const nextDt = new DataTransfer();
            Array.from(dt.files).forEach((file, idx) => {
                if (idx !== indexToRemove) {
                    nextDt.items.add(file);
                }
            });
            dt = nextDt;
            attachmentsInput.files = dt.files;
            clearError();
            renderList();
        };

        attachmentsInput.addEventListener('change', () => {
            clearError();
            const picked = Array.from(attachmentsInput.files);

            if (picked.length === 0) {
                // Cancelled dialog or empty; retain existing accumulated files
                attachmentsInput.files = dt.files;
                return;
            }

            for (const file of picked) {
                if (dt.items.length >= 10) {
                    showError('You can upload up to 10 files maximum.');
                    break;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showError(`"${file.name}" exceeds the 5 MB limit.`);
                    continue;
                }
                const alreadyAdded = Array.from(dt.files).some(
                    existing => existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified
                );
                if (!alreadyAdded) {
                    dt.items.add(file);
                }
            }

            attachmentsInput.files = dt.files;
            renderList();
        });
    }

    const leaveStart = document.querySelector('[data-leave-start]');
    const leaveReturn = document.querySelector('[data-leave-return]');
    const leaveHint = document.querySelector('[data-leave-limit-hint]');

    if (leaveStart && leaveReturn) {
        const toLocalIso = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };
        const parseLocal = (value) => new Date(`${value}T00:00:00`);
        const formatHint = (date) => date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });

        const syncLeaveLimit = () => {
            const extensionFrom = leaveReturn.getAttribute('data-leave-extension-from');
            if (extensionFrom) {
                const currentEnd = parseLocal(extensionFrom);
                const min = parseLocal(extensionFrom);
                min.setDate(min.getDate() + 1);
                const max = parseLocal(extensionFrom);
                max.setFullYear(max.getFullYear() + 1);
                const minValue = toLocalIso(min);
                const maxValue = toLocalIso(max);
                leaveReturn.min = minValue;
                leaveReturn.max = maxValue;
                if (leaveHint) {
                    leaveHint.textContent = `Current leave ends ${formatHint(currentEnd)}. New return date: ${formatHint(min)} through ${formatHint(max)}.`;
                }
                if (leaveReturn.value && (leaveReturn.value < minValue || leaveReturn.value > maxValue)) {
                    leaveReturn.value = '';
                }
                return;
            }

            if (!leaveStart.value) {
                leaveReturn.removeAttribute('max');
                if (leaveHint) leaveHint.textContent = 'Select a start date to see the latest allowed return date.';
                return;
            }
            const max = parseLocal(leaveStart.value);
            max.setFullYear(max.getFullYear() + 1);
            const maxValue = toLocalIso(max);
            leaveReturn.min = leaveStart.value;
            leaveReturn.max = maxValue;
            if (leaveHint) {
                leaveHint.textContent = `Latest allowed return date: ${formatHint(max)}.`;
            }
            if (leaveReturn.value && leaveReturn.value > maxValue) {
                leaveReturn.value = maxValue;
            }
            if (leaveReturn.value && leaveReturn.value <= leaveStart.value) {
                leaveReturn.value = '';
            }
        };
        leaveStart.addEventListener('change', syncLeaveLimit);
        leaveReturn.addEventListener('change', syncLeaveLimit);
        syncLeaveLimit();
    }

    const attachmentRows = document.querySelector('[data-attachment-rows]');
    const addAttachmentRow = document.querySelector('[data-add-attachment-row]');

    if (attachmentRows && addAttachmentRow) {
        const syncRemoveButtons = () => {
            const rows = attachmentRows.querySelectorAll('[data-attachment-row]');
            rows.forEach((row) => {
                const btn = row.querySelector('[data-remove-attachment-row]');
                if (btn) btn.classList.toggle('hidden', rows.length < 2);
            });
        };

        addAttachmentRow.addEventListener('click', () => {
            const rows = attachmentRows.querySelectorAll('[data-attachment-row]');
            if (rows.length >= 10) return;
            const clone = rows[0].cloneNode(true);
            clone.querySelectorAll('select').forEach((el) => { el.selectedIndex = 0; });
            clone.querySelectorAll('input[type="file"]').forEach((el) => { el.value = ''; });
            attachmentRows.appendChild(clone);
            syncRemoveButtons();
        });

        attachmentRows.addEventListener('click', (event) => {
            const btn = event.target.closest('[data-remove-attachment-row]');
            if (!btn) return;
            const rows = attachmentRows.querySelectorAll('[data-attachment-row]');
            if (rows.length < 2) return;
            btn.closest('[data-attachment-row]')?.remove();
            syncRemoveButtons();
        });

        syncRemoveButtons();
    }

    const grammarBox = document.querySelector('[data-grammar-check]');
    if (grammarBox) {
        const source = grammarBox.querySelector('[data-grammar-source]');
        const runBtn = grammarBox.querySelector('[data-grammar-run]');
        const status = grammarBox.querySelector('[data-grammar-status]');
        const preview = grammarBox.querySelector('[data-grammar-preview]');
        const suggested = grammarBox.querySelector('[data-grammar-suggested]');
        const acceptBtn = grammarBox.querySelector('[data-grammar-accept]');
        const keepBtn = grammarBox.querySelector('[data-grammar-keep]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const url = grammarBox.getAttribute('data-grammar-url');

        const setStatus = (text, isError = false) => {
            if (!status) return;
            status.textContent = text || '';
            status.classList.toggle('hidden', !text);
            status.classList.toggle('text-red-700', isError);
            status.classList.toggle('text-maroon-700', !isError);
        };

        runBtn?.addEventListener('click', async () => {
            const text = (source?.value || '').trim();
            if (!text) {
                setStatus('Write a reason first, then check grammar.', true);
                return;
            }
            runBtn.disabled = true;
            setStatus('Checking grammar…');
            preview?.classList.add('hidden');
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ text: source.value }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    setStatus(data.message || 'Grammar check is not available right now.', true);
                    return;
                }
                if (!data.changed) {
                    setStatus('No grammar changes suggested.');
                    return;
                }
                if (suggested) suggested.textContent = data.suggested;
                preview?.classList.remove('hidden');
                setStatus(`${data.fixes || 1} suggestion${(data.fixes || 1) === 1 ? '' : 's'}. Review below.`);
            } catch (e) {
                setStatus('Grammar check is not available right now. You can still submit.', true);
            } finally {
                runBtn.disabled = false;
            }
        });

        acceptBtn?.addEventListener('click', () => {
            if (source && suggested) source.value = suggested.textContent || source.value;
            preview?.classList.add('hidden');
            setStatus('Suggested wording applied. You can still edit it.');
        });

        keepBtn?.addEventListener('click', () => {
            preview?.classList.add('hidden');
            setStatus('Kept your original wording.');
        });
    }
});
