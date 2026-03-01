# Sistema de Notas de Profesores para Alumnos

## Resumen

Este documento describe la implementación completa del sistema que permite a los profesores agregar notas/comentarios a los alumnos de sus clases.

## Características Principales

- Los profesores pueden agregar notas sobre alumnos de sus clases
- Un alumno puede tener múltiples notas de la misma clase (profesor) o de diferentes clases
- Las notas pueden ser escritas tanto por el profesor titular como por el suplente
- Las notas se pueden ver, editar y eliminar
- Solo el profesor que creó la nota puede editarla o eliminarla (si sigue asignado a la clase)

## Archivos Creados

### 1. Migration: `database/migrations/2026_03_01_033900_create_student_notes_table.php`

Crea la tabla `student_notes` con la siguiente estructura:

```php
- id (primary key)
- student_id (FK a students, cascade on delete)
- teacher_id (FK a teachers, cascade on delete)
- subject_id (FK a subjects, cascade on delete)
- note (text, máximo 2000 caracteres)
- created_at, updated_at (timestamps)
- Índice: (student_id, subject_id, created_at)
```

### 2. Modelo: `app/Models/StudentNote.php`

**Relaciones:**
- `student()` - belongsTo Student
- `teacher()` - belongsTo Teacher
- `subject()` - belongsTo Subject

**Métodos:**
- `canBeEditedBy(Teacher $teacher): bool` - Verifica si el profesor puede editar la nota
- `logNote(Student $student, Teacher $teacher, Subject $subject, string $note): self` - Método estático para crear notas

### 3. Controlador: `app/Http/Controllers/StudentNoteController.php`

**Métodos:**

#### `store(Request $request, Student $student)`
- Crea una nueva nota para un alumno
- Validaciones:
  - El profesor debe ser titular o suplente de la clase
  - El alumno debe estar inscrito en la clase
  - La nota es obligatoria (máx. 2000 caracteres)
- Retorna JSON con la nota creada

#### `update(Request $request, StudentNote $note)`
- Actualiza una nota existente
- Validaciones:
  - El profesor debe ser el autor de la nota
  - El profesor debe seguir asignado a la clase
  - La nota no puede estar vacía (máx. 2000 caracteres)
- Retorna JSON con confirmación

#### `destroy(Request $request, StudentNote $note)`
- Elimina una nota
- Validaciones:
  - El profesor debe ser el autor de la nota
  - El profesor debe seguir asignado a la clase
- Retorna JSON con confirmación

## Archivos Modificados

### 1. `app/Models/Student.php`

**Agregado:**
```php
public function notes()
{
    return $this->hasMany(\App\Models\StudentNote::class)->orderBy('created_at', 'desc');
}
```

### 2. `app/Models/Teacher.php`

**Agregado:**
```php
public function studentNotes(): HasMany
{
    return $this->hasMany(StudentNote::class);
}
```

### 3. `app/Models/Subject.php`

**Agregado:**
```php
public function studentNotes()
{
    return $this->hasMany(\App\Models\StudentNote::class)->orderBy('created_at', 'desc');
}
```

### 4. `app/Http/Controllers/TeacherController.php`

**Actualizado el método `show()`:**
```php
// Eager load subjects with subjectType, students, student notes, and comments
$teacher->load([
    'subjects.subjectType',
    'subjects.students',
    'subjects.studentNotes.teacher',
    'subjects.studentNotes.student',
    'subjects.comments.teacher',
    'subjectsAsSubstitute.subjectType',
    'subjectsAsSubstitute.students',
    'subjectsAsSubstitute.studentNotes.teacher',
    'subjectsAsSubstitute.studentNotes.student',
    'subjectsAsSubstitute.comments.teacher'
]);
```

### 5. `routes/web.php`

**Rutas agregadas:**
```php
// Rutas para notas de alumnos (Student Notes)
Route::post('/students/{student}/notes', [\App\Http\Controllers\StudentNoteController::class, 'store'])->name('student-notes.store');
Route::put('/notes/{note}', [\App\Http\Controllers\StudentNoteController::class, 'update'])->name('student-notes.update');
Route::delete('/notes/{note}', [\App\Http\Controllers\StudentNoteController::class, 'destroy'])->name('student-notes.destroy');
```

### 6. `resources/views/teachers/show.blade.php`

**Secciones agregadas:**

1. **Sección de notas de alumnos (titular)** - Después de los comentarios de clase
2. **Sección de notas de alumnos (suplente)** - Después de los comentarios de clase como suplente

Cada sección incluye:
- Listado de alumnos inscritos en la clase
- Formulario para agregar nota por alumno
- Listado de notas existentes con opciones de editar/eliminar
- Validación visual (autor, fecha relativa)

**Funciones JavaScript agregadas:**
- `addStudentNote(studentId, subjectId, noteText, form)` - Agrega una nueva nota
- `showStudentNoteEditForm(noteId)` - Muestra el formulario de edición
- `cancelStudentNoteEdit(noteId)` - Cancela la edición
- `updateStudentNote(noteId, noteText)` - Actualiza una nota existente
- `deleteStudentNote(noteId)` - Elimina una nota con confirmación

## Reglas de Negocio

1. **Autorización para agregar notas:**
   - El profesor debe ser titular O suplente de la clase
   - El alumno debe estar inscrito en la clase

2. **Autorización para editar/eliminar notas:**
   - Solo el autor de la nota puede editarla o eliminarla
   - El profesor debe seguir asignado a la clase (titular o suplente)

3. **Persistencia:**
   - Las notas persisten incluso si el profesor es removido de la clase
   - Si el profesor es removido, no puede editar/eliminar sus notas antiguas

4. **Organización:**
   - Las notas se muestran por alumno, dentro de cada clase
   - Ordenadas por fecha de creación (más reciente primero)

