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
                    <label for="subject_type_id" class="block font-medium mb-1">Materia</label>
                    <select name="subject_type_id" id="subject_type_id" class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100" required>
                        <option value="">Seleccionar materia</option>
                        @foreach($subjectTypes as $subjectType)
                            <option value="{{ $subjectType->id }}">{{ $subjectType->name }}</option>
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
                    <button type="button" id="delete-btn" class="hidden px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                    <button type="button" id="cancel-btn" class="px-4 py-2 bg-gray-300 dark:bg-zinc-800 text-gray-700 dark:text-gray-100 rounded hover:bg-gray-400 dark:hover:bg-zinc-700">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-[#29b1dc] text-white rounded hover:bg-[#24a8cf]">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
    .fc-event-materia-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 0.92em;
        font-weight: 600;
        margin-bottom: 2px;
        color: #fff;
        box-shadow: 0 1px 4px 0 rgba(0,0,0,0.08);
        background: #29b1dc;
    }
    .fc-event-badge-capacity {
        font-size: 0.85em;
        font-weight: 400;
        margin-left: 5px;
        padding: 0 6px;
        border-radius: 6px;
        background: rgba(0,0,0,0.08);
        color: #fff;
        opacity: 0.9;
    }
    .dark .fc-event-materia-badge,
    .dark .fc-event-badge-capacity {
        color: #fff !important;
        border: none;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Baseline URLs (usar las rutas de subjects)
        const eventsUrl = "{{ url('/subjects/events') }}";
        const baseUrl = "{{ url('/subjects') }}";
        var subjectColors = @json($subjectColors);

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'es',
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            events: {
                url: eventsUrl,
                method: 'GET',
                failure: function() {
                    console.error('No se pudieron cargar las clases desde', eventsUrl);
                    alert('No se pudieron cargar las clases. Revisa la consola (Network / Console).');
                }
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay'
            },
            selectable: true,
            editable: true,
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
                .then(data => {
                    calendar.refetchEvents();
                })
                .catch(err => {
                    console.error('Error al mover la clase:', err);
                    alert('No se pudo mover la clase. Revisa la consola.');
                    calendar.refetchEvents();
                });
            },
            eventDidMount: function(info) {
                let color = subjectColors[info.event.extendedProps.subject_type_id] || '#29b1dc';
                info.el.style.backgroundColor = color;
                info.el.style.borderColor = color;

                // Cupos
                let capacity = info.event.extendedProps.capacity;
                let freeCapacity = info.event.extendedProps.free_capacity ?? capacity;

                // Badge materia + cupos
                let badge = document.createElement('span');
                badge.className = 'fc-event-materia-badge';
                badge.style.backgroundColor = color;
                badge.textContent = info.event.title;

                let cupos = document.createElement('span');
                cupos.className = 'fc-event-badge-capacity';
                cupos.textContent = `Cupo: ${capacity} | Libre: ${freeCapacity}`;
                cupos.style.backgroundColor = 'rgba(0,0,0,0.18)';

                badge.appendChild(cupos);

                // Insertar badge en el evento
                let titleNode = info.el.querySelector('.fc-event-title');
                if (titleNode) {
                    titleNode.innerHTML = '';
                    titleNode.appendChild(badge);
                } else {
                    info.el.prepend(badge);
                }
            },
            select: function(info) {
                showModal();
                setFormValues({
                    day: getDayName(info.start),
                    start_time: info.start.toTimeString().substring(0,5),
                    end_time: info.end.toTimeString().substring(0,5),
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
                    start_time: info.event.start.toTimeString().substring(0,5),
                    end_time: info.event.end.toTimeString().substring(0,5),
                });
                document.getElementById('modal-title').textContent = 'Editar Clase';
                document.getElementById('delete-btn').classList.remove('hidden');
            },
        });
        calendar.render();

        function showModal() {
            document.getElementById('class-modal').classList.remove('hidden');
        }
        function hideModal() {
            document.getElementById('class-modal').classList.add('hidden');
            setFormValues({});
        }
        document.getElementById('cancel-btn').onclick = hideModal;
        window.onclick = function(e) {
            if (e.target == document.getElementById('class-modal')) hideModal();
        };

        function setFormValues(data) {
            ['id','subject_type_id','capacity','day','start_time','end_time'].forEach(function(field){
                const el = document.getElementById(field);
                if (el) el.value = data[field] || '';
            });
        }

        function getDayName(date) {
            return ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'][date.getDay()];
        }

        document.getElementById('class-form').onsubmit = function(e) {
            e.preventDefault();
            var id = document.getElementById('id').value;
            var url = id ? baseUrl + '/' + id : baseUrl;
            var method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    subject_type_id: document.getElementById('subject_type_id').value,
                    capacity: document.getElementById('capacity').value,
                    day: document.getElementById('day').value,
                    start_time: document.getElementById('start_time').value,
                    end_time: document.getElementById('end_time').value,
                })
            })
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.json();
            })
            .then(data => {
                hideModal();
                calendar.refetchEvents();
            })
            .catch(err => {
                console.error('Error al guardar la clase:', err);
                alert('No se pudo guardar la clase. Revisa la consola.');
            });
        };

        document.getElementById('delete-btn').onclick = function() {
            var id = document.getElementById('id').value;
            if (!id) return;
            if (!confirm('¿Seguro que deseas eliminar esta clase?')) return;

            fetch(baseUrl + '/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.json();
            })
            .then(data => {
                hideModal();
                calendar.refetchEvents();
            })
            .catch(err => {
                console.error('Error al eliminar la clase:', err);
                alert('No se pudo eliminar la clase. Revisa la consola.');
            });
        };
    });
    </script>
</x-layouts.app>