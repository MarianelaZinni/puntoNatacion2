<x-layouts.app title="Reportes">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold">Reportes</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('reports.class_enrollees.form') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-[#29b1dc] dark:hover:border-[#29b1dc] transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-[#29b1dc] group-hover:bg-[#29b1dc] group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">1. Inscriptos por clase</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Seleccioná una clase y generá el listado de inscriptos.</div>
                </div>
            </a>

            <a href="{{ route('reports.debtors.form') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-red-400 dark:hover:border-red-500 transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">3. Alumnos deudores</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Listado de alumnos con deuda (filtrable por monto mínimo).</div>
                </div>
            </a>

             <a href="{{ route('reports.class_type_enrollees.form') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-cyan-400 dark:hover:border-cyan-500 transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-cyan-50 dark:bg-cyan-900/30 flex items-center justify-center text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10m-10 5h16"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">2. Alumnos por tipo de clase</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Filtrá por tipo de clase para ver todos los alumnos, sin importar horario.</div>
                </div>
            </a>

            <a href="{{ route('reports.payments_by_student.form') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-500 transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">4. Pagos por alumno</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Seleccioná un alumno y rango de periodos para ver sus pagos.</div>
                </div>
            </a>

            <a href="{{ route('reports.all_students.view') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-gray-400 dark:hover:border-gray-400 transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 group-hover:bg-gray-500 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">5. Todos los alumnos</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Listado completo de alumnos (imprimible / PDF).</div>
                </div>
            </a>

            <a href="{{ route('medical_checkups.report') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-lime-400 dark:hover:border-lime-500 transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-lime-50 dark:bg-lime-900/30 flex items-center justify-center text-lime-600 group-hover:bg-lime-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">6. Revisiones Médicas</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Filtrá revisiones por estado y período. Exportable a PDF.</div>
                </div>
            </a>

            <a href="{{ route('reports.accounting.form') }}"
               class="flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl hover:shadow-md hover:border-green-500 dark:hover:border-green-500 transition group">
                <div class="shrink-0 h-10 w-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600 group-hover:bg-green-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">7. Reporte Contable</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Todos los pagos por rango de fechas. Exportable a Excel.</div>
                </div>
            </a>
        </div>
    </div>
</x-layouts.app>