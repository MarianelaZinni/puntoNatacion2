# Ejemplos de Historial de Precios

## Ejemplo 1: Creación de un Nuevo Precio

### Acción en el código:
```php
SubjectPrice::create([
    'subject_type_id' => 1,
    'has_teacher' => true,
    'times_per_week' => 2,
    'price' => 5000.00
]);
```

### Registro en subject_prices:
```
id | subject_type_id | has_teacher | times_per_week | price   | created_at
---+----------------+-------------+----------------+---------+--------------------
15 | 1              | true        | 2              | 5000.00 | 2026-02-28 10:30:00
```

### Registro automático en subject_price_history:
```
id | subject_price_id | subject_type_id | has_teacher | times_per_week | old_price | new_price | changed_at
---+------------------+----------------+-------------+----------------+-----------+-----------+--------------------
1  | 15               | 1              | true        | 2              | NULL      | 5000.00   | 2026-02-28 10:30:00
```

**Nota:** `old_price` es NULL porque es un precio nuevo.

---

## Ejemplo 2: Primera Actualización de Precio

### Acción:
```php
$price = SubjectPrice::find(15);
$price->update(['price' => 5500.00]);
```

### Cambio en subject_prices:
```
id | subject_type_id | has_teacher | times_per_week | price   | updated_at
---+----------------+-------------+----------------+---------+--------------------
15 | 1              | true        | 2              | 5500.00 | 2026-03-15 14:20:00
```

### Nuevo registro en subject_price_history:
```
id | subject_price_id | subject_type_id | has_teacher | times_per_week | old_price | new_price | changed_at
---+------------------+----------------+-------------+----------------+-----------+-----------+--------------------
1  | 15               | 1              | true        | 2              | NULL      | 5000.00   | 2026-02-28 10:30:00
2  | 15               | 1              | true        | 2              | 5000.00   | 5500.00   | 2026-03-15 14:20:00
```

---

## Ejemplo 3: Segunda Actualización (Aumento de Precio)

### Acción:
```php
$price->update(['price' => 6000.00]);
```

### Historial completo en subject_price_history:
```
id | subject_price_id | subject_type_id | has_teacher | times_per_week | old_price | new_price | changed_at
---+------------------+----------------+-------------+----------------+-----------+-----------+--------------------
1  | 15               | 1              | true        | 2              | NULL      | 5000.00   | 2026-02-28 10:30:00
2  | 15               | 1              | true        | 2              | 5000.00   | 5500.00   | 2026-03-15 14:20:00
3  | 15               | 1              | true        | 2              | 5500.00   | 6000.00   | 2026-04-01 09:00:00
```

### Visualización cronológica:
```
Feb 28, 2026 → Precio inicial: $5,000.00
Mar 15, 2026 → Aumento: $5,000.00 → $5,500.00 (+$500.00, +10%)
Apr 01, 2026 → Aumento: $5,500.00 → $6,000.00 (+$500.00, +9.09%)
```

---

## Ejemplo 4: Múltiples Precios con sus Historiales

### Tabla subject_prices (estado actual):
```
id | subject_type_id | has_teacher | times_per_week | price    | updated_at
---+----------------+-------------+----------------+----------+--------------------
10 | NULL           | true        | 1              | 3000.00  | 2026-01-15 10:00:00
11 | NULL           | true        | 2              | 5500.00  | 2026-03-20 15:30:00
12 | NULL           | false       | 1              | 2500.00  | 2026-02-10 11:00:00
13 | 1              | true        | 2              | 6000.00  | 2026-04-01 09:00:00
```

### Tabla subject_price_history (todos los cambios):
```
id | subject_price_id | old_price | new_price | changed_at
---+------------------+-----------+-----------+--------------------
1  | 10               | NULL      | 2800.00   | 2026-01-10 10:00:00
2  | 10               | 2800.00   | 3000.00   | 2026-01-15 10:00:00
3  | 11               | NULL      | 5000.00   | 2026-02-01 12:00:00
4  | 11               | 5000.00   | 5500.00   | 2026-03-20 15:30:00
5  | 12               | NULL      | 2500.00   | 2026-02-10 11:00:00
6  | 13               | NULL      | 5000.00   | 2026-02-28 10:30:00
7  | 13               | 5000.00   | 5500.00   | 2026-03-15 14:20:00
8  | 13               | 5500.00   | 6000.00   | 2026-04-01 09:00:00
```

