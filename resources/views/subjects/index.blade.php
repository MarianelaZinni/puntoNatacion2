<x-layouts.app title="Calendario de Clases">
    <div class="max-w-5xl mx-auto py-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Calendario de Clases</h1>
        <div id="calendar"></div>

        <!-- Modal para crear/editar clase -->
        <div id="class-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
            <form id="class-form" class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-lg w-full max-w-md space-y-4">
                <h2 class="text-xl font-bold mb-2" id="modal-title">Nueva Clase</h2>
                <input type="hidden" name="id" id="id">

                <div>
                    <label for="subject_type_id" class="block font-medium mb-1">Clase</label>
                    <select name="subject_type_id" id="subject_type_id" class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100" required>
                        <option value="">Seleccionar clase</option>
                        @foreach($subjectTypes as $subjectType)
                            <option value="{{ $subjectType->id }}">{{ $subjectType->description ?? $subjectType->value ?? $subjectType->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="capacity" class="block font-medium mb-1">Cupo</label>
                    <input type="number" name="capacity" id="capacity" min="1" class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100" required>
                </div>

                <div>
                    <label for="day" class="block font-medium mb-1">Día</label>
                    <select name="day" id="day" class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100" required>
                        @foreach(['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <label for="start_time" class="block font-medium mb-1">Inicio</label>
                        <input type="time" name="start_time" id="start_time" class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100" required>
                    </div>
                    <div class="flex-1">
                        <label for="end_time" class="block font-medium mb-1">Fin</label>
                        <input type="time" name="end_time" id="end_time" class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100" required>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" id="delete-btn" class="hidden px-4 py-2 rounded hover:opacity-95">Eliminar</button>
                    <button type="button" id="cancel-btn" class="px-4 py-2 rounded hover:opacity-95">Cancelar</button>
                    <button type="submit" id="save-btn" class="px-4 py-2 rounded hover:opacity-95">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    /* Solo estilos para los botones del modal de clase */
    #class-modal #delete-btn,
    #class-modal #cancel-btn,
    #class-modal #save-btn {
        background-color: #29b1dc !important;
        border-color: #29b1dc !important;
        color: #fff !important;
    }
    #class-modal #delete-btn:hover,
    #class-modal #cancel-btn:hover,
    #class-modal #save-btn:hover,
    #class-modal #delete-btn:focus,
    #class-modal #cancel-btn:focus,
    #class-modal #save-btn:focus {
        filter: brightness(0.95);
    }

    /* Custom event layout rendered via eventContent */
    .fc-custom-event {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        box-sizing: border-box;
    }
    .fc-custom-left {
        display: flex;
        flex-direction: column;
        min-width: 0; /* for ellipsis to work */
    }
    /* We will render time inline with title; allow wrapping if needed */
    .fc-custom-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: inherit;
        white-space: normal;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /*
      IMPORTANT: Force internal FullCalendar scrollers to expand instead of scrolling internally.
      This removes calendar internal vertical scrollbar; the page will scroll instead.
    */
    .fc .fc-scroller {
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }
    .fc .fc-timegrid-body, .fc .fc-timegrid-slots, .fc .fc-timegrid {
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }

    /* Ensure the #calendar container expands to fit content */
    #calendar {
        height: auto !important;
        min-height: 460px;
    }

    /* Keep event contents readable and reserve a bit of right padding */
    .fc .fc-timegrid-event .fc-event-main-frame {
        box-sizing: border-box;
        padding-right: 80px;
    }

    /* Remove top white stripe and force transparency in FullCalendar internal wrappers */
    #calendar,
    .fc,
    .fc .fc-scrollgrid,
    .fc .fc-scroller,
    .fc .fc-timegrid {
      background: transparent !important;
      box-shadow: none !important;
    }
    </style>

    <script>
    (function () {
        // Idempotent initializer for the calendar so it works on client-side navigation
        const eventsUrl = "{{ url('/subjects/events') }}";
        const baseUrl = "{{ url('/subjects') }}";
        const subjectColors = @json($subjectColors);

        function formatTimeFromDate(dt) {
            if (!dt) return '';
            try {
                return dt.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return dt.toTimeString().substring(0,5);
            }
        }

        function addMinutesToTime(date, minutes) {
            const d = new Date(date.getTime());
            d.setMinutes(d.getMinutes() + minutes);
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        }

        function timeToMinutes(t) {
            if (!t) return null;
            const [hh, mm] = t.split(':').map(Number);
            return hh * 60 + mm;
        }

        function validateTimes(startTime, endTime) {
            if (!startTime || !endTime) return false;
            const s = timeToMinutes(startTime);
            const e = timeToMinutes(endTime);
            return s < e;
        }

        function showModal() { document.getElementById('class-modal').classList.remove('hidden'); }
        function hideModal() { document.getElementById('class-modal').classList.add('hidden'); setFormValues({}); }

        function setFormValues(data) {
            ['id','subject_type_id','capacity','day','start_time','end_time'].forEach(function(field){
                const el = document.getElementById(field);
                if (el) el.value = data[field] || '';
            });
        }

        function getDayName(date) {
            return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][date.getDay()];
        }

        // main init function (idempotent)
        window.initSubjectsCalendar = function initSubjectsCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            // destroy previous instance if exists
            if (window._subjectsCalendar) {
                try { window._subjectsCalendar.destroy(); } catch (e) { /* ignore */ }
                window._subjectsCalendar = null;
            }

            // Ensure container is auto height so internal scrollers won't appear
            calendarEl.style.height = 'auto';

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridDay',
                locale: 'es',
                firstDay: 1,
                allDaySlot: false,
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00',

                // let FullCalendar size itself by content (no internal scroller)
                height: 'auto',

                buttonText: {
                    today: 'Hoy',
                    day: 'Día',
                    week: 'Semana',
                    month: 'Mes',
                    list: 'Agenda'
                },

                views: {
                    timeGridDay: { titleFormat: { year: 'numeric', month: 'long', day: 'numeric' } },
                    timeGridWeek: { titleFormat: { year: 'numeric', month: 'short', day: 'numeric' } }
                },

                events: {
                    url: eventsUrl,
                    method: 'GET',
                    failure: function() {
                        console.error('No se pudieron cargar las clases desde', eventsUrl);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las clases. Revisa la consola.'});
                    }
                },

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek'
                },

                selectable: true,
                editable: true,

                /* IMPORTANT CHANGE:
                   Render the time inline with the title so the time text is never hidden by internal layout.
                   This solves the issue where arg.timeText disappears in some stacked/column layouts.
                */
                eventContent: function(arg) {
                    const ev = arg.event;
                    const container = document.createElement('div');
                    container.className = 'fc-custom-event';

                    const left = document.createElement('div');
                    left.className = 'fc-custom-left';

                    const titleEl = document.createElement('div');
                    titleEl.className = 'fc-custom-title';

                    // Put the time text inline before the title so it is always visible
                    const timePrefix = arg.timeText ? arg.timeText + ' — ' : '';
                    // Use the event title (which already may include "Cupo: ..." from backend)
                    titleEl.textContent = timePrefix + (ev.title || '');

                    left.appendChild(titleEl);
                    container.appendChild(left);
                    return { domNodes: [container] };
                },

                eventDidMount: function(info) {
                    try {
                        const ev = info.event;
                        const color = subjectColors[ev.extendedProps.subject_type_id] || ev.extendedProps.color || '#29b1dc';
                        info.el.style.backgroundColor = color;
                        info.el.style.borderColor = color;
                        info.el.style.overflow = 'visible';
                    } catch (e) {
                        console.warn('eventDidMount warning', e);
                    }
                },

                select: function(info) {
                    const start = info.start;
                    const end = info.end;
                    showModal();
                    setFormValues({
                        day: getDayName(start),
                        start_time: String(start.getHours()).padStart(2,'0') + ':' + String(start.getMinutes()).padStart(2,'0'),
                        end_time: String(new Date(end.getTime() - 1).getHours()).padStart(2,'0') + ':' + String(new Date(end.getTime() - 1).getMinutes()).padStart(2,'0')
                    });
                    document.getElementById('modal-title').textContent = 'Nueva Clase';
                    document.getElementById('delete-btn').classList.add('hidden');
                },

                dateClick: function(info) {
                    const clicked = info.date;
                    showModal();
                    const defaultEnd = addMinutesToTime(clicked, 50);
                    const startTime = String(clicked.getHours()).padStart(2,'0') + ':' + String(clicked.getMinutes()).padStart(2,'0');
                    setFormValues({
                        day: getDayName(clicked),
                        start_time: startTime,
                        end_time: defaultEnd
                    });
                    document.getElementById('modal-title').textContent = 'Nueva Clase';
                    document.getElementById('delete-btn').classList.add('hidden');
                },

                eventClick: function(info) {
                    showModal();
                    setFormValues({
                        id: info.event.id,
                        subject_type_id: info.event.extendedProps.subject_type_id,
                        capacity: info.event.extendedProps.capacity,
                        day: info.event.extendedProps.day,
                        start_time: info.event.start ? formatTimeFromDate(info.event.start) : (info.event.extendedProps.startTime || ''),
                        end_time: info.event.end ? formatTimeFromDate(info.event.end) : (info.event.extendedProps.endTime || ''),
                    });
                    document.getElementById('modal-title').textContent = 'Editar Clase';
                    document.getElementById('delete-btn').classList.remove('hidden');
                },

                eventDrop: function(info) {
                    const event = info.event;
                    const newDay = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][event.start.getDay()];
                    fetch(baseUrl + '/' + event.id + '/move', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            day: newDay,
                            start_time: event.start.toTimeString().substring(0,5),
                            end_time: event.end.toTimeString().substring(0,5),
                        })
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('Network response was not ok');
                        return r.json();
                    })
                    .then(() => window._subjectsCalendar.refetchEvents())
                    .catch(err => {
                        console.error('Error al mover la clase:', err);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo mover la clase. Revisa la consola.'});
                        window._subjectsCalendar.refetchEvents();
                    });
                },
            });

            // --- Deferred render: only render when #calendar is visible/have size ---
            function ensureCalendarRendered() {
                if (calendar._rendered) return;
                try {
                    calendar.render();
                    calendar._rendered = true;
                    // attach to global reference once rendered
                    window._subjectsCalendar = calendar;
                    // force a size update if available
                    if (typeof calendar.updateSize === 'function') calendar.updateSize();
                } catch (err) {
                    console.warn('Error al renderizar calendario, reintentando:', err);
                    setTimeout(() => {
                        try {
                            calendar.render();
                            calendar._rendered = true;
                            window._subjectsCalendar = calendar;
                            if (typeof calendar.updateSize === 'function') calendar.updateSize();
                        } catch (e) {
                            console.error('Segundo intento de render falló', e);
                        }
                    }, 200);
                }
            }

            // If the element already has size, render now; otherwise observe
            const rect = calendarEl.getBoundingClientRect();
            if (rect.width > 0 && rect.height > 0 && window.getComputedStyle(calendarEl).display !== 'none') {
                ensureCalendarRendered();
            } else if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            ensureCalendarRendered();
                            try { io.disconnect(); } catch(e) {}
                        }
                    });
                }, { root: null, threshold: 0.01 });
                io.observe(calendarEl);
            } else {
                // fallback poll
                let attempts = 0;
                const interval = setInterval(() => {
                    attempts++;
                    const r = calendarEl.getBoundingClientRect();
                    if ((r.width > 0 && r.height > 0 && window.getComputedStyle(calendarEl).display !== 'none') || attempts >= 25) {
                        clearInterval(interval);
                        ensureCalendarRendered();
                    }
                }, 200);
            }
        }; // end initSubjectsCalendar

        // Call on standard DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function () {
            window.initSubjectsCalendar();
        });

        // Also call immediately if document already parsed (client-side nav might inject view after DOMContentLoaded)
        if (document.readyState === 'interactive' || document.readyState === 'complete') {
            setTimeout(() => window.initSubjectsCalendar(), 0);
        }

        // Listen to common PJAX/Turbo/Flux navigation events so the calendar is initialized on client-side navigation.
        [
            'turbo:load',
            'turbolinks:load',
            'pjax:complete',
            'flux:navigate',
            'flux:content:loaded',
            'load'
        ].forEach(evt => {
            document.addEventListener(evt, () => {
                // small delay to let the new content be mounted
                setTimeout(() => window.initSubjectsCalendar(), 50);
            });
        });

        // MutationObserver fallback: if #calendar is added dynamically, init calendar
        const mo = new MutationObserver((mutations) => {
            for (const m of mutations) {
                for (const n of m.addedNodes) {
                    if (n instanceof HTMLElement) {
                        if (n.id === 'calendar' || n.querySelector && n.querySelector('#calendar')) {
                            setTimeout(() => window.initSubjectsCalendar(), 20);
                            return;
                        }
                    }
                }
            }
        });
        mo.observe(document.body, { childList: true, subtree: true });

        // Modal handlers and form submit (unchanged)
        document.addEventListener('click', function(e){
            if (e.target && e.target.id === 'cancel-btn') hideModal();
        });
        window.onclick = function(e) {
            if (e.target == document.getElementById('class-modal')) hideModal();
        };

        document.getElementById('class-form').onsubmit = function(e) {
            e.preventDefault();

            const id = document.getElementById('id').value;
            const subject_type_id = document.getElementById('subject_type_id').value;
            const capacity = document.getElementById('capacity').value;
            const day = document.getElementById('day').value;
            const start_time = document.getElementById('start_time').value;
            const end_time = document.getElementById('end_time').value;

            if (!validateTimes(start_time, end_time)) {
                Swal.fire({ icon: 'warning', title: 'Horas inválidas', text: 'La hora de inicio debe ser menor que la hora de fin.'});
                return;
            }

            const url = id ? baseUrl + '/' + id : baseUrl;
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    subject_type_id,
                    capacity,
                    day,
                    start_time,
                    end_time,
                })
            })
            .then(r => {
                if (!r.ok) return r.text().then(t => { throw new Error(t || 'Network response not ok'); });
                return r.json();
            })
            .then(data => {
                hideModal();
                if (window._subjectsCalendar) window._subjectsCalendar.refetchEvents();
                Swal.fire({ icon: 'success', title: 'Listo', text: 'Clase guardada correctamente.'});
            })
            .catch(err => {
                console.error('Error al guardar la clase:', err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la clase. Revisa la consola.'});
            });
        };

        document.getElementById('delete-btn').onclick = function() {
            const id = document.getElementById('id').value;
            if (!id) return;

            Swal.fire({
                title: '¿Seguro que deseas eliminar esta clase?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#777',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(baseUrl + '/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(r => {
                        if (!r.ok) return r.text().then(t => { throw new Error(t || 'Network response not ok'); });
                        return r.json();
                    })
                    .then(data => {
                        hideModal();
                        if (window._subjectsCalendar) window._subjectsCalendar.refetchEvents();
                        Swal.fire({ icon: 'success', title: 'Eliminado', text: 'La clase fue eliminada.'});
                    })
                    .catch(err => {
                        console.error('Error al eliminar la clase:', err);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar la clase. Revisa la consola.'});
                    });
                }
            });
        };
    })();
    </script>
</x-layouts.app>