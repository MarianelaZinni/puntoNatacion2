# Resumen: Configuración del Mes de Inicio de Deuda

## ¿Qué se implementó?

Se agregó la posibilidad de configurar un mes de inicio para el cálculo de deuda, de manera que no se genere deuda antes de ese mes en cada año académico.

## ¿Cómo usarlo?

### 1. Editar el archivo `.env`

Agregar estas líneas (o modificar si ya existen):

```env
DEBT_START_MONTH=3
DEBT_DUE_DAY=10
```

### 2. Valores comunes:

- **DEBT_START_MONTH=3** → Marzo (ideal para natación, por ejemplo)
- **DEBT_START_MONTH=9** → Septiembre (ideal para universidades)
- **DEBT_START_MONTH=1** → Enero (cobra desde el mes de inscripción)

### 3. Limpiar cache

Después de cambiar `.env`, ejecutar:

```bash
php artisan config:clear
```

## Ejemplos Prácticos

### Ejemplo 1: Natación que empieza en Marzo
```env
DEBT_START_MONTH=3
```

**Situación:** Juan se inscribe el 15 de enero de 2024
- ❌ NO se genera deuda por enero
- ❌ NO se genera deuda por febrero
- ✅ SÍ se genera deuda desde marzo 2024 en adelante

### Ejemplo 2: María se inscribe en abril
```env
DEBT_START_MONTH=3
```

**Situación:** María se inscribe el 10 de abril de 2024
- ❌ NO se genera deuda por marzo (ya pasó)
- ✅ SÍ se genera deuda desde abril 2024 en adelante

## Archivos Modificados

1. **config/business.php** (NUEVO)
   - Configuración centralizada

2. **.env.example**
   - Plantilla con las nuevas variables

3. **app/Models/Student.php**
   - Lógica de cálculo actualizada

4. **DEBT_START_MONTH_CONFIGURATION.md** (NUEVO)
   - Documentación completa (en inglés)

## Verificación

Para probar que funciona:

```bash
php artisan tinker
```

Luego en tinker:
```php
// Ver configuración actual
config('business.debt_start_month')

// Probar con un alumno
$student = App\Models\Student::find(1)
$debt = $student->calculateDebtFromCreationUsingCurrentMonthly()
print_r($debt)
```

## ¿Afecta a los datos existentes?

**NO.** Esta configuración solo cambia cómo se *calcula* la deuda. Todos los pagos históricos se mantienen intactos.

## Valores por Defecto

Si no se configura nada, el sistema usa:
- **DEBT_START_MONTH = 3** (Marzo)
- **DEBT_DUE_DAY = 10** (día 10 del mes)

## Soporte

Para más información detallada, ver el archivo:
`DEBT_START_MONTH_CONFIGURATION.md`
