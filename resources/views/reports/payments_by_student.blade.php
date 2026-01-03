<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Pagos por alumno</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color:#111; margin:20px; }
        h1 { font-size:18px; margin:0 0 8px; }
        .meta { color:#555; margin-bottom:12px; font-size:13px; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        th, td { padding:8px; border:1px solid #ddd; text-align:left; vertical-align:middle; }
        th { background:#f5f5f5; font-weight:600; }
        .no-data { padding:12px; background:#fff6f6; border:1px solid #ffd6d6; color:#800; }
    </style>
</head>
<body>
    <h1>{{ $company ?? 'Mi Escuela' }} — Pagos por alumno</h1>
    <div class="meta">
        Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}<br>
        @if($student)
            Alumno: {{ $student->name }}
        @else
            Todos los alumnos
        @endif
        @if($period_from || $period_to)
            <br>Periodo: 
            @if($period_from && $period_to)
                {{ \Carbon\Carbon::createFromFormat('Y-m', $period_from)->format('m/Y') }} - {{ \Carbon\Carbon::createFromFormat('Y-m', $period_to)->format('m/Y') }}
            @elseif($period_from)
                Desde {{ \Carbon\Carbon::createFromFormat('Y-m', $period_from)->format('m/Y') }}
            @elseif($period_to)
                Hasta {{ \Carbon\Carbon::createFromFormat('Y-m', $period_to)->format('m/Y') }}
            @endif
        @endif
    </div>

    @include('reports.partials._payments_table', ['printMode' => true])
</body>
</html>