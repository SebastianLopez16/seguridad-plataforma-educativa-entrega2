# Plataforma Educativa - Segundo Avance

Este es el segundo avance del proyecto **seguridad-plataforma-educativa**. Incluye:
- Login con 2FA (simulado).
- Roles: estudiante, profesor, invitado.
- Almacenamiento de intentos y bloqueo temporal.
- Diseño responsivo y moderno.

## Instalación (XAMPP)

1. Copia la carpeta `seguridad-plataforma-educativa-v2` dentro de `htdocs`.
2. Crea la base de datos y tablas (MySQL). Usa este script SQL:

```sql
CREATE DATABASE plataforma_educativa_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plataforma_educativa_v2;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  role ENUM('estudiante','profesor','invitado') NOT NULL DEFAULT 'estudiante',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE two_factor (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  code VARCHAR(10) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  success TINYINT(1) NOT NULL,
  type VARCHAR(20) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE user_lock (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample users (password: pass123)
INSERT INTO users (username, password_hash, email, role)
VALUES
('estudiante1', '{PASSWORD_HASH_PLACEHOLDER}', 'est1@example.com', 'estudiante'),
('profesor1', '{PASSWORD_HASH_PLACEHOLDER}', 'prof1@example.com', 'profesor'),
('invitado1', '{PASSWORD_HASH_PLACEHOLDER}', 'inv1@example.com', 'invitado');

-- To generate the password hash for 'pass123' in PHP:
-- <?php echo password_hash('pass123', PASSWORD_DEFAULT); ?>
```

3. Después de crear la base de datos, actualiza `includes/db.php` si tus credenciales son diferentes.

## Notas importantes
- El envío del código 2FA está simulado: el código se guarda en la tabla `two_factor` y se muestra en la pantalla de verificación para pruebas.
- En producción reemplaza el envío simulado por un proveedor de correo/SMS y usa HTTPS.
- Maneja sesiones y cookies con más rigor en producción.

## Archivos incluidos
- `index.php` — formulario de login.
- `verify.php` — verificación 2FA.
- `includes/db.php`, `includes/functions.php`
- `dashboard/*` — páginas por rol.
- `assets/*` — CSS y JS.

¡Listo! Copia la carpeta al `htdocs` de XAMPP y sigue el README para crear la base de datos y usuarios de prueba.