---

## Consultas Útiles

### 1. Ver evolución de un precio específico
```php
$history = SubjectPriceHistory::where('subject_price_id', 13)
    ->orderBy('changed_at', 'asc')
    ->get(['old_price', 'new_price', 'changed_at']);

foreach ($history as $record) {
    echo sprintf(
        "%s: %s → %s\n",
        $record->changed_at->format('d/m/Y'),
        $record->old_price ?? 'NUEVO',
        $record->new_price
    );
}
```

**Salida:**
```
28/02/2026: NUEVO → 5000.00
15/03/2026: 5000.00 → 5500.00
01/04/2026: 5500.00 → 6000.00
```

### 2. Calcular incremento total
```php
$first = SubjectPriceHistory::where('subject_price_id', 13)
    ->orderBy('changed_at', 'asc')
    ->first();
    
$last = SubjectPriceHistory::where('subject_price_id', 13)
    ->orderBy('changed_at', 'desc')
    ->first();

$initialPrice = $first->new_price;
$currentPrice = $last->new_price;
$increase = $currentPrice - $initialPrice;
$percentIncrease = ($increase / $initialPrice) * 100;

echo "Precio inicial: $" . number_format($initialPrice, 2);
echo "Precio actual: $" . number_format($currentPrice, 2);
echo "Incremento: $" . number_format($increase, 2) . " (" . 
     number_format($percentIncrease, 2) . "%)";
```

**Salida:**
```
Precio inicial: $5,000.00
Precio actual: $6,000.00
Incremento: $1,000.00 (20.00%)
```

### 3. Encontrar todos los aumentos mayores a 10%
```php
$bigIncreases = SubjectPriceHistory::whereNotNull('old_price')
    ->whereRaw('((new_price - old_price) / old_price) > 0.10')
    ->with('subjectPrice')
    ->get();

foreach ($bigIncreases as $record) {
    $percent = (($record->new_price - $record->old_price) / $record->old_price) * 100;
    echo sprintf(
        "%s: $%s → $%s (+%.2f%%)\n",
        $record->changed_at->format('d/m/Y'),
        number_format($record->old_price, 2),
        number_format($record->new_price, 2),
        $percent
    );
}
```

### 4. Analizar cambios por mes
```php
use Illuminate\Support\Facades\DB;

$monthlyChanges = SubjectPriceHistory::select(
        DB::raw('DATE_FORMAT(changed_at, "%Y-%m") as month'),
        DB::raw('COUNT(*) as total_changes'),
        DB::raw('AVG(new_price - old_price) as avg_increase')
    )
    ->whereNotNull('old_price')
    ->groupBy('month')
    ->orderBy('month', 'desc')
    ->get();
```

---

## Escenarios Especiales

### Sin cambio en el precio (no se registra)
```php
$price->update(['has_teacher' => false]); // Solo cambia has_teacher
// NO se crea registro en history porque 'price' no cambió
```

### Mismo valor de precio (no se registra)
```php
$price->update(['price' => 5000.00]); // Ya es 5000.00
// NO se crea registro porque el valor no cambió realmente
```

### Eliminación de precio
```php
$price->delete();
// Todos los registros en subject_price_history con subject_price_id = 15
// se eliminan automáticamente (CASCADE)
```

---

## Beneficios del Historial

### 1. Auditoría
Puedes ver exactamente cuándo y cómo cambió cada precio.

### 2. Análisis de Tendencias
Identifica patrones de incremento de precios a lo largo del tiempo.

### 3. Trazabilidad
Si hay una discrepancia, puedes rastrear el historial completo.

### 4. Reportes
Genera reportes de evolución de precios para análisis financiero.

### 5. Datos para Decisiones
Ayuda a tomar decisiones informadas sobre futuros ajustes de precios.
