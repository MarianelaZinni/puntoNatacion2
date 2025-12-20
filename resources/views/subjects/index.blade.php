<x-layouts.app title="Calendario de Clases">
    <div class="max-w-5xl mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Calendario de Clases</h1>

            <button id="new-class-btn" class="inline-flex items-center gap-2 px-4 py-2 bg-[#29b1dc] hover:bg-[#24a8cf] text-white rounded shadow transition focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc]">
                Nueva clase
            </button>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
            <div id="timetable-wrapper" class="overflow-auto">
                <div id="timetable-grid" class="min-w-full"></div>
            </div>
        </div>

        <!-- Modal para editar/crear clase -->
        <div id="class-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
            <form id="class-form" class="bg-white dark:bg-zinc-900 p-6 rounded-lg shadow-lg w-full max-w-md space-y-4">
                <h2 class="text-xl font-bold mb-2" id="modal-title">Editar Clase</h2>
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
                    <input type="number" name="capacity" id="capacity" min="1"
                           class="w-full rounded border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
                           required>
                    <div id="capacity-hint" class="text-xs text-gray-500 dark:text-gray-400 mt-1" style="display:none;"></div>
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

                <div>
                    <label class="block font-medium mb-1">Inscriptos</label>
                    <div id="modal-students" class="max-h-40 overflow-auto text-sm text-gray-700 dark:text-gray-200"></div>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" id="delete-btn" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Eliminar</button>
                    <button type="button" id="cancel-btn" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700">Cancelar</button>
                    <button type="submit" id="save-btn" class="px-4 py-2 rounded bg-[#29b1dc] text-white hover:bg-[#24a8cf]">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .slot-card { cursor:pointer; }
    .slot-card:hover { transform: translateY(-2px); transition: transform .12s ease; }
    </style>

    @push('scripts')
    <script>
    (function () {
        // Data injected from controller
        const subjects = @json($subjectsForJs);
        const subjectTypes = @json($subjectTypesForJs);
        const subjectColors = @json($subjectColors);

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

        function addMinutesToTime(timeStr, minutes) {
            // timeStr "HH:MM"
            const [hh, mm] = (timeStr || '07:00').split(':').map(Number);
            const d = new Date(2025,0,1, hh, mm, 0);
            d.setMinutes(d.getMinutes() + minutes);
            const H = String(d.getHours()).padStart(2,'0');
            const M = String(d.getMinutes()).padStart(2,'0');
            return `${H}:${M}`;
        }

        // Build schedule map
        const scheduleMap = {};
        for (const d of days) {
            scheduleMap[d] = {};
            for (const s of slots) scheduleMap[d][s] = [];
        }
        for (const s of subjects) {
            if (!s || !s.day || !s.start_time) continue;
            if (!scheduleMap[s.day]) continue;
            scheduleMap[s.day][s.start_time] = scheduleMap[s.day][s.start_time] || [];
            scheduleMap[s.day][s.start_time].push(s);
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

        // Render grid
        function renderGrid() {
            const wrapper = document.getElementById('timetable-grid');
            let html = '<div class="overflow-auto border border-gray-200 dark:border-gray-700 rounded">';

            // Header
            html += '<div class="grid grid-cols-6 gap-0 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">';
            for (let i=0;i<days.length;i++) {
                html += `<div class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 text-center">${days[i]}</div>`;
            }
            html += '</div>';

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
                            const free = Math.max(0, (subj.capacity || 0) - enrolled);
                            const title = subj.subject_type ? (subj.subject_type.description || subj.subject_type.value || 'Materia') : 'Materia';
                            cellInner += `
                                <button
                                    type="button"
                                    class="slot-card w-full text-left block mb-1 p-2 rounded shadow-sm"
                                    style="background: linear-gradient(90deg, ${hexToRgba(color,0.16)}, ${hexToRgba(color,0.06)}); border-left:4px solid ${color};"
                                    data-subject-id="${subj.id}"
                                    data-subject='${escapeHtml(JSON.stringify(subj))}'
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">${escapeHtml(title)}</div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-300 mt-1">${escapeHtml(subj.start_time)} — ${escapeHtml(subj.end_time)}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">${enrolled}/${subj.capacity || '—'}</div>
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
            wrapper.innerHTML = html;

            // Attach click handlers for slot cards
            document.querySelectorAll('.slot-card').forEach(btn => {
                btn.addEventListener('click', function () {
                    const subjStr = btn.dataset.subject;
                    let subj;
                    try { subj = JSON.parse(subjStr); } catch(e) { console.error(e); return; }
                    openEditModal(subj);
                });
            });
        }

        // Modal logic
        const modal = document.getElementById('class-modal');
        const form = document.getElementById('class-form');
        const modalStudents = document.getElementById('modal-students');
        const capacityInput = document.getElementById('capacity');
        const capacityHint = document.getElementById('capacity-hint');

        function openEditModal(subj) {
            // fill form
            document.getElementById('id').value = subj.id || '';
            document.getElementById('subject_type_id').value = subj.subject_type_id || '';
            capacityInput.value = subj.capacity || '';
            capacityInput.dataset.enrolled = (subj.students ? subj.students.length : 0);
            document.getElementById('day').value = subj.day || '';
            document.getElementById('start_time').value = subj.start_time || '';
            document.getElementById('end_time').value = subj.end_time || '';

            // capacity hint: show current enrolled count
            const enrolledCount = subj.students ? subj.students.length : 0;
            if (enrolledCount > 0) {
                capacityHint.style.display = 'block';
                capacityHint.textContent = `Inscripto(s) actualmente: ${enrolledCount}. El cupo no puede ser menor a este valor.`;
            } else {
                capacityHint.style.display = 'none';
                capacityHint.textContent = '';
            }

            // students list
            modalStudents.innerHTML = '';
            if (subj.students && subj.students.length) {
                subj.students.forEach(st => {
                    const div = document.createElement('div');
                    div.className = 'py-1 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between';
                    div.innerHTML = `<div class="text-sm font-medium text-gray-900 dark:text-gray-100">${escapeHtml(st.name)}</div><div class="text-xs text-gray-500 dark:text-gray-300">${escapeHtml(st.email || '')}</div>`;
                    modalStudents.appendChild(div);
                });
            } else {
                modalStudents.innerHTML = '<div class="text-sm text-gray-600 dark:text-gray-400">No hay alumnos inscriptos.</div>';
            }

            document.getElementById('modal-title').textContent = subj.id ? 'Editar Clase' : 'Nueva Clase';
            document.getElementById('delete-btn').classList.toggle('hidden', !subj.id);

            modal.classList.remove('hidden');
        }

        function hideModal() {
            modal.classList.add('hidden');
            form.reset();
            modalStudents.innerHTML = '';
            capacityHint.style.display = 'none';
            capacityHint.textContent = '';
            capacityInput.dataset.enrolled = 0;
        }

        document.getElementById('cancel-btn').addEventListener('click', hideModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) hideModal();
        });

        // Create new class button
        document.getElementById('new-class-btn').addEventListener('click', function () {
            const defaultStart = slots[0];
            const defaultEnd = addMinutesToTime(defaultStart, slotMinutes);
            openEditModal({
                id: null,
                subject_type_id: '',
                capacity: '',
                day: 'Lunes',
                start_time: defaultStart,
                end_time: defaultEnd,
                students: []
            });
        });

        // Save (create/update) with client-side capacity check
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('id').value;
            const subject_type_id = document.getElementById('subject_type_id').value;
            const capacity = parseInt(capacityInput.value || '0', 10);
            const enrolled = parseInt(capacityInput.dataset.enrolled || '0', 10);
            const day = document.getElementById('day').value;
            const start_time = document.getElementById('start_time').value;
            const end_time = document.getElementById('end_time').value;

            if (!start_time || !end_time || start_time >= end_time) {
                Swal.fire({ icon: 'warning', title: 'Horas inválidas', text: 'La hora de inicio debe ser menor que la hora de fin.'});
                return;
            }

            // If editing, validate capacity >= enrolled
            if (id && !isNaN(enrolled) && capacity < enrolled) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cupo inválido',
                    text: `El cupo no puede ser menor que la cantidad de alumnos actualmente inscriptos (${enrolled}).`
                });
                return;
            }

            const url = id ? `{{ url('subjects') }}/${id}` : `{{ url('subjects') }}`;
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ subject_type_id, capacity, day, start_time, end_time })
            })
            .then(r => {
                if (!r.ok) return r.text().then(t => { throw new Error(t || 'Network response not ok'); });
                return r.json();
            })
            .then(data => {
                hideModal();
                window.location.reload();
            })
            .catch(err => {
                // If server returned a 422 JSON message, show it
                try {
                    const parsed = JSON.parse(err.message);
                    if (parsed && parsed.message) {
                        Swal.fire({ icon: 'error', title: 'Error', text: parsed.message });
                        return;
                    }
                } catch (_) {}
                console.error('Error al guardar la clase:', err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la clase. Revisa la consola.'});
            });
        });

        // Delete
        document.getElementById('delete-btn').addEventListener('click', function () {
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
                if (!result.isConfirmed) return;
                fetch(`{{ url('subjects') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => {
                    if (!r.ok) return r.text().then(t => { throw new Error(t || 'Network response not ok'); });
                    return r.json();
                })
                .then(data => {
                    hideModal();
                    window.location.reload();
                })
                .catch(err => {
                    console.error('Error al eliminar la clase:', err);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar la clase. Revisa la consola.'});
                });
            });
        });

        // Inicial render
        document.addEventListener('DOMContentLoaded', function () {
            renderGrid();
        });

    })();
    </script>
    @endpush
</x-layouts.app>