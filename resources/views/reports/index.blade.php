<x-layouts.app title="Reportes">
    <div class="max-w-4xl mx-auto py-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Reportes</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('reports.class_enrollees.form') }}" class="p-4 border rounded hover:shadow">
                <div class="font-semibold">1. Inscriptos por clase</div>
                <div class="text-sm text-gray-600 mt-1">Seleccioná una clase y generá el listado de inscriptos.</div>
            </a>

            <a href="{{ route('reports.debtors.form') }}" class="p-4 border rounded hover:shadow">
                <div class="font-semibold">2. Alumnos deudores</div>
                <div class="text-sm text-gray-600 mt-1">Listado de alumnos con deuda (filtrable por monto mínimo).</div>
            </a>

            <a href="{{ route('reports.payments_by_student.form') }}" class="p-4 border rounded hover:shadow">
                <div class="font-semibold">3. Pagos por alumno</div>
                <div class="text-sm text-gray-600 mt-1">Seleccioná un alumno y rango de periodos para ver sus pagos.</div>
            </a>

            <a href="{{ route('reports.all_students.view') }}"
   target="_self"
   rel="noopener"
   onclick="window.location.href='{{ route('reports.all_students.view') }}'; return false;"
   class="p-4 border rounded hover:shadow">
    <div class="font-semibold">4. Todos los alumnos</div>
    <div class="text-sm text-gray-600 mt-1">Listado completo de alumnos (imprimible / PDF).</div>
</a>

           
        </div>
    </div>
</x-layouts.app>