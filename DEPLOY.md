# Deploy en cPanel — Enertecs Laravel

## Contexto: dominio primario en cPanel

El dominio primario (`enertecs.cl`) en cPanel apunta fijamente a `public_html/` y no permite cambiar el document root por interfaz gráfica. Por eso se usa la siguiente estructura:

```
/home/{usuario}/
├── public_html/          ← document root de enertecs.cl
│   ├── index.php         ← entry point adaptado (ver deploy/public_html/)
│   ├── .htaccess         ← copiado de enertecs-laravel/public/.htaccess
│   ├── build/            ← symlink → ../enertecs-laravel/public/build/
│   └── storage/          ← symlink → ../enertecs-laravel/storage/app/public/
└── enertecs-laravel/     ← proyecto Laravel (fuera de public_html)
```

---

## Requisitos previos (local)

Antes de subir al servidor, ejecutar en la máquina de desarrollo:

```bash
npm run build          # compila Tailwind CSS + JS a public/build/
composer install --no-dev --optimize-autoloader
```

---

## Pasos de deploy (en orden)

### 1. Clonar el repositorio

En cPanel → **Git™ Version Control**: conectar el repositorio y hacer clone/pull a:
```
/home/{usuario}/enertecs-laravel/
```

### 2. Subir dependencias vía FTP

Subir a `/home/{usuario}/enertecs-laravel/`:
- `vendor/` — directorio completo de dependencias PHP
- `public/build/` — assets compilados (CSS + JS)

### 3. Configurar `public_html/`

En cPanel → **File Manager**, dentro de `public_html/`:

**a) Subir `index.php` adaptado**

Subir el archivo `deploy/public_html/index.php` (del repo) a `public_html/index.php`.  
Este archivo apunta el entry point al proyecto en `../enertecs-laravel/`.

**b) Subir `.htaccess`**

Copiar `enertecs-laravel/public/.htaccess` a `public_html/.htaccess`.

**c) Crear symlinks**

Desde cPanel → File Manager (o por SSH si está disponible):

```bash
# Symlink de assets compilados
ln -s /home/{usuario}/enertecs-laravel/public/build /home/{usuario}/public_html/build

# Symlink de storage público
ln -s /home/{usuario}/enertecs-laravel/storage/app/public /home/{usuario}/public_html/storage
```

> **Reemplaza `{usuario}`** con el nombre de usuario real de cPanel.

### 4. Crear el archivo `.env`

En cPanel → **File Manager**: crear `/home/{usuario}/enertecs-laravel/.env` basado en `.env.example`. Campos críticos:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://enertecs.cl

APP_KEY=              # generar con: php artisan key:generate --show
APP_MIGRATION_TOKEN=  # token secreto aleatorio

DB_HOST=localhost
DB_DATABASE=          # nombre de la BD en cPanel
DB_USERNAME=          # usuario de BD en cPanel
DB_PASSWORD=          # contraseña de BD en cPanel

ANTHROPIC_API_KEY=    # clave de API de Anthropic (extracción boletas)
VRM_TOKEN=            # token API Victron
VRM_SITE_ID=          # ID sitio Victron

MAIL_MAILER=smtp
MAIL_HOST=            # SMTP del hosting
MAIL_PORT=465
MAIL_USERNAME=        # correo SMTP
MAIL_PASSWORD=        # contraseña SMTP
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=    # correo remitente
```

### 5. Ejecutar migraciones

Abrir en el navegador:
```
https://enertecs.cl/run-migrations?token=TU_TOKEN
```
Donde `TU_TOKEN` es el valor de `APP_MIGRATION_TOKEN` en `.env`.

Los seeders se ejecutan automáticamente (servicios, proyectos, certificaciones, configuración inicial).

### 6. Cron Job para la cola

En cPanel → **Cron Jobs**, agregar:
```
* * * * * php /home/{usuario}/enertecs-laravel/artisan queue:work --stop-when-empty
```

### 7. SSL

En cPanel → **SSL/TLS Status**: activar Let's Encrypt para `enertecs.cl` (1 clic).

### 8. Crear usuario admin de Filament

Por SSH (si está disponible):
```bash
php /home/{usuario}/enertecs-laravel/artisan make:filament-user
```

Si no hay acceso SSH, crear el usuario directamente en la DB desde cPanel → **phpMyAdmin**.

### 9. Limpiar ruta de migraciones

Una vez confirmado el deploy exitoso, eliminar de `routes/web.php` la ruta `/run-migrations`, hacer commit y pull en el servidor.

---

## Actualizaciones posteriores (re-deploy)

```bash
# Local: compilar assets
npm run build

# Subir vía FTP:
# - public/build/   (si hubo cambios de CSS/JS)
# - vendor/         (solo si cambiaron dependencias)

# En el servidor: hacer pull desde cPanel → Git™ Version Control
```

No es necesario tocar `public_html/` en actualizaciones normales, a menos que cambie el `.htaccess`.
