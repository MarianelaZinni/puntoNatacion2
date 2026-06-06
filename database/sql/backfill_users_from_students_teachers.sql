-- Backfill de usuarios desde alumnos y profesores.
-- Preparado para ejecutar en phpMyAdmin (MySQL/MariaDB).
-- Crea/actualiza usuarios con rol, vínculos y password basada en DNI.
-- Requiere función ENCRYPT habilitada para generar hash compatible con bcrypt.

START TRANSACTION;

-- 1) Alumnos -> usuarios rol alumno (si no existen por DNI)
INSERT INTO users (name, email, dni, password, role, teacher_id, email_verified_at, created_at, updated_at)
SELECT
    s.name,
    NULL,
    s.dni,
    ENCRYPT(
        s.dni,
        CONCAT('$2y$10$', SUBSTRING(SHA2(CONCAT('puntoNatacion2:', s.dni), 256), 1, 22))
    ) AS password_hash,
    'alumno',
    NULL,
    NULL,
    NOW(),
    NOW()
FROM students s
LEFT JOIN users u ON u.dni = s.dni
WHERE s.dni IS NOT NULL
  AND s.dni <> ''
  AND u.id IS NULL;

-- 2) Forzar rol alumno y desvincular teacher_id para usuarios que matchean alumnos por DNI
UPDATE users u
INNER JOIN students s ON s.dni = u.dni
SET
    u.role = 'alumno',
    u.teacher_id = NULL,
    u.updated_at = NOW();

-- 3) Vincular user_students para cada alumno
INSERT IGNORE INTO user_students (user_id, student_id, created_at, updated_at)
SELECT
    u.id AS user_id,
    s.id AS student_id,
    NOW(),
    NOW()
FROM students s
INNER JOIN users u ON u.dni = s.dni
WHERE s.dni IS NOT NULL
  AND s.dni <> '';

-- 4) Profesores -> usuarios rol profesor
--    Prioridad: usuario ya vinculado por teacher_id; fallback por DNI.
UPDATE users u
INNER JOIN teachers t ON t.id = u.teacher_id
SET
    u.role = 'profesor',
    u.teacher_id = t.id,
    u.updated_at = NOW();

UPDATE users u
INNER JOIN teachers t ON t.dni = u.dni
SET
    u.role = 'profesor',
    u.teacher_id = t.id,
    u.updated_at = NOW()
WHERE t.dni IS NOT NULL
  AND t.dni <> '';

INSERT INTO users (name, email, dni, password, role, teacher_id, email_verified_at, created_at, updated_at)
SELECT
    t.name,
    NULLIF(t.email, ''),
    NULLIF(t.dni, ''),
    ENCRYPT(
        t.dni,
        CONCAT('$2y$10$', SUBSTRING(SHA2(CONCAT('puntoNatacion2:teacher:', t.dni), 256), 1, 22))
    ) AS password_hash,
    'profesor',
    t.id,
    CASE WHEN t.email IS NULL OR t.email = '' THEN NULL ELSE NOW() END,
    NOW(),
    NOW()
FROM teachers t
LEFT JOIN users u_by_teacher ON u_by_teacher.teacher_id = t.id
LEFT JOIN users u_by_dni ON u_by_dni.dni = t.dni AND t.dni IS NOT NULL AND t.dni <> ''
WHERE u_by_teacher.id IS NULL
  AND u_by_dni.id IS NULL
  AND t.dni IS NOT NULL
  AND t.dni <> '';

COMMIT;

