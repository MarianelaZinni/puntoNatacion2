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
                <!-- grid renderizado por JS -->
                <div id="timetable-grid" class="min-w-full"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        // Datos inyectados desde el servidor (ya serializados en el controlador)
        const subjects = @json($subjectsForJs);
        const subjectColors = @json($subjectColors);
        const subjectPrices = @json($subjectPricesForJs);

        // Configuración de la grilla
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

        // Construye un map(day -> startTime -> [subjects])
        const scheduleMap = {};
        for (const d of days) {
            scheduleMap[d] = {};
            for (const s of slots) scheduleMap[d][s] = [];
        }
        for (const s of subjects) {
            if (!s || !s.day || !s.start_time) continue;
            if (!scheduleMap[s.day]) continue;
            if (scheduleMap[s.day][s.start_time] !== undefined) {
                scheduleMap[s.day][s.start_time].push(s);
            } else {
                scheduleMap[s.day][s.start_time] = scheduleMap[s.day][s.start_time] || [];
                scheduleMap[s.day][s.start_time].push(s);
            }
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

        function renderGrid() {
            const wrapper = document.getElementById('timetable-grid');
            // construye tabla simple con header días y primera columna horarios
            let html = '<div class="overflow-auto border border-gray-200 dark:border-gray-700 rounded">';

            // Header
            html += '<div class="grid grid-cols-6 gap-0 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">';
            for (let i=0;i<days.length;i++) {
                html += `<div class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 text-center">${days[i]}</div>`;
            }
            html += '</div>';

            // Rows for each slot
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
                            const free = Math.max(0, subj.capacity ? subj.capacity - enrolled : 0);
                            const title = subj.subject_type ? (subj.subject_type.description || subj.subject_type.value || 'Materia') : 'Materia';
                            cellInner += `
                                <button
                                    type="button"
                                    data-subject-id="${subj.id}"
                                    data-subject-title="${escapeHtml(title)}"
                                    class="w-full text-left block mb-1 p-2 rounded shadow-sm cursor-pointer"
                                    style="background: linear-gradient(90deg, ${hexToRgba(color,0.18)}, ${hexToRgba(color,0.06)}); border-left:4px solid ${color};"
                                    onclick="openStudentsModal(${subj.id})"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">${escapeHtml(title)}</div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-300 mt-1">
                                        ${escapeHtml(subj.start_time)} — ${escapeHtml(subj.end_time)}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                        ${enrolled}/${subj.capacity ?? '—'}
                                    </div>
                                </button>
                            `;
                        }
                    }
                    cellInner += `</div>`;
                    html += `<div class="border-r border-gray-100 dark:border-gray-800">${cellInner}</div>`;
                });
                html += `</div>`;
            });
            html += '</div>'; // end flex col

            html += '</div>'; // end wrapper

            wrapper.innerHTML = html;
        }

        // Modal logic (reuse students-modal from previous implementations)
        const modal = document.getElementById('students-modal');
        const modalTitle = modal ? document.getElementById('students-modal-title') : null;
        const modalBody = modal ? document.getElementById('students-modal-body') : null;

        // fetch students for subject when opening modal (to ensure fresh data)
        async function openStudentsModal(subjectId) {
            try {
                const res = await fetch(`/subjects/${subjectId}`);
                if (!res.ok) throw new Error('No se pudo obtener la clase');
                const json = await res.json();
                const subj = json.subject;
                if (!subj) return;
                const title = subj.subject_type ? (subj.subject_type.description || subj.subject_type.value || 'Materia') : 'Materia';
                if (modalTitle) modalTitle.textContent = `${title} — ${subj.end_time} / ${subj.day}`;
                if (modalBody) {
                    if (subj.students && subj.students.length) {
                        modalBody.innerHTML = '';
                        subj.students.forEach(st => {
                            const el = document.createElement('div');
                            el.className = 'flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700';
                            el.innerHTML = `<div class="text-sm font-medium text-gray-900 dark:text-gray-100">${escapeHtml(st.name)}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-300">${escapeHtml(st.email || '')}</div>`;
                            modalBody.appendChild(el);
                        });
                    } else {
                        modalBody.innerHTML = `<div class="text-sm text-gray-600 dark:text-gray-400">No hay alumnos inscriptos.</div>`;
                    }
                }
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la información de la clase.' });
            }
        }

        function closeStudentsModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderGrid();
        });

        window.openStudentsModal = openStudentsModal;
        window.closeStudentsModal = closeStudentsModal;
    })();
    </script>
    @endpush
</x-layouts.app>