@push('scripts')
<script>
(function () {
    const roleSelect       = document.getElementById('role');
    const teacherSection   = document.getElementById('teacher-section');
    const studentSection   = document.getElementById('student-section');
    const emailReqIndicator = document.getElementById('email-required-indicator');
    const userDataSection  = document.getElementById('user-data-section');
    const userInputs       = document.querySelectorAll('[data-user-input]');
    const isCreate         = document.getElementById('user-form')?.action.includes('/users') &&
                             !document.getElementById('user-form')?.querySelector('input[name="_method"][value="PUT"]');

    // ── Buscador de alumnos ──────────────────────────────────────────────────
    const searchInput     = document.getElementById('student-search');
    const resultsBox      = document.getElementById('student-search-results');
    const chipsContainer  = document.getElementById('selected-students-chips');
    const checkboxItems   = document.querySelectorAll('.student-checkbox-item');
    const searchUrl       = '{{ route("users.search-students") }}';

    let searchTimeout = null;

    function getCheckedIds() {
        return Array.from(document.querySelectorAll('[data-student-checkbox]:checked'))
                    .map(cb => parseInt(cb.value));
    }

    function renderChips() {
        if (!chipsContainer) return;
        chipsContainer.innerHTML = '';
        checkboxItems.forEach(item => {
            const cb = item.querySelector('[data-student-checkbox]');
            if (cb && cb.checked) {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-[#29b1dc]/15 text-[#1a8eb5] dark:text-[#29b1dc]';
                chip.innerHTML = `${item.dataset.studentName} <button type="button" data-id="${cb.value}" class="chip-remove ml-0.5 text-[#1a8eb5] hover:text-red-500 font-bold leading-none">×</button>`;
                chipsContainer.appendChild(chip);
            }
        });
        // Chip remove buttons
        chipsContainer.querySelectorAll('.chip-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.id);
                const item = document.querySelector(`.student-checkbox-item[data-student-id="${id}"]`);
                if (item) {
                    item.querySelector('[data-student-checkbox]').checked = false;
                    item.classList.add('hidden');
                }
                renderChips();
                updateSections();
            });
        });
    }

    function addStudent(id, name, dni) {
        // Check if already has a checkbox for this student
        let item = document.querySelector(`.student-checkbox-item[data-student-id="${id}"]`);
        if (!item) {
            // Create a new hidden checkbox dynamically for students not pre-loaded
            const div = document.getElementById('student-checkboxes');
            item = document.createElement('label');
            item.className = 'flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 student-checkbox-item';
            item.dataset.studentId   = id;
            item.dataset.studentName = name;
            item.dataset.studentDni  = dni || '';
            item.innerHTML = `<input type="checkbox" name="student_ids[]" value="${id}"
                class="rounded border-gray-300 text-[#29b1dc]" data-student-checkbox checked>
                ${name}${dni ? ` <span class="text-gray-400 text-xs">(DNI ${dni})</span>` : ''}`;
            div.appendChild(item);
            // Re-attach listener
            item.querySelector('[data-student-checkbox]').addEventListener('change', () => {
                renderChips(); updateSections();
            });
        } else {
            item.querySelector('[data-student-checkbox]').checked = true;
            item.classList.remove('hidden');
        }
        renderChips();
        updateSections();
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const q = searchInput.value.trim();
            if (q.length < 1) { resultsBox.classList.add('hidden'); return; }
            searchTimeout = setTimeout(async () => {
                const res  = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                resultsBox.innerHTML = '';
                if (data.length === 0) {
                    resultsBox.innerHTML = '<p class="p-3 text-sm text-gray-400">Sin resultados.</p>';
                } else {
                    data.forEach(s => {
                        const alreadyChecked = getCheckedIds().includes(s.id);
                        const row = document.createElement('button');
                        row.type = 'button';
                        row.className = `w-full text-left px-3 py-2 text-sm hover:bg-[#29b1dc]/10 transition ${alreadyChecked ? 'opacity-50 cursor-default' : 'cursor-pointer'}`;
                        row.innerHTML = `<span class="font-medium">${s.name}</span>${s.dni ? ` <span class="text-gray-400 text-xs">DNI ${s.dni}</span>` : ''}${alreadyChecked ? ' <span class="text-[#29b1dc] text-xs">✓ ya seleccionado</span>' : ''}`;
                        if (!alreadyChecked) {
                            row.addEventListener('click', () => {
                                addStudent(s.id, s.name, s.dni);
                                searchInput.value = '';
                                resultsBox.classList.add('hidden');
                            });
                        }
                        resultsBox.appendChild(row);
                    });
                }
                resultsBox.classList.remove('hidden');
            }, 250);
        });

        // Cerrar resultados al hacer clic afuera
        document.addEventListener('click', e => {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });
    }

    // Escuchar cambios en checkboxes existentes
    document.querySelectorAll('[data-student-checkbox]').forEach(cb => {
        cb.addEventListener('change', () => { renderChips(); updateSections(); });
    });

    // ── Lógica de secciones (rol) ────────────────────────────────────────────
    function updateSections() {
        const role = roleSelect ? roleSelect.value : '';
        const isStudentLinkedRole = ['alumno', 'super_alumno'].includes(role);
        const hasStudentSelection = getCheckedIds().length > 0;
        const shouldLockDataFields = isCreate && (!role || (isStudentLinkedRole && !hasStudentSelection));

        if (teacherSection)    teacherSection.classList.toggle('hidden', role !== 'profesor');
        if (studentSection)    studentSection.classList.toggle('hidden', !isStudentLinkedRole);
        if (emailReqIndicator) emailReqIndicator.classList.toggle('hidden', isStudentLinkedRole);
        if (userDataSection)   userDataSection.classList.toggle('opacity-60', shouldLockDataFields);

        userInputs.forEach(input => {
            input.disabled = shouldLockDataFields;
            if (input.id === 'email') input.required = !isStudentLinkedRole;
        });
    }

    if (roleSelect) roleSelect.addEventListener('change', updateSections);

    // Init
    renderChips();
    updateSections();
})();
</script>
@endpush