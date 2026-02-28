# Sistema de Historial de Precios (Cuotas)

## Descripción
Este sistema mantiene automáticamente un historial completo de todos los cambios de precios en las cuotas. El historial se guarda en la base de datos pero no se muestra en la interfaz de usuario.

## Estructura de la Base de Datos

### Tabla: `subject_price_history`

Campos:
- `id`: ID único del registro histórico
- `subject_price_id`: FK al precio actual en `subject_prices`
- `subject_type_id`: Referencia al tipo de materia (nullable)
- `has_teacher`: Boolean indicando si es con profesor
- `times_per_week`: Número de veces por semana (1-5)
- `old_price`: Precio anterior (NULL para registros nuevos)
- `new_price`: Nuevo precio establecido
- `changed_at`: Fecha y hora del cambio
- `created_at`, `updated_at`: Timestamps de Laravel

### Índices
- `(subject_price_id, changed_at)`: Para consultas por precio específico
- `changed_at`: Para consultas cronológicas

## Funcionamiento Automático

El sistema utiliza **Model Events** de Laravel para rastrear cambios automáticamente:

### Creación de Nuevos Precios
Cuando se crea un nuevo registro en `subject_prices`:
```php
SubjectPrice::create([
    'subject_type_id' => 1,
    'has_teacher' => true,
    'times_per_week' => 2,
    'price' => 5000.00
]);
```

Se registra automáticamente en el historial:
- `old_price`: NULL (nuevo registro)
- `new_price`: 5000.00
- `changed_at`: timestamp actual

### Actualización de Precios
Cuando se actualiza el precio de un registro existente:
```php
$subjectPrice->update(['price' => 6000.00]);
```

Se registra automáticamente:
- `old_price`: 5000.00 (precio anterior)
- `new_price`: 6000.00 (nuevo precio)
- `changed_at`: timestamp actual

**Nota**: Solo se registra el cambio si el campo `price` realmente cambió.

## Modelo: SubjectPriceHistory

### Métodos Principales

#### `logPriceChange($subjectPrice, $oldPrice, $newPrice)`
Método estático para registrar un cambio de precio:

```php
SubjectPriceHistory::logPriceChange(
    $subjectPrice,  // Instancia de SubjectPrice
    5000.00,        // Precio anterior (o null)
    6000.00         // Nuevo precio
);
```

### Relaciones

#### En SubjectPrice
```php
$subjectPrice->priceHistory; // Obtiene todos los cambios históricos
```

#### En SubjectPriceHistory
```php
$history->subjectPrice;  // Obtiene el precio actual
$history->subjectType;   // Obtiene el tipo de materia
```

## Consultas de Ejemplo

### Ver historial completo de un precio específico
```php
$history = SubjectPriceHistory::where('subject_price_id', $id)
    ->orderBy('changed_at', 'desc')
    ->get();
```

### Ver todos los cambios de precio en un rango de fechas
```php
$changes = SubjectPriceHistory::whereBetween('changed_at', [$desde, $hasta])
    ->orderBy('changed_at', 'desc')
    ->get();
```

### Ver aumentos de precio (donde new_price > old_price)
```php
$increases = SubjectPriceHistory::whereNotNull('old_price')
    ->whereRaw('new_price > old_price')
    ->get();
```

### Calcular promedio de precios históricos
```php
$avgPrice = SubjectPriceHistory::where('subject_price_id', $id)
    ->avg('new_price');
```

## Migración

Para aplicar el historial a la base de datos:

```bash
php artisan migrate
```

Esto creará la tabla `subject_price_history` con todos los campos e índices necesarios.

## Ventajas del Sistema

1. **Automático**: No requiere código adicional al crear o actualizar precios
2. **Completo**: Registra todos los cambios con timestamps exactos
3. **Auditable**: Permite rastrear quién cambió qué y cuándo
4. **Eficiente**: Usa índices optimizados para consultas rápidas
5. **No invasivo**: No afecta la funcionalidad existente
6. **Datos históricos**: Mantiene copia de la configuración completa (subject_type_id, has_teacher, etc.)

## Consideraciones

- El historial se mantiene indefinidamente. Si se necesita, se puede implementar una limpieza periódica de registros antiguos.
- Si se elimina un `SubjectPrice`, sus registros históricos también se eliminan (CASCADE).
- Los cambios solo se registran cuando el campo `price` cambia, no cuando se modifican otros campos.

## Integración Futura

Este sistema permite implementar fácilmente:
- Reportes de evolución de precios
- Gráficos de tendencias de precios
- Auditorías de cambios
- Análisis de impacto de cambios de precio
- Restauración de precios anteriores
