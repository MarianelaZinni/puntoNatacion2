<x-layouts.app :title="__('Dashboard')">

    @if(auth()->user()?->isAdmin())
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
@endif
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

    <style>
.slot-card {
    cursor: pointer;
}

.slot-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.10);
}
    </style>
    
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

        function timeToMinutes(time) {
    if (!time) return 0;

    const [hours, minutes] = time
        .substring(0, 5)
        .split(':')
        .map(Number);

    return (hours * 60) + minutes;
}

        // build scheduleMap
      function buildScheduleMap() {
    const map = {};

    days.forEach(day => {
        map[day] = [];
    });

    subjects.forEach(subject => {
        if (!subject || !subject.day || !subject.start_time) {
            return;
        }

        if (!map[subject.day]) {
            return;
        }

        map[subject.day].push(subject);
    });

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

function renderGridInto(container) {

    const scheduleMap = buildScheduleMap();

    /*
     * Escala vertical.
     *
     * 1 minuto = 2 píxeles.
     *
     * Esto se usa para calcular el espacio entre
     * diferentes horarios.
     */
    const pixelsPerMinute = 2;

    /*
     * Obtener todas las horas de inicio existentes
     * en toda la semana.
     *
     * Ejemplo:
     * 07:00
     * 07:05
     * 07:50
     * 08:40
     * 08:51
     */
    const startTimes = [];

    days.forEach(day => {

        const daySubjects = scheduleMap[day] || [];

        daySubjects.forEach(subj => {

            if (!subj || !subj.start_time) {
                return;
            }

            if (!startTimes.includes(subj.start_time)) {
                startTimes.push(subj.start_time);
            }
        });
    });

    /*
     * Orden cronológico de los horarios.
     */
    startTimes.sort((a, b) => {
        return timeToMinutes(a) - timeToMinutes(b);
    });

    /*
     * Agrupar las clases por día + horario.
     *
     * Esto permite que si, por ejemplo, hay:
     *
     * Viernes 07:00 -> clase A
     * Viernes 07:00 -> clase B
     *
     * ambas aparezcan una debajo de la otra.
     */
    const subjectsByDayAndTime = {};

    days.forEach(day => {

        subjectsByDayAndTime[day] = {};

        const daySubjects = scheduleMap[day] || [];

        daySubjects.forEach(subj => {

            if (!subj || !subj.start_time) {
                return;
            }

            if (!subjectsByDayAndTime[day][subj.start_time]) {
                subjectsByDayAndTime[day][subj.start_time] = [];
            }

            subjectsByDayAndTime[day][subj.start_time].push(subj);
        });
    });

    let html = `
        <div class="
            overflow-x-auto
            border
            border-gray-200
            dark:border-gray-700
            rounded
        ">

            <div class="min-w-[600px]">

                <!-- Encabezado de días -->
                <div class="
                    grid
                    grid-cols-6
                    gap-0
                    bg-gray-50
                    dark:bg-gray-800
                    border-b
                    border-gray-200
                    dark:border-gray-700
                ">
    `;

    days.forEach(day => {

        html += `
            <div class="
                px-3
                py-3
                text-sm
                font-semibold
                text-gray-700
                dark:text-gray-200
                text-center
                border-r
                border-gray-200
                dark:border-gray-700
            ">
                ${day}
            </div>
        `;
    });

    html += `
                </div>

                <!-- Filas de horarios -->
                <div>
    `;

    /*
     * Construimos una fila por cada horario de inicio.
     */
    startTimes.forEach((startTime, timeIndex) => {

        const currentMinutes =
            timeToMinutes(startTime);

        /*
         * Diferencia con el horario anterior.
         */
        let timeGap = 0;

        if (timeIndex > 0) {

            const previousMinutes =
                timeToMinutes(startTimes[timeIndex - 1]);

            timeGap =
                (currentMinutes - previousMinutes)
                * pixelsPerMinute;
        }

        /*
         * La separación mínima entre filas.
         *
         * Para el primer horario no agregamos espacio.
         */
        const paddingTop =
            timeIndex === 0
                ? 0
                : timeGap;

        html += `
            <div
                class="
                    grid
                    grid-cols-6
                    gap-0
                    items-start
                "
                style="
                    padding-top: ${paddingTop}px;
                "
            >
        `;

        days.forEach(day => {

            const classes =
                subjectsByDayAndTime[day][startTime] || [];

            html += `
                <div class="
                    px-2
                    border-r
                    border-gray-100
                    dark:border-gray-800
                    flex
                    flex-col
                    gap-2
                ">
            `;

            /*
             * Clases que empiezan exactamente
             * en este horario y este día.
             */
            classes.forEach(subj => {

                const color =
                    subjectColors[subj.subject_type_id]
                    || '#29b1dc';

                const enrolled =
                    subj.students
                        ? subj.students.length
                        : 0;

                const title =
                    subj.subject_type
                        ? (
                            subj.subject_type.description ||
                            subj.subject_type.value ||
                            'Materia'
                        )
                        : 'Materia';

                html += `
                    <button
                        type="button"
                        data-subject-id="${subj.id}"
                        class="
                            w-full
                            text-left
                            block
                            p-2.5
                            rounded-lg
                            shadow-sm
                            cursor-pointer
                            slot-card
                            transition
                        "
                        style="
                            background: linear-gradient(
                                90deg,
                                ${hexToRgba(color, 0.16)},
                                ${hexToRgba(color, 0.06)}
                            );
                            border-left: 4px solid ${color};
                        "
                    >

                        <!-- Horario -->
                        <div class="
                            text-xs
                            font-medium
                            text-gray-500
                            dark:text-gray-400
                            whitespace-nowrap
                        ">
                            ${escapeHtml(subj.start_time)}
                            –
                            ${escapeHtml(subj.end_time)}
                        </div>

                        <!-- Nombre de la clase -->
                        <div class="
                            text-sm
                            font-semibold
                            text-gray-800
                            dark:text-gray-100
                            mt-1
                            leading-tight
                        ">
                            ${escapeHtml(title)}
                        </div>

                        <!-- Inscriptos -->
                        <div class="
                            text-xs
                            text-gray-600
                            dark:text-gray-300
                            mt-1
                        ">
                            ${enrolled}/${subj.capacity ?? '—'}
                        </div>

                        
                    </button>
                `;
            });

            html += `
                </div>
            `;
        });

        html += `
            </div>
        `;
    });

    html += `
                </div>
            </div>
        </div>
    `;

    container.innerHTML = html;

    /*
     * Click sobre una clase.
     *
     * Mantenemos exactamente el comportamiento
     * de la versión que funcionaba.
     */
    const TODAY_DATE = '{{ $todayDate }}';

    container
        .querySelectorAll('[data-subject-id]')
        .forEach(btn => {

            btn.addEventListener('click', function () {

                const subjId =
                    btn.getAttribute('data-subject-id');

                window.location.href =
                    `/attendance/take?subject_id=${
                        encodeURIComponent(subjId)
                    }&date=${
                        encodeURIComponent(TODAY_DATE)
                    }`;
            });
        });
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