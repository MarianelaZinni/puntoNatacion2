<x-layouts.app :title="__('Dashboard')">

    
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <!-- Card: Precios con profesor -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Precios — Con profesor</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Precio según cantidad de clases por semana</p>
                <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-200">
                    @php
                        $teacherPrices = $subjectPricesForJs['teacher'] ?? [];
                    @endphp
                    @for($i=1;$i<=5;$i++)
                        <li class="flex items-center justify-between">
                            <span> {{ $i }} vez/semana</span>
                            <span class="font-medium">{{ isset($teacherPrices[$i]) ? number_format($teacherPrices[$i], 0, ',', '.') : '—' }}</span>
                        </li>
                    @endfor
                </ul>
            </div>

            <!-- Card: Precios sin profesor -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 p-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Precios — Sin profesor</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Precio según cantidad de clases por semana</p>
                <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-200">
                    @php
                        $noTeacherPrices = $subjectPricesForJs['no_teacher'] ?? [];
                    @endphp
                    @for($i=1;$i<=5;$i++)
                        <li class="flex items-center justify-between">
                            <span> {{ $i }} vez/semana</span>
                            <span class="font-medium">{{ isset($noTeacherPrices[$i]) ? number_format($noTeacherPrices[$i], 0, ',', '.') : '—' }}</span>
                        </li>
                    @endfor
                </ul>
            </div>
        </div>

        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-gray-900 p-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Horario semanal</h2>

            <div id="timetable-wrapper" class="overflow-auto">
                <div id="timetable-grid" class="min-w-full"></div>
            </div>
        </div>
    </div>

    <!-- students modal (used by grid) -->
<div id="students-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 p-4">
    <div class="max-w-2xl w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mx-auto max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700">
            <h3 id="students-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Inscriptos</h3>
            <button id="students-modal-close" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 p-1">✕</button>
        </div>
        <div class="p-4 overflow-y-auto" style="min-height: 6rem;" id="students-modal-body-wrapper">
            <div id="students-modal-body" class="space-y-2 text-sm text-gray-700 dark:text-gray-200"></div>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button id="students-modal-close-2" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded">Cerrar</button>
        </div>
    </div>
</div>

    @push('scripts')
    <script>
    (function () {
        // Data from server
        const subjects = @json($subjectsForJs);
        const subjectColors = @json($subjectColors);
        const subjectPrices = @json($subjectPricesForJs);

        // Config
        const days = ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'];
        const slotMinutes = 50;
        const startHour = { h:7, m:0 };
        const endHour = { h:22, m:0 };

        function generateSlots() {
            const slots = [];
            const base = new Date(2025,0,1, startHour.h, startHour.m, 0);
            const end = new Date(2025,0,1, endHour.h, endHour.m, 0);
            let cur = new Date(base);
            while (cur < end) {
                const hh = String(cur.getHours()).padStart(2,'0');
                const mm = String(cur.getMinutes()).padStart(2,'0');
                slots.push(hh + ':' + mm);
                cur.setMinutes(cur.getMinutes() + slotMinutes);
            }
            return slots;
        }
        const slots = generateSlots();

        // build scheduleMap
        function buildScheduleMap() {
            const map = {};
            for (const d of days) {
                map[d] = {};
                for (const s of slots) map[d][s] = [];
            }
            for (const s of subjects) {
                if (!s || !s.day || !s.start_time) continue;
                if (!map[s.day]) continue;
                map[s.day][s.start_time] = map[s.day][s.start_time] || [];
                map[s.day][s.start_time].push(s);
            }
            return map;
        }

        function hexToRgba(hex, alpha) {
            const h = hex.replace('#','');
            const full = (h.length === 3) ? h.split('').map(c => c + c).join('') : h;
            const bigint = parseInt(full, 16);
            const r = (bigint >> 16) & 255;
            const g = (bigint >> 8) & 255;
            const b = bigint & 255;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"'`=\/]/g, function (s) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x2F;', '`':'&#x60;', '=':'&#x3D;' })[s];
            });
        }

        // render function
        function renderGridInto(container) {
            const scheduleMap = buildScheduleMap();
            let html = '<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded">';
            html += '<div class="min-w-[600px]">';

            // header
            html += '<div class="grid grid-cols-6 gap-0 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">';
            for (let i=0;i<days.length;i++) {
                html += `<div class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 text-center">${days[i]}</div>`;
            }
            html += '</div>';

            // rows
            html += '<div class="flex flex-col">';
            slots.forEach(slot => {
                html += `<div class="grid grid-cols-6 gap-0 border-b border-gray-100 dark:border-gray-800 min-h-[64px]">`;
                days.forEach(day => {
                    const cellSubjects = scheduleMap[day][slot] || [];
                    let cellInner = `<div class="p-2">`;
                    if (cellSubjects.length === 0) {
                        cellInner += `<div class="text-xs text-gray-400 dark:text-gray-600">${slot}</div>`;
                    } else {
                        for (const subj of cellSubjects) {
                            const color = subjectColors[subj.subject_type_id] || '#29b1dc';
                            const enrolled = subj.students ? subj.students.length : 0;
                            const title = subj.subject_type ? (subj.subject_type.description || subj.subject_type.value || 'Materia') : 'Materia';
                            cellInner += `
                                <button
                                    type="button"
                                    data-subject-id="${subj.id}"
                                    class="w-full text-left block mb-1 p-2 rounded shadow-sm cursor-pointer subject-card"
                                    style="background: linear-gradient(90deg, ${hexToRgba(color,0.18)}, ${hexToRgba(color,0.06)}); border-left:4px solid ${color};"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">${escapeHtml(title)}</div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-300 mt-1">${escapeHtml(subj.start_time)} — ${escapeHtml(subj.end_time)}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">${enrolled}/${subj.capacity ?? '—'}</div>
                                </button>
                            `;
                        }
                    }
                    cellInner += `</div>`;
                    html += `<div class="border-r border-gray-100 dark:border-gray-800">${cellInner}</div>`;
                });
                html += `</div>`;
            });
            html += '</div>';
            html += '</div>';
            html += '</div>';

            container.innerHTML = html;

            // attach click handlers — navigate to attendance/take for today
            // Date is provided by the server (app timezone = America/Argentina/Buenos_Aires)
            // to avoid depending on the client browser's timezone.
            const TODAY_DATE = '{{ $todayDate }}';
            container.querySelectorAll('[data-subject-id]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const subjId = btn.getAttribute('data-subject-id');
                    window.location.href = `/attendance/take?subject_id=${encodeURIComponent(subjId)}&date=${encodeURIComponent(TODAY_DATE)}`;
                });
            });
        }

        // Modal logic (uses #students-modal in DOM)
        const modalEl = document.getElementById('students-modal');
        const modalTitle = document.getElementById('students-modal-title');
        const modalBody = document.getElementById('students-modal-body');
        document.getElementById('students-modal-close')?.addEventListener('click', () => { modalEl.classList.add('hidden'); });
        document.getElementById('students-modal-close-2')?.addEventListener('click', () => { modalEl.classList.add('hidden'); });

        // Use the preloaded subject object to populate modal (no network)
        function openStudentsModalFromData(subj) {
            try {
                const title = subj.subject_type ? (subj.subject_type.description || subj.subject_type.value || 'Materia') : 'Materia';
                modalTitle.textContent = `${title}`;
                modalBody.innerHTML = '';
                const studentsList = subj.students || [];
                if (studentsList.length) {
                    studentsList.forEach(st => {
                        const el = document.createElement('div');
                        el.className = 'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700';
                        el.innerHTML = `<div class="text-sm font-medium text-gray-900 dark:text-gray-100">${escapeHtml(st.name)}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">${escapeHtml(st.email || '')}</div>`;
                        modalBody.appendChild(el);
                    });
                } else {
                    modalBody.innerHTML = `<div class="text-sm text-gray-600 dark:text-gray-400">No hay alumnos inscriptos.</div>`;
                }
                modalEl.classList.remove('hidden');
            } catch (err) {
                console.error(err);
                 Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo mostrar la información.' });
            }
        }

        // Fallback: fetch subject details from server (kept for compatibility)
        async function openStudentsModal(subjectId) {
            try {
                const res = await fetch(`/subjects/${subjectId}`);
                if (!res.ok) throw new Error('No se pudo obtener la clase');
                const json = await res.json();
                const subj = json.subject;
                if (!subj) return;
                const title = subj.subject_type ? (subj.subject_type.description || subj.subject_type.value || 'Materia') : 'Materia';
                modalTitle.textContent = `${title}`;
                modalBody.innerHTML = '';
                const students = subj.students || [];
                if (students.length) {
                    students.forEach(st => {
                        const el = document.createElement('div');
                        el.className = 'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700';
                        el.innerHTML = `<div class="text-sm font-medium text-gray-900 dark:text-gray-100">${escapeHtml(st.name)}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">${escapeHtml(st.email || '')}</div>`;
                        modalBody.appendChild(el);
                    });
                } else {
                    modalBody.innerHTML = `<div class="text-sm text-gray-600 dark:text-gray-400">No hay alumnos inscriptos.</div>`;
                }
                modalEl.classList.remove('hidden');
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la información de la clase.' });
            }
        }

        // Idempotent initializer that renders the grid when #timetable-grid is visible
        function initTimetableGrid() {
            const container = document.getElementById('timetable-grid');
            if (!container) return;

            // avoid double render
            if (container.dataset._rendered === '1') return;

            function ensureRender() {
                try {
                    renderGridInto(container);
                    container.dataset._rendered = '1';
                } catch (e) {
                    console.error('Render grid failed, retrying...', e);
                    setTimeout(() => {
                        try { renderGridInto(container); container.dataset._rendered = '1'; } catch (err) { console.error(err); }
                    }, 200);
                }
            }

            // If container is visible and has size, render now
            const rect = container.getBoundingClientRect();
            if (rect.width > 0 && rect.height > 0 && window.getComputedStyle(container).display !== 'none') {
                ensureRender();
                return;
            }

            // Otherwise observe intersection
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            ensureRender();
                            try { io.disconnect(); } catch(e) {}
                        }
                    });
                }, { root: null, threshold: 0.01 });
                io.observe(container);
                return;
            }

            // Fallback: MutationObserver to wait for container being added/shown
            const mo = new MutationObserver((mutations, observer) => {
                const r = container.getBoundingClientRect();
                if (r.width > 0 && r.height > 0) {
                    ensureRender();
                    try { observer.disconnect(); } catch(e) {}
                }
            });
            mo.observe(container, { attributes: true, childList: true, subtree: true });
        }

        // Wire navigation events for client-side nav systems
        ['DOMContentLoaded','load','turbo:load','turbolinks:load','pjax:complete','flux:navigate','flux:content:loaded'].forEach(evt => {
            document.addEventListener(evt, () => {
                setTimeout(initTimetableGrid, 20);
            });
        });

        // Also observe if #timetable-wrapper inserted dynamically
        const bodyMo = new MutationObserver((mutations) => {
            for (const m of mutations) {
                for (const n of m.addedNodes) {
                    if (n instanceof HTMLElement) {
                        if (n.querySelector && n.querySelector('#timetable-grid')) {
                            setTimeout(initTimetableGrid, 20);
                            return;
                        }
                    }
                }
            }
        });
        bodyMo.observe(document.body, { childList: true, subtree: true });

        // Try to init immediately
        setTimeout(initTimetableGrid, 50);
        window.initTimetableGrid = initTimetableGrid;
    })();
    </script>
    @endpush
</x-layouts.app>