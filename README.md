# Bitácora Inteligente para Técnicos

Plataforma web para empresas de servicios técnicos (clima, refrigeración, eléctrico, mantenimiento) que permite documentar un trabajo con la mínima fricción: **el técnico habla y la app lo convierte en un reporte profesional**.

> Voz → Transcripción → Información estructurada por IA → Reporte corregible → PDF → Compartir

---

## Problema que resuelve

Tras un servicio, el técnico no documenta el trabajo porque escribir reportes consume tiempo y la información queda dispersa en WhatsApp, notas, fotos, papel o memoria. La empresa pierde trazabilidad, no puede respaldar lo realizado y no puede atender garantías ni programar mantenimientos.

## Valor

- El técnico **no cambia su forma de trabajar**: graba una nota de voz y la app arma el reporte.
- La IA **nunca tiene la última palabra**: el técnico revisa, corrige y confirma.
- Todo queda **trazable** (historial por equipo, auditoría de cambios, tiempos de procesamiento).

---

## Características

- **Multi-tenant por empresa** (`company_id`) con autorización por políticas y scoping.
- **Roles y permisos** con `spatie/laravel-permission` (`admin` / `technician` + permisos granulares).
- **Clientes, equipos y órdenes de servicio** (CRUD, estados y transiciones).
- **Grabación de voz** con `MediaRecorder` (web) y subida a disco privado.
- **Pipeline de IA desacoplado**: Speech-to-Text → extracción estructurada por LLM, con contrato `TranscriptionProvider` / `ExtractionProvider` y drivers intercambiables (`fake` para dev, `local`/`openai` para producción).
- **Reportes**: borrador automático desde la voz → editor de revisión → confirmación (`draft` → `finalized`).
- **PDF** (dompdf) y **compartir con el cliente** mediante enlace firmado (sin login).
- **Fotografías** como evidencia (acceso privado).
- **Historial de servicios por equipo**.
- **Mantenimientos programados** con notificaciones (in-app + correo) vía `maintenance:check`.
- **Auditoría y observabilidad**: `report_events` (quién/cuándo) + tiempos de IA.
- **Internacionalización** (español/inglés) con `laravel-lang` + `laravel-inertia-i18n`.
- **App móvil** con NativePHP (vistas Blade) — rama separada `caracteristica/nativephp-mobile`.

---

## Stack

- **Laravel 13** / PHP 8.4
- **Inertia.js + React** + Tailwind CSS 4 (starter kit)
- **Fortify** (autenticación, 2FA / passkeys)
- **Wayfinder** (rutas tipadas)
- **spatie/laravel-permission**, **laravel-lang/common**, **laravel-inertia-i18n**, **barryvdh/laravel-dompdf**
- **PostgreSQL** en producción (sqlite en desarrollo)
- Colas (database → Redis) y Scheduler
- Pest, PHPStan, Pint

---

## Requisitos

- PHP ≥ 8.4 (extensiones `mbstring`, `dom`, `zip`, `intl` opcional)
- Composer 2
- Node 22+ / npm
- PostgreSQL (o SQLite para desarrollo)

---

## Instalación

```bash
git clone <repo> bitacora && cd bitacora
composer install
cp .env.example .env
php artisan key:generate
# configura DB_CONNECTION / DB_* en .env (postgres) o usa sqlite por defecto
php artisan migrate --seed
npm install
npm run build
```

### Variables de entorno relevantes

```env
APP_LOCALE=es
APP_FALLBACK_LOCALE=en

FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

# Speech-to-Text: fake | local
STT_DRIVER=fake
STT_LOCAL_BINARY=whisper
STT_LOCAL_MODEL=base
STT_LOCAL_LANGUAGE=es

# Extracción con LLM: fake | openai (OpenAI / DeepSeek / Ollama-compatible)
LLM_DRIVER=fake
LLM_BASE_URL=https://api.openai.com/v1
LLM_MODEL=gpt-4o-mini
LLM_API_KEY=
```

> En desarrollo usa `STT_DRIVER=fake` / `LLM_DRIVER=fake` para probar el pipeline sin servicios externos. En producción cambia al driver real y configura el proveedor.

---

## Ejecución

```bash
# Servidor web (Inertia/React)
php artisan serve

# Worker de colas (procesa voz → transcripción → IA → reporte)
php artisan queue:work

# Scheduler (mantenimiento:check diario a las 08:00)
php artisan schedule:work          # o el scheduler del host
```

Usuarios demo (tras `--seed`):

| Correo              | Contraseña | Rol        |
| ------------------- | ---------- | ---------- |
| `admin@demo.test`   | `password` | admin      |
| `tecnico@demo.test` | `password` | technician |

---

## Estructura

```
app/
├── Actions/                 # operaciones puntuales
├── Console/Commands/        # maintenance:check
├── Enums/                   # estados y tipos
├── Http/Controllers/        # clientes, equipos, servicios, reportes, móvil…
├── Http/Middleware/         # SetLocale, HandleInertiaRequests
├── Http/Requests/           # validación
├── Jobs/                    # TranscribeAudioJob, AnalyzeTranscriptJob
├── Models/
├── Notifications/           # MaintenanceDue
├── Policies/                # autorización por empresa
├── Services/                # ReportService, ReportPdfService, ReportShareService
├── Services/Providers/      # TranscriptionProvider / ExtractionProvider (+ fake/local/openai)
├── Support/Dto/             # ExtractedReport
└── ValueObjects/
resources/
├── js/pages/                # páginas Inertia + React
└── views/                   # Blade (PDF, reporte compartido, vistas móviles)
```

---

## Testing

```bash
php artisan test --compact       # suites Pest
vendor/bin/phpstan analyse       # análisis estático (si falla por larastan: clear-result-cache)
vendor/bin/pint --dirty          # formato
```

Cada rama del proyecto incluye tests de lógica/permisos/validación y sus suites están en verde.

---

## Notas de arquitectura

- **IA desacoplada**: el dominio solo conoce los contratos `TranscriptionProvider` y `ExtractionProvider`; cambiar de proveedor no toca la lógica de negocio.
- **La IA rellena un borrador; el humano confirma** (`draft` → `finalized`); nunca sobrescribe un reporte finalizado.
- **Multi-tenant**: scoping por `company_id` con políticas; tenancy global, guard `web` (single guard).
- **Archivos privados** con enlaces autenticados/firmados (nunca públicos).

---

## Licencia

Ver [LICENSE](LICENSE).
