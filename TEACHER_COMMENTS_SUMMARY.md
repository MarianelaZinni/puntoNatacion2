# Teacher Comments System - Implementation Summary

## Overview
Sistema de comentarios para que los profesores puedan agregar, editar y eliminar comentarios en las clases que dictan.

## Business Rules
- Solo el profesor titular o suplente de una clase puede agregar comentarios
- Una clase puede tener múltiples comentarios
- Los comentarios se pueden ver, editar y eliminar
- Solo el autor del comentario puede editarlo o eliminarlo
- El profesor debe seguir asignado a la clase para poder editar/eliminar sus comentarios

## Files Created

### 1. Migration: `database/migrations/2026_02_28_165300_create_subject_comments_table.php`
```php
Schema::create('subject_comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->text('comment');
    $table->timestamps();
    
    $table->index(['subject_id', 'created_at']);
});
```

### 2. Model: `app/Models/SubjectComment.php`
- Relationships: subject(), teacher()
- Method: canBeEditedBy(Teacher $teacher) - validates edit permission
- Fillable: subject_id, teacher_id, comment

### 3. Controller: `app/Http/Controllers/SubjectCommentController.php`
- store() - Create new comment
- update() - Update existing comment
- destroy() - Delete comment
- All methods include authorization checks

## Files Modified

### 1. `app/Models/Subject.php`
Added relationship:
```php
public function comments()
{
    return $this->hasMany(\App\Models\SubjectComment::class)->orderBy('created_at', 'desc');
}
```

### 2. `app/Models/Teacher.php`
Added relationship and helper method:
```php
public function subjectComments(): HasMany
{
    return $this->hasMany(SubjectComment::class);
}

public function canCommentOnSubject(Subject $subject): bool
{
    return $this->id === $subject->teacher_id || $this->id === $subject->substitute_teacher_id;
}
```

### 3. `app/Http/Controllers/TeacherController.php`
Updated eager loading in show() method:
```php
$teacher->load([
    'subjects.subjectType',
    'subjects.students',
    'subjects.comments.teacher',
    'subjectsAsSubstitute.subjectType',
    'subjectsAsSubstitute.students',
    'subjectsAsSubstitute.comments.teacher'
]);
```

### 4. `routes/web.php`
Added routes:
```php
Route::post('/subjects/{subject}/comments', [SubjectCommentController::class, 'store'])->name('subject-comments.store');
Route::put('/comments/{comment}', [SubjectCommentController::class, 'update'])->name('subject-comments.update');
Route::delete('/comments/{comment}', [SubjectCommentController::class, 'destroy'])->name('subject-comments.destroy');
```

### 5. `resources/views/teachers/show.blade.php`
Added:
- Comment display sections for each class (titular and substitute)
- Add comment forms
- Edit comment inline forms
- Delete comment buttons
- JavaScript functions: addComment(), showEditForm(), cancelEdit(), updateComment(), deleteComment()

## UI Features

### Add Comment
- Textarea with 1000 character limit
- Character counter
- Submit button
- Only visible if teacher can comment on the class

### Display Comments
- Author name and timestamp (relative time)
- Comment text
- Edit button (pencil icon)
- Delete button (trash icon)
- Only shows edit/delete for comment author who is still assigned

### Edit Comment
- Inline editing (replaces comment text)
- Textarea with current comment text
- Save and Cancel buttons
- Updates via AJAX

### Delete Comment
- SweetAlert2 confirmation dialog
- Permanent deletion via AJAX
- Success notification

## Authorization Flow

```
Can Add Comment:
  teacher->canCommentOnSubject(subject)
    ↓
  teacher_id == subject->teacher_id 
  OR 
  teacher_id == subject->substitute_teacher_id

Can Edit/Delete Comment:
  comment->canBeEditedBy(teacher)
    ↓
  comment->teacher_id == teacher->id
  AND
  teacher->canCommentOnSubject(comment->subject)
```

## API Endpoints

### POST /subjects/{subject}/comments
Request:
```json
{
  "comment": "Texto del comentario",
  "teacher_id": 1
}
```
Response:
```json
{
  "success": true,
  "message": "Comentario agregado exitosamente.",
  "comment": {
    "id": 5,
    "comment": "Texto del comentario",
    "teacher_name": "Juan Pérez",
    "created_at": "hace 5 minutos"
  }
}
```

### PUT /comments/{comment}
Request:
```json
{
  "comment": "Texto actualizado",
  "teacher_id": 1
}
```
Response:
```json
{
  "success": true,
  "message": "Comentario actualizado exitosamente.",
  "comment": {
    "id": 5,
    "comment": "Texto actualizado",
    "updated_at": "hace unos segundos"
  }
}
```

### DELETE /comments/{comment}
Request:
```json
{
  "teacher_id": 1
}
```
Response:
```json
{
  "success": true,
  "message": "Comentario eliminado exitosamente."
}
```

## Database Schema

```
subject_comments
├── id (bigint, primary key)
├── subject_id (bigint, foreign key → subjects.id, cascade on delete)
├── teacher_id (bigint, foreign key → teachers.id, cascade on delete)
├── comment (text)
├── created_at (timestamp)
└── updated_at (timestamp)

Indexes:
- PRIMARY KEY (id)
- INDEX (subject_id, created_at)
- FOREIGN KEY (subject_id) REFERENCES subjects(id)
- FOREIGN KEY (teacher_id) REFERENCES teachers(id)
```

## Migration Command

```bash
php artisan migrate
```

## Testing Scenarios

1. **Add comment as titular teacher** ✓
2. **Add comment as substitute teacher** ✓
3. **Try to add comment as non-assigned teacher** → Should fail with 403
4. **Edit own comment** ✓
5. **Delete own comment** ✓
6. **Try to edit another teacher's comment** → Should fail with 403
7. **Remove teacher from class** → Can't edit/delete old comments
8. **View comments from multiple teachers** ✓
9. **Add multiple comments to same class** ✓
10. **View comments in both titular and substitute sections** ✓

## UI Location

Comments are displayed in the teacher detail page (`/teachers/{id}`):
- Under "Clases como Titular" table
- Under "Clases como Suplente" table
- Each class has an expandable comments section
- Comments appear in a collapsible row below the class information

## Technologies Used

- Laravel 11 (Backend)
- Blade Templates (Views)
- Tailwind CSS (Styling)
- SweetAlert2 (Confirmations)
- Fetch API (AJAX requests)
- Flux Icons (UI icons)

## Notes

- Comments use relative timestamps ("hace 5 minutos")
- Maximum 1000 characters per comment
- AJAX-based operations with page reload on success (2 second delay)
- Comments persist even if teacher is removed from class
- Cascade delete: If class or teacher is deleted, comments are deleted too
- Dark mode support included in all UI components