## Instrucciones de Instalación

1. **Ejecutar la migración:**
```bash
php artisan migrate
```

2. **Limpiar cachés (opcional):**
```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

## Uso del Sistema

### Desde la vista del profesor (`/teachers/{id}`)

1. **Agregar una nota:**
   - Navegar a la clase donde es titular o suplente
   - La tabla muestra los alumnos inscritos
   - Cada alumno tiene un formulario "Agregar una nota sobre este alumno..."
   - Escribir la nota (máx. 2000 caracteres)
   - Click en "Agregar Nota"
   - Confirmación con SweetAlert2
   - La página se recarga y muestra la nueva nota

2. **Editar una nota:**
   - Click en el ícono de lápiz junto a la nota
   - Aparece un formulario inline
   - Modificar el texto
   - Click en "Guardar" o "Cancelar"
   - Confirmación con SweetAlert2

3. **Eliminar una nota:**
   - Click en el ícono de papelera junto a la nota
   - Confirmación con SweetAlert2
   - La nota se elimina de la base de datos

## Ejemplos de Uso

### Ejemplo 1: Profesor titular agrega nota
```
Profesor: Juan Pérez (titular de "Natación Nivel 1 - Lunes 10:00")
Alumno: María García
Nota: "Excelente progreso en estilo libre. Necesita practicar más la respiración."
```

### Ejemplo 2: Profesor suplente agrega nota
```
Profesor: Ana López (suplente de "Natación Nivel 1 - Lunes 10:00")
Alumno: María García
Nota: "Clase del 01/03 - María demostró buen dominio del estilo espalda."
```

### Ejemplo 3: Alumno con múltiples notas
```
Alumno: Pedro Rodríguez
- Clase: Natación Nivel 1 (Profesor: Juan Pérez)
  - Nota 1: "Buen primer día de clase."
  - Nota 2: "Mejoró considerablemente la técnica de brazada."
- Clase: Natación Nivel 2 (Profesor: Ana López)
  - Nota 1: "Promovido de Nivel 1. Muy motivado."
```

## Características Técnicas

- **AJAX:** Todas las operaciones usan AJAX para evitar recargas completas
- **SweetAlert2:** Confirmaciones y notificaciones visuales
- **Validación:** Frontend y backend
- **Autorización:** Verificación en cada operación
- **Responsivo:** Diseño adaptable a diferentes tamaños de pantalla
- **Dark Mode:** Soporte para modo oscuro

## Diferencias con Comentarios de Clase

| Característica | Comentarios de Clase | Notas de Alumnos |
|----------------|---------------------|------------------|
| Sobre qué | La clase en general | Alumnos específicos |
| Límite | 1000 caracteres | 2000 caracteres |
| Organización | Por clase | Por alumno, por clase |
| Ejemplo | "Cambio de horario la próxima semana" | "María mejoró su técnica" |

## Consultas SQL Útiles

### Ver todas las notas de un alumno
```sql
SELECT sn.*, t.name as teacher_name, s.day, st.name as subject_type
FROM student_notes sn
JOIN teachers t ON sn.teacher_id = t.id
JOIN subjects s ON sn.subject_id = s.id
JOIN subject_types st ON s.subject_type_id = st.id
WHERE sn.student_id = ?
ORDER BY sn.created_at DESC;
```

### Ver todas las notas de un profesor
```sql
SELECT sn.*, st.name as student_name, s.day, sut.name as subject_type
FROM student_notes sn
JOIN students st ON sn.student_id = st.id
JOIN subjects s ON sn.subject_id = s.id
JOIN subject_types sut ON s.subject_type_id = sut.id
WHERE sn.teacher_id = ?
ORDER BY sn.created_at DESC;
```

### Ver todas las notas de una clase
```sql
SELECT sn.*, st.name as student_name, t.name as teacher_name
FROM student_notes sn
JOIN students st ON sn.student_id = st.id
JOIN teachers t ON sn.teacher_id = t.id
WHERE sn.subject_id = ?
ORDER BY st.name, sn.created_at DESC;
```

## Pruebas Recomendadas

1. ✅ Ver página de detalle de profesor con clases asignadas
2. ✅ Agregar nota como profesor titular
3. ✅ Agregar nota como profesor suplente
4. ✅ Intentar agregar nota como profesor no asignado (debe fallar)
5. ✅ Editar nota propia
6. ✅ Eliminar nota propia
7. ✅ Intentar editar/eliminar nota de otro profesor (debe fallar)
8. ✅ Remover profesor de clase y verificar que no puede editar sus notas antiguas
9. ✅ Verificar que las notas persisten cuando se remueve el profesor
10. ✅ Verificar que un alumno puede tener notas de múltiples profesores
11. ✅ Verificar que un alumno puede tener múltiples notas del mismo profesor
12. ✅ Verificar límite de 2000 caracteres

## Posibles Extensiones Futuras

1. **Filtros:** Filtrar notas por fecha, profesor, tipo
2. **Búsqueda:** Buscar notas por contenido
3. **Exportación:** Exportar notas a PDF o Excel
4. **Notificaciones:** Notificar a los padres cuando se agrega una nota
5. **Categorías:** Categorizar notas (comportamiento, progreso, asistencia, etc.)
6. **Historial:** Ver historial de cambios en las notas
7. **Archivos adjuntos:** Permitir adjuntar imágenes o documentos
8. **Vista del alumno:** Que los alumnos puedan ver sus propias notas

## Soporte

Para preguntas o problemas con esta funcionalidad, consultar la documentación de Laravel o contactar al desarrollador del sistema.

---

**Fecha de implementación:** Marzo 2026  
**Versión:** 1.0.0  
**Estado:** Completado y probado
