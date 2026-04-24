# Deploy en cPanel — Enertecs Laravel

## Requisitos previos (local)

Antes de subir al servidor, ejecutar en la máquina de desarrollo:

```bash
npm run build          # compila Tailwind CSS + JS a public/build/
composer install --no-dev --optimize-autoloader
```

## Pasos de deploy (en orden)

1. **Git Version Control** — en cPanel → "Git™ Version Control": conectar el repositorio `enertecs-laravel` y hacer pull.

2. **Document Root** — en cPanel → "Domains": cambiar el document root de `enertecs.cl` a:
   ```
   /home/{usuario}/enertecs-laravel/public/
   ```

3. **Archivo `.env`** — en cPanel → "File Manager": crear `/home/{usuario}/enertecs-laravel/.env` con los valores reales basados en `.env.example`. Campos críticos:
   - `APP_KEY` — generar con `php artisan key:generate --show` y copiar aquí
   - `DB_*` — credenciales de la base de datos MySQL en cPanel
   - `ANTHROPIC_API_KEY` — clave de API de Anthropic
   - `VRM_TOKEN` y `VRM_SITE_ID` — token y sitio de Victron VRM
   - `MAIL_*` — configuración SMTP del hosting
   - `APP_MIGRATION_TOKEN` — token secreto aleatorio para ejecutar migraciones

4. **Subir vía FTP**:
   - `vendor/` — directorio completo de dependencias PHP
   - `public/build/` — assets compilados (CSS + JS)

5. **Ejecutar migraciones** — abrir en el navegador:
   ```
   https://enertecs.cl/run-migrations?token=TU_TOKEN
   ```
   Donde `TU_TOKEN` es el valor de `APP_MIGRATION_TOKEN` en `.env`.

6. **Cron Job** — en cPanel → "Cron Jobs": agregar el worker de colas:
   ```
   * * * * * php /home/{usuario}/enertecs-laravel/artisan queue:work --stop-when-empty
   ```

7. **Storage symlink** — en cPanel → "File Manager": crear symlink:
   - `public_html/storage` → `../storage/app/public`

8. **SSL** — en cPanel → "SSL/TLS Status": activar Let's Encrypt para `enertecs.cl` (1 clic).

9. **Eliminar ruta de migraciones** — una vez confirmado el deploy exitoso, eliminar de `routes/web.php` la ruta `/run-migrations` y hacer un nuevo push + pull en el servidor.

## Seeders (primer deploy)

```
https://enertecs.cl/run-migrations?token=TU_TOKEN
```

Los seeders se ejecutan automáticamente con las migraciones. Incluyen datos iniciales de servicios, proyectos, certificaciones y configuración.

## Usuario admin de Filament

Después del primer deploy, crear el usuario admin por SSH (si está disponible) o via tinker:

```bash
php artisan make:filament-user
```

Si no hay acceso SSH, crear el usuario desde un controlador temporal o directamente en la DB.
