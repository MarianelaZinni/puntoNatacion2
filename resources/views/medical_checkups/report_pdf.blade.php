<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Revisiones Médicas</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 20px; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        .meta { color: #555; margin-bottom: 12px; font-size: 11px; }
        .filters { color: #555; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 7px 8px; border: 1px solid #ddd; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; font-weight: 600; }
        .badge-approved { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 11px; }
        .badge-rejected { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 9999px; font-size: 11px; }
        .no-data { padding: 12px; background: #fff6f6; border: 1px solid #ffd6d6; color: #800; }
        .count { font-size: 11px; color: #555; margin-bottom: 8px; }
    </style>
</head>
<body>
    <h1>{{ $company ?? 'Punto Natación' }} — Revisiones Médicas</h1>
    <div class="meta">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>

    <div class="filters">
        @php
            $estadoLabel = match($filters['approved'] ?? '') {
                '1'     => 'Aprobada',
                '0'     => 'No Aprobada',
                default => 'Todos',
            };
            $periodoLabel = ($filters['period'] ?? '') !== ''
                ? \Carbon\Carbon::createFromFormat('Y-m', $filters['period'])->format('m/Y')
                : 'Todos';
        @endphp
        Estado: <strong>{{ $estadoLabel }}</strong> &nbsp;|&nbsp; Período: <strong>{{ $periodoLabel }}</strong>
    </div>

    @if($checkups->isEmpty())
        <div class="no-data">No se encontraron revisiones médicas con los filtros seleccionados.</div>
    @else
        <div class="count">{{ $checkups->count() }} {{ $checkups->count() === 1 ? 'revisión encontrada' : 'revisiones encontradas' }}</div>
        <table>
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Período</th>
                    <th>Fecha Revisión</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($checkups as $checkup)
                    @php
                        try {
                            $periodLabel = $checkup->period
                                ? \Carbon\Carbon::parse($checkup->period)->format('m/Y')
                                : '-';
                        } catch (\Throwable $e) {
                            $periodLabel = '-';
                        }
                    @endphp
                    <tr>
                        <td>{{ $checkup->student_name ?? '-' }}</td>
                        <td>{{ $periodLabel }}</td>
                        <td>{{ $checkup->checkup_date ? $checkup->checkup_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($checkup->approved)
                                <span class="badge-approved">Aprobada</span>
                            @else
                                <span class="badge-rejected">No Aprobada</span>
                            @endif
                        </td>
                        <td>{{ $checkup->observations ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>