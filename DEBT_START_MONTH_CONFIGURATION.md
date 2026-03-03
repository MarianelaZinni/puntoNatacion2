# Configuración del Mes de Inicio de Deuda

## Descripción General

Esta funcionalidad permite configurar a partir de qué mes del año se comienza a calcular la deuda de los alumnos, evitando que se genere deuda en meses anteriores al inicio del año académico.

## Problema que Resuelve

Anteriormente, si un alumno se registraba en el sistema en enero pero el año lectivo comenzaba en marzo, el sistema calculaba deuda por los meses de enero y febrero, lo cual no era correcto.

Con esta configuración, ahora puedes definir que la deuda solo se genere a partir de un mes específico (por ejemplo, marzo), independientemente de cuándo se haya creado el registro del alumno.

## Configuración

### Archivo: `.env`

Agrega o modifica las siguientes variables en tu archivo `.env`:

```env
# Mes de inicio del año lectivo para cálculo de deuda (1-12)
# Ejemplo: 3 = Marzo (por defecto)
DEBT_START_MONTH=3

# Día del mes a partir del cual se considera vencido el periodo actual
DEBT_DUE_DAY=10
```

### Valores Válidos

- **DEBT_START_MONTH**: Número del 1 al 12
  - 1 = Enero
  - 2 = Febrero
  - 3 = Marzo (valor por defecto)
  - 4 = Abril
  - ... y así sucesivamente
  
- **DEBT_DUE_DAY**: Número del 1 al 31
  - Por defecto: 10
  - Define el día a partir del cual el mes actual se considera vencido

## Ejemplos de Uso

### Ejemplo 1: Año lectivo comienza en Marzo
```env
DEBT_START_MONTH=3
```

**Escenarios:**
- Alumno creado el 15 de enero de 2024
  - ✅ Deuda comienza a contarse desde marzo 2024
  - ❌ NO se genera deuda por enero ni febrero 2024

- Alumno creado el 20 de abril de 2024
  - ✅ Deuda comienza desde abril 2024
  - (Se creó después del mes de inicio)

- Alumno creado el 10 de diciembre de 2023
  - ✅ Deuda comienza desde marzo 2024
  - ❌ NO se genera deuda por diciembre 2023, ni enero o febrero 2024

### Ejemplo 2: Año lectivo comienza en Septiembre (sistema tipo universitario)
```env
DEBT_START_MONTH=9
```

**Escenarios:**
- Alumno creado el 5 de julio de 2024
  - ✅ Deuda comienza desde septiembre 2024
  - ❌ NO se genera deuda por julio ni agosto 2024

- Alumno creado el 15 de octubre de 2024
  - ✅ Deuda comienza desde octubre 2024
  - (Se creó después del mes de inicio)

### Ejemplo 3: Deuda desde el mes de creación (comportamiento clásico)
```env
DEBT_START_MONTH=1
```

Con esta configuración, la deuda se calcula desde el mes de creación del alumno, independientemente del mes (comportamiento similar al anterior).

## Lógica Implementada

### Flujo de Cálculo

1. **Obtener fecha de creación del alumno**
   - Ejemplo: 15 de enero de 2024

2. **Leer configuración `DEBT_START_MONTH`**
   - Ejemplo: 3 (marzo)

3. **Calcular fecha efectiva de inicio de deuda:**
   
   ```
   SI (mes_creación < mes_inicio_académico)
      ENTONCES inicio_deuda = mes_inicio_académico del mismo año
   SI NO
      inicio_deuda = mes_creación
   ```

4. **Calcular deuda desde la fecha efectiva hasta hoy**

### Código Relevante

El cálculo se realiza en el método `calculateDebtFromCreationUsingCurrentMonthly()` del modelo `Student`:

```php
// Obtener configuración
$debtStartMonth = config('business.debt_start_month', 3);

// Calcular fecha efectiva
$start = $this->calculateEffectiveDebtStartDate($creationDate, $debtStartMonth);
```

El método helper `calculateEffectiveDebtStartDate()` implementa la lógica:

```php
protected function calculateEffectiveDebtStartDate(Carbon $creationDate, int $debtStartMonth): Carbon
{
    $creationYear = $creationDate->year;
    
    // Fecha del mes de inicio en el año de creación
    $academicStartInCreationYear = Carbon::create($creationYear, $debtStartMonth, 1)->startOfMonth();
    
    // Si el alumno se creó antes del mes de inicio
    if ($creationDate->lt($academicStartInCreationYear)) {
        return $academicStartInCreationYear;
    }
    
    // Si se creó después, usar fecha de creación
    return $creationDate->copy();
}
```

## Archivos Modificados

1. **config/business.php** (NUEVO)
   - Archivo de configuración para lógica de negocio
   - Define valores por defecto

2. **.env.example**
   - Agregadas variables de configuración
   - Documentación de valores

3. **app/Models/Student.php**
   - Modificado `calculateDebtFromCreationUsingCurrentMonthly()`
   - Agregado método `calculateEffectiveDebtStartDate()`
   - Usa `config('business.debt_start_month')` y `config('business.debt_due_day')`

## Impacto en el Sistema

### Funcionalidades Afectadas

- ✅ Cálculo de deuda total del alumno
- ✅ Periodos impagos mostrados en la vista
- ✅ Lista de periodos seleccionables para pago
- ✅ Reportes de deudores

### Funcionalidades NO Afectadas

- ❌ Pagos realizados (se mantienen todos los registros históricos)
- ❌ Inscripciones a clases (no afecta la relación alumno-materia)
- ❌ Precios de cuotas (se mantiene el sistema de precios actual)

## Casos de Uso Comunes

### Natación (Año calendario)
```env
DEBT_START_MONTH=3  # Marzo
```
El año lectivo de natación suele comenzar en marzo/abril en muchos lugares.

### Sistema Universitario
```env
DEBT_START_MONTH=9  # Septiembre
```
Las universidades típicamente inician el ciclo académico en septiembre.

### Gimnasio (Todo el año)
```env
DEBT_START_MONTH=1  # Enero
```
Los gimnasios operan todo el año, se cobra desde el mes de inscripción.

### Escuela (Febrero/Marzo según país)
```env
# Argentina, Chile, Uruguay:
DEBT_START_MONTH=3  # Marzo

# México, España:
DEBT_START_MONTH=9  # Septiembre
```

## Testing Manual

### Caso de Prueba 1: Alumno antes del mes de inicio
```
1. Configurar DEBT_START_MONTH=3
2. Crear alumno con fecha 2024-01-15
3. Verificar que la deuda NO incluye enero ni febrero
4. Verificar que la deuda SÍ incluye marzo en adelante
```

### Caso de Prueba 2: Alumno después del mes de inicio
```
1. Configurar DEBT_START_MONTH=3
2. Crear alumno con fecha 2024-05-15
3. Verificar que la deuda comienza desde mayo
4. Verificar que NO se incluye marzo ni abril
```

### Caso de Prueba 3: Cambio de configuración
```
1. Tener alumnos existentes
2. Cambiar DEBT_START_MONTH de 3 a 9
3. Limpiar cache: php artisan config:clear
4. Verificar que el cálculo de deuda se actualiza automáticamente
```

## Comandos Útiles

### Limpiar cache de configuración
```bash
php artisan config:clear
```

### Ver configuración actual
```bash
php artisan tinker
>>> config('business.debt_start_month')
=> 3
```

### Probar cálculo de deuda
```bash
php artisan tinker
>>> $student = App\Models\Student::find(1)
>>> $debt = $student->calculateDebtFromCreationUsingCurrentMonthly()
>>> print_r($debt)
```

## Notas Importantes

1. **Cache de Configuración**: Después de cambiar valores en `.env`, ejecutar `php artisan config:clear`

2. **Valores por Defecto**: Si no se define `DEBT_START_MONTH`, el valor por defecto es 3 (marzo)

3. **Retroactividad**: Esta configuración afecta el cálculo en tiempo real. Los pagos existentes se respetan siempre.

4. **Períodos Futuros**: El sistema solo calcula deuda hasta el mes actual, independientemente de la configuración.

5. **Consistencia**: Una vez definido el mes de inicio, se recomienda no cambiarlo durante el ciclo académico para mantener consistencia en los reportes.

## Soporte

Para problemas o preguntas relacionadas con esta funcionalidad, revisar:
- `app/Models/Student.php` - Método `calculateDebtFromCreationUsingCurrentMonthly()`
- `config/business.php` - Archivo de configuración
- Este documento de documentación

## Changelog

### Versión 1.0 (2026-03-03)
- ✅ Implementación inicial
- ✅ Soporte para `DEBT_START_MONTH`
- ✅ Soporte para `DEBT_DUE_DAY`
- ✅ Método helper `calculateEffectiveDebtStartDate()`
- ✅ Documentación completa
