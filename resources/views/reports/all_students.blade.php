<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Todos los alumnos</title>
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
    <h1>{{ $company ?? 'Mi Escuela' }} — Todos los alumnos</h1>
    <div class="meta">Generado: {{ ($generated_at ?? \Carbon\Carbon::now())->format('d/m/Y H:i') }}</div>

    @if(empty($students) || $students->isEmpty())
        <div class="no-data">No hay alumnos registrados.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Clases inscritas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $s)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->dni ?? '-' }}</td>
                        <td>{{ $s->email ?? '-' }}</td>
                        <td>{{ $s->phone ?? '-' }}</td>
                        <td>
                            @if(!empty($s->subjects) && $s->subjects->count())
                                {{ $s->subjects->pluck('subjectType.description')->filter()->implode(', ') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>