# 🍰 Pastelería - CRUD de Pasteles e Ingredientes

Aplicación web completa para gestionar pasteles y sus ingredientes.  
Desarrollada con **Vue 3** (frontend), **PHP nativo** (backend REST API) y **MySQL** (base de datos).  
Implementa un **maestro-detalle**: un pastel puede tener muchos ingredientes, y cada ingrediente puede estar en múltiples pasteles.

---

## 📋 Requisitos previos

- **PHP 7.4 o superior** (recomendado: PHP 8.3/8.4 estable) con las siguientes extensiones activas:
  - `pdo_mysql`
  - `mysqli` (opcional)
- **MySQL** (5.7 o superior) o MariaDB
- **Git** (para clonar el repositorio)
- **Navegador web** (Chrome, Firefox, Edge)
- **Postman** (opcional, para probar la API directamente)

---

## 🚀 Instalación y ejecución

### 1. Clonar el repositorio

```bash
git clone https://github.com/BrayanPradoMarroquin/pruebatecnicaMICOOPE.git
cd pasteleria

```

### 2. Configurar la base de datos

- Abre tu gestor de MySQL (phpMyAdmin, MySQL Workbench o línea de comandos).

- Ejecuta el script database/schema.sql.
- Este script:
    - Crea la base de datos pasteleria.
    - Crea las tablas pasteles, ingredientes y pastel_ingrediente.

### 3. Configurar conexión a la base de datos

Edita el archivo ```api/config.php``` y ajusta las credenciales de MySQL si es necesario:

```
$host = 'localhost';
$dbname = 'pasteleria';
$username = 'root';
$password = '';   // Tu contraseña de MySQL
```

### 4. Ejecutar el servidor PHP

Desde la raíz del proyecto (donde están ```index.html``` y ```router.php```), ejecuta:

```
php -S localhost:8000 router.php
```

Si usas una versión específica de PHP (por ejemplo, ```C:\php8\php.exe```), reemplaza ```php``` por la ruta completa.

El servidor quedará escuchando en ```http://localhost:8000```.

5. Acceder a la aplicación
Abre tu navegador y ve a: ```http://localhost:8000```

Verás la interfaz con tres pestañas:

Pasteles: lista, crea, edita o elimina pasteles (asignando ingredientes y cantidades).

Ingredientes: CRUD completo de ingredientes.

Reporte: muestra todos los pasteles con sus ingredientes.

## 🧪 Probar la API con Postman (opcional)

Puedes importar la colección de Postman proporcionada durante el desarrollo. Los endpoints disponibles son:

| Metodo | Endpoint | Descripcion |
| ------- | --------- | ------------- |
| GET | /api/ingregientes | Lista todos los ingredientes |
| POST | /api/ingredientes | Crea un nuevo ingrediente |
| PUT | /api/ingredientes/{id} | Actualiza un ingrediente |
| DELETE | /api/ingredientes/{id} | Elimina un ingrediente (si no está usado) |
| GET | /api/pasteles | Lista todos los pasteles (datos básicos) |
| GET | /api/pasteles/{id} | Obtiene un pastel con sus ingredientes |
| POST | /api/pasteles | Crea un pastel con sus ingredientes asociados |
| PUT | /api/pasteles/{id} | Actualiza pastel y su lista de ingredientes |
| DELETE | /api/pasteles/{id} | Elimina un pastel (en cascada) |
| GET | /api/reporte | Reporte completo: pasteles + ingredientes |

## Ejemplo de creación de pastel (POST /api/pasteles)

```
{
  "nombre": "Pastel de Zanahoria",
  "descripcion": "Con nueces y queso crema",
  "ingredientes": [
    { "id": 1, "cantidad": "300" },
    { "id": 2, "cantidad": "3" }
  ]
}
```

## 📁 Estructura del proyecto
```
PruebaTecnica
├── index.html                 # Frontend Vue
├── router.php                 # Enrutador para el servidor integrado
├── api/
│   ├── config.php             # Conexión a la base de datos
│   └── index.php              # API REST (router + lógica)
├── database/
│   └── script.sql             # Script de creación de la BD
└── README.md                  # Este archivo
```