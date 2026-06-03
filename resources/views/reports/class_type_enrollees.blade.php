<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Inscriptos por tipo de clase</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color:#111; margin:20px; }
        h1 { font-size:18px; margin:0 0 8px; }
        .meta { color:#555; margin-bottom:12px; font-size:13px; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        th, td { padding:8px; border:1px solid #ddd; text-align:left; vertical-align:middle; }
        th { background:#f5f5f5; font-weight:600; }
        .no-data { padding:12px; background:#fff6f6; border:1px solid #ffd6d6; color:#800; }
        .class-info { margin-bottom:12px; }
    </style>
</head>
<body>
    <h1>{{ $company ?? 'Mi Escuela' }} — Inscriptos por tipo de clase</h1>
    <div class="meta">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>

    @if(empty($subject_type))
        <div class="no-data">No se seleccionó ningún tipo de clase.</div>
    @else
        <div class="class-info">
            <strong>{{ $subject_type->description }}</strong><br>
            Clases de este tipo: {{ $classes_count }}<br>
            Alumnos únicos inscriptos: {{ $students->count() }}
        </div>

        @include('reports.partials._class_enrollees_table', ['printMode' => true])
    @endif
</body>
</html>
