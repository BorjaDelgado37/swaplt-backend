# Documentación de la API SWAPLT

## Índice
1. [Introducción General](#1-introducción-general)
2. [Estructura General de la API](#2-estructura-general-de-la-api)
3. [Controladores y Endpoints](#3-controladores-y-endpoints)
   - [AuthController](#31-authcontroller)
   - [VehiculoController](#32-vehiculocontroller)
   - [MensajeController](#33-mensajecontroller)
   - [FavoritoController](#34-favoritocontroller)
   - [UserBlockController](#35-userblockcontroller)
4. [Autenticación](#4-autenticación)
5. [Códigos de Estado](#5-códigos-de-estado)
6. [Errores Comunes](#6-errores-comunes)
7. [Consideraciones de Seguridad](#7-consideraciones-de-seguridad)
8. [Variables de Entorno](#8-variables-de-entorno)
9. [Guía de Implementación](#9-guía-de-implementación)
10. [Mejores Prácticas](#10-mejores-prácticas)
11. [Limitaciones y Cuotas](#11-limitaciones-y-cuotas)
12. [Licencia](#12-licencia)
13. [Autoría](#13-autoría)

## Notas Importantes
- La API está en constante evolución. Se recomienda revisar periódicamente esta documentación para estar al tanto de los cambios.
- Todos los endpoints devuelven respuestas en formato JSON.
- Las fechas se manejan en formato ISO 8601 (YYYY-MM-DD HH:mm:ss).
- La API implementa rate limiting para prevenir abusos.
- Se recomienda implementar manejo de errores en el cliente para todas las peticiones.

## Requisitos del Sistema
- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0 o PostgreSQL >= 13
- Extensión PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Servidor web (Apache/Nginx) con mod_rewrite habilitado
- Memoria RAM recomendada: 2GB mínimo
- Espacio en disco: 1GB mínimo

## 1. Introducción General

SWAPLT es una plataforma de intercambio y venta de vehículos desarrollada con Laravel 12.0 y PHP 8.2. La API RESTful proporciona endpoints para gestionar usuarios, vehículos, mensajería, favoritos y más.

### Tecnologías Principales
- **Backend**: Laravel 12.0
- **PHP**: 8.2
- **Base de Datos**: MySQL/PostgreSQL
- **Autenticación**: JWT (JSON Web Tokens)
- **Almacenamiento**: Sistema de archivos local para imágenes
- **Dependencias Principales**:
  - `tymon/jwt-auth`: Para autenticación JWT
  - `laravel/socialite`: Para autenticación con Google
  - `barryvdh/laravel-dompdf`: Para generación de PDFs

## 2. Estructura General de la API

La API está organizada en los siguientes módulos principales:

1. **Autenticación** (`/api/auth/*`)
2. **Usuarios** (`/api/users/*`)
3. **Vehículos** (`/api/vehiculos/*`)
4. **Categorías** (`/api/categorias/*`)
5. **Favoritos** (`/api/favoritos/*`)
6. **Mensajes** (`/api/mensajes/*`)
7. **Bloqueos** (`/api/usuarios/bloqueos/*`)
8. **Valoraciones** (`/api/valoraciones/*`)

## 3. Controladores y Endpoints

### 3.1 AuthController

Gestiona la autenticación y autorización de usuarios.

#### Endpoints:

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| POST | `/api/register` | Registro de nuevo usuario | No |
| GET | `/api/verify-email/{code}` | Verificación de email | No |
| POST | `/api/login` | Inicio de sesión | No |
| GET | `/api/me` | Obtener datos del usuario actual | Sí |
| POST | `/api/logout` | Cerrar sesión | Sí |
| POST | `/api/refresh` | Refrescar token JWT | Sí |
| POST | `/api/password/reset-request` | Solicitar reset de contraseña | No |
| GET | `/api/password/reset/{token}` | Mostrar formulario de reset | No |
| POST | `/api/password/reset/{token}` | Resetear contraseña | No |
| PUT | `/api/profile` | Actualizar perfil | Sí |

#### Ejemplo de Registro:
```json
POST /api/register
{
    "name": "Usuario Ejemplo",
    "email": "usuario@ejemplo.com",
    "password": "contraseña123",
    "password_confirmation": "contraseña123"
}
```

#### Ejemplo de Login:
```json
POST /api/login
{
    "email": "usuario@ejemplo.com",
    "password": "contraseña123"
}
```

### 3.2 VehiculoController

Gestiona todas las operaciones relacionadas con vehículos.

#### Endpoints:

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| GET | `/api/vehiculos` | Listar vehículos | No |
| GET | `/api/vehiculos/search` | Buscar vehículos | No |
| GET | `/api/vehiculos/filter` | Filtrar vehículos | No |
| GET | `/api/vehiculos/{id}` | Ver detalle de vehículo | No |
| POST | `/api/vehiculos` | Crear vehículo | Sí |
| PUT | `/api/vehiculos/{id}` | Actualizar vehículo | Sí |
| DELETE | `/api/vehiculos/{id}` | Eliminar vehículo | Sí |
| GET | `/api/user/vehiculos` | Mis vehículos | Sí |
| GET | `/api/vehiculos/{id}/estadisticas-visitas` | Estadísticas de visitas | Sí |

#### Ejemplo de Creación de Vehículo:
```json
POST /api/vehiculos
{
    "user_id": 1,
    "categoria_id": 1,
    "marca": "Toyota",
    "modelo": "Corolla",
    "precio": 25000,
    "anio": 2020,
    "estado": "usado",
    "transmision": "automático",
    "tipo_combustible": "gasolina",
    "kilometraje": 50000,
    "fuerza": 150,
    "capacidad_motor": 1.8,
    "color": "negro",
    "ubicacion": "Madrid",
    "matricula": "1234ABC",
    "numero_serie": "ABC123XYZ",
    "numero_puertas": 4,
    "descripcion": "Vehículo en excelente estado",
    "vehiculo_robado": "no",
    "vehiculo_libre_accidentes": "si"
}
```

### 3.3 MensajeController

Gestiona el sistema de mensajería entre usuarios.

#### Endpoints:

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| POST | `/api/mensajes` | Enviar mensaje | Sí |
| GET | `/api/mensajes/{emisor_id}/{receptor_id}` | Ver conversación | Sí |
| PATCH | `/api/mensajes/{id}/leido` | Marcar como leído | Sí |
| DELETE | `/api/mensajes/{id}` | Eliminar mensaje | Sí |

#### Ejemplo de Envío de Mensaje:
```json
POST /api/mensajes
{
    "emisor_id": 1,
    "receptor_id": 2,
    "contenido": "Hola, ¿está disponible el vehículo?"
}
```

### 3.4 FavoritoController

Gestiona los vehículos favoritos de los usuarios.

#### Endpoints:

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| POST | `/api/favoritos` | Añadir a favoritos | Sí |
| GET | `/api/favoritos` | Ver favoritos | Sí |
| DELETE | `/api/favoritos/{id}` | Eliminar de favoritos | Sí |

#### Ejemplo de Añadir Favorito:
```json
POST /api/favoritos
{
    "vehiculo_id": 1
}
```

### 3.5 UserBlockController

Gestiona el sistema de bloqueo entre usuarios.

#### Endpoints:

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| GET | `/api/usuarios/bloqueos-todos` | Ver todos los bloqueos | Sí |
| POST | `/api/usuarios/{usuario}/bloquear` | Bloquear usuario | Sí |
| POST | `/api/usuarios/{usuario}/desbloquear` | Desbloquear usuario | Sí |
| GET | `/api/usuarios/bloqueados` | Ver usuarios bloqueados | Sí |
| GET | `/api/usuarios/que-me-bloquearon` | Ver usuarios que me bloquearon | Sí |

## 4. Autenticación

La API utiliza JWT (JSON Web Tokens) para la autenticación. Para acceder a endpoints protegidos:

1. Obtener token mediante login
2. Incluir token en el header de las peticiones:
```
Authorization: Bearer {token}
```

### Ejemplo de Uso:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","password":"contraseña123"}'

# Usar el token recibido
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer {token_recibido}"
```

## 5. Códigos de Estado

| Código | Descripción |
|--------|-------------|
| 200 | OK - Petición exitosa |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Error en la petición |
| 401 | Unauthorized - No autenticado |
| 403 | Forbidden - No autorizado |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

## 6. Errores Comunes

### 6.1 Errores de Autenticación
```json
{
    "error": "Token inválido"
}
```

### 6.2 Errores de Validación
```json
{
    "errors": {
        "email": ["El email ya está registrado"],
        "password": ["La contraseña debe tener al menos 8 caracteres"]
    }
}
```

### 6.3 Errores de Recurso No Encontrado
```json
{
    "message": "Vehículo no encontrado"
}
```

## 7. Consideraciones de Seguridad

1. Todas las contraseñas se almacenan hasheadas
2. Los tokens JWT tienen tiempo de expiración
3. Implementación de middleware de autenticación
4. Validación de datos en todos los endpoints
5. Protección contra CSRF
6. Sanitización de inputs
7. Control de acceso basado en roles

## 8. Variables de Entorno

Crear archivo `.env` con las siguientes variables:

```env
APP_NAME=SWAPLT
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=swaplt
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=your-jwt-secret
JWT_TTL=60

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"
```

## 9. Guía de Implementación

### 9.1 Instalación
```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/swaplt.git

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos
php artisan migrate
php artisan db:seed

# Iniciar servidor
php artisan serve
```

### 9.2 Configuración del Cliente
```javascript
// Ejemplo de configuración con Axios
const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Interceptor para manejar tokens
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});
```

## 10. Mejores Prácticas

### 10.1 Manejo de Imágenes
- Máximo 5 imágenes por vehículo
- Formatos permitidos: JPG, PNG, GIF
- Tamaño máximo por imagen: 2MB
- Resolución recomendada: 1920x1080
- Comprimir imágenes antes de subirlas

### 10.2 Optimización de Peticiones
- Implementar caché en el cliente
- Usar paginación para listados grandes
- Implementar infinite scroll en lugar de paginación tradicional
- Comprimir respuestas usando gzip
- Utilizar campos específicos en lugar de select *

### 10.3 Seguridad
- Nunca almacenar tokens en localStorage (usar httpOnly cookies)
- Implementar refresh tokens
- Validar todas las entradas en el cliente
- Sanitizar datos antes de mostrarlos
- Implementar CSRF tokens en formularios

## 11. Limitaciones y Cuotas

### 11.1 Límites de API
| Endpoint | Límite por minuto | Límite por hora |
|----------|------------------|-----------------|
| /api/login | 5 | 30 |
| /api/register | 3 | 10 |
| /api/vehiculos | 60 | 1000 |
| /api/mensajes | 30 | 500 |
| /api/favoritos | 30 | 500 |

### 11.2 Límites de Recursos
- Máximo de vehículos por usuario: 10
- Máximo de imágenes por vehículo: 5
- Tamaño máximo de mensaje: 1000 caracteres
- Tiempo de expiración de token: 60 minutos
- Tiempo de expiración de refresh token: 30 días

### 11.3 Políticas de Uso
- No se permite el scraping de datos
- No se permite el uso de la API para spam
- Se requiere atribución en caso de uso público
- Se reserva el derecho de bloquear IPs abusivas
- Se recomienda implementar backoff exponencial en caso de errores

## 12. Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 13. Autoría

Desarrollado por el equipo de SWAPLT.

---

Para más información o soporte, contactar al equipo de desarrollo. 