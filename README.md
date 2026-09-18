# BOT COOMEVA — MVP

Proyecto inicial del asistente virtual de Coomeva.

## Flujo

1. Usuario escribe al número principal de WhatsApp.
2. El bot solicita la cédula.
3. Consulta `afiliados`.
4. Si encuentra coincidencia, saluda con nombres y apellidos y muestra el menú de afiliado.
5. Si no encuentra coincidencia, muestra el menú comercial para no afiliados.
6. Cuando el usuario solicita un asesor, se consulta `asesores` y se entrega el enlace del asesor disponible.

## Importante sobre la base real

El archivo `pro1.xlsx` entregado por el cliente contiene datos personales. No se incluye dentro de este ZIP.

La tabla `afiliados` ya está preparada para recibir los datos necesarios. Para cargar los datos reales, primero se debe validar con Coomeva qué campos están autorizados para el uso del bot y hacer la importación en un entorno protegido.

## Instalación local

1. Instalar XAMPP con Apache, PHP y MySQL.
2. Copiar esta carpeta a `htdocs`.
3. Crear/importar `database/coomeva.sql` desde phpMyAdmin.
4. Copiar `config.example.php` como `config.local.php` y configurar las credenciales locales.
5. Abrir `http://localhost/BOT-COOMEVA/`.

Para una base ya instalada, ejecutar también `database/migrations/001_security.sql` y `database/migrations/002_consentimiento_web.sql` antes de desplegar este código.

## Seguridad y despliegue

Esta versión incorpora cabeceras de seguridad, cookies de sesión `HttpOnly`/`SameSite`, tokens CSRF para toda acción que cambia estado, rotación de sesión al validar identidad y un límite de cinco intentos de identificación por 15 minutos. El flujo web solicita los términos y la autorización de tratamiento de datos antes de pedir la cédula y conserva una prueba seudonimizada de la aceptación (versión, fecha, sesión e IP protegidas con hash).

Los textos de `terminos.php` y `politica_privacidad.php` son provisionales. Deben ser sustituidos y aprobados por el responsable de protección de datos y el equipo jurídico de Coomeva antes de producción.

- `config.local.php` está ignorado por Git. En producción se prefieren las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `RATE_LIMIT_PEPPER`, `SESSION_COOKIE_SECURE`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_APP_SECRET` y `WHATSAPP_GRAPH_API_VERSION`.
- En producción usar una cuenta MySQL exclusiva con permisos mínimos; el proyecto rechaza el usuario `root`, una contraseña vacía y cookies no seguras.
- El servidor debe usar HTTPS. Configurar `SESSION_COOKIE_SECURE=true` y un `RATE_LIMIT_PEPPER` aleatorio de al menos 32 caracteres.
- El `.htaccess` bloquea descargas de archivos de datos y de configuración. Verificar que Apache tenga `AllowOverride All` o migrar estas reglas a la configuración del virtual host.
- Los archivos de importación que estaban en el proyecto local ya se movieron a `C:\xampp\private\BOT-COOMEVA-importacion`, fuera de `htdocs`. No deben incluirse en el paquete de entrega, repositorios ni entornos de prueba.

## WhatsApp

`whatsapp/webhook.php` y `whatsapp/enviar_mensaje.php` están preparados como base para WhatsApp Cloud API. El webhook rechaza peticiones cuya firma `X-Hub-Signature-256` no coincida con `WHATSAPP_APP_SECRET`, registra los IDs de mensajes entrantes para no procesar reintentos de Meta dos veces y conserva el estado del menú por teléfono.

Los comandos disponibles después de identificarse son el número de la opción, `MENU` para mostrar las opciones y `CERRAR` para terminar una atención con asesor y liberar su disponibilidad. La asignación de asesor se ejecuta de forma transaccional para web y WhatsApp; una segunda solicitud del mismo teléfono conserva el asesor ya reservado.

Antes de conectarlo al número real de Coomeva se necesitan:
- proveedor/API de WhatsApp confirmado;
- número de WhatsApp Business;
- credenciales;
- URL HTTPS pública para el webhook;
- versión de Graph API confirmada para la app de Meta;
- reglas de atención y plantillas aprobadas por el cliente.

Configurar `WHATSAPP_GRAPH_API_VERSION` (por ejemplo, el valor vigente confirmado en la consola de Meta) antes de activar envíos.

## Seguridad

No subir credenciales reales al repositorio. Usar variables de entorno o un archivo de configuración fuera del control de versiones.

No usar la base real en pruebas públicas. Crear primero datos ficticios.

## Próximo desarrollo

- Completar los menús de afiliados y no afiliados.
- Reemplazar las respuestas provisionales del menú de WhatsApp por contenido oficial aprobado por Coomeva.
- Definir la política de expiración automática y el proceso operativo para liberar asesores que no cierren una atención.
- Registrar conversaciones y mensajes.
- Probar webhook con un número de prueba.


## Estado de esta versión
Esta versión conserva el proyecto anterior completo, incluyendo:
- Consulta de afiliados por cédula.
- Flujo de afiliado.
- Flujo de no afiliado.
- Planes, servicios, citas, clínicas y especialistas.
- Estructura inicial de WhatsApp.
- Base `coomeva`.
- Esquema preparado para importar afiliados autorizados en un entorno protegido.

Además incorpora:
- Asignación de asesor con bloqueo transaccional MySQL.
- Registro de conversación.
- Liberación de asesor.
- Página `prueba_asesores.php` para validar concurrencia.

Los archivos de importación con datos personales están fuera del proyecto web. No deben publicarse ni subirse a GitHub.
