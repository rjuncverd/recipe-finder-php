# Recipe Finder API

API REST mínima en PHP para buscar recetas por nombre usando Spoonacular. El proyecto está pensado para ejecutarse con Docker y expone una documentación OpenAPI/Swagger para probar el endpoint desde el navegador.

## Descripción

- Endpoint principal: `GET /recipes?name=...`
- Base URL local: `http://localhost:8000`
- Swagger UI: `http://localhost:8000/swagger.html`
- Especificación OpenAPI: `http://localhost:8000/openapi.yaml`

La API valida el parámetro `name`, consulta Spoonacular y devuelve la información principal de la receta encontrada.

## Requisitos

- Docker
- Docker Compose
- PHP 8.2 (solo si quieres ejecutarlo fuera de Docker)
- Composer (opcional si quieres instalar dependencias localmente)
- Una API key válida de Spoonacular

## Instalación

1. Clona el repositorio:

```bash
git clone <url-del-repositorio>
cd recipe-finder-php
```

2. Crea el archivo de entorno a partir del ejemplo:

```bash
cp .env.example .env
```

3. Añade tu clave de Spoonacular en `.env`:

```env
SPOONACULAR_API_KEY=tu_api_key_aqui
```

4. Instala dependencias de PHP con Composer:

```bash
composer install
```

> Si ya tienes el proyecto con `vendor/` incluido, este paso puede omitirse, pero es recomendable para asegurar que PHPUnit y las dependencias queden instaladas correctamente.

## Ejecución con Docker

Levanta el servicio:

```bash
docker compose up --build -d
```

El contenedor quedará disponible en:

```bash
http://localhost:8000
```

Para parar la aplicación:

```bash
docker compose down
```

## Ejecución sin Docker

Si prefieres arrancarlo directamente con PHP:

```bash
export SPOONACULAR_API_KEY=tu_api_key_aqui
php -S 0.0.0.0:8000 -t public
```

Y luego accede a:

```bash
http://localhost:8000
```

## Endpoints

### 1) Ruta de bienvenida

```bash
GET http://localhost:8000/
```

Devuelve un mensaje con información básica sobre la API y la URL de Swagger.

### 2) Buscar receta por nombre

```bash
GET http://localhost:8000/recipes?name=pasta
```

Ejemplos:

```bash
curl "http://localhost:8000/recipes?name=pasta"
```

```bash
curl "http://localhost:8000/recipes?name=lasagna"
```

## Ejemplos de uso

### Con curl

```bash
curl -X GET "http://localhost:8000/recipes?name=pasta" \
  -H "Accept: application/json"
```

### Con PowerShell

```powershell
Invoke-RestMethod -Uri "http://localhost:8000/recipes?name=pasta" -Method Get
```

## Respuesta esperada

```json
{
  "name": "Pasta Primavera",
  "prepTimeMinutes": 30,
  "servings": 4,
  "ingredients": [
    "200 g pasta",
    "1 zucchini",
    "1 bell pepper"
  ],
  "instructions": [
    "Cook the pasta.",
    "Sauté the vegetables."
  ],
  "imageUrl": "https://img.spoonacular.com/recipes/123456-556x370.jpg"
}
```

## Manejo de errores

La API puede devolver errores si:

- falta el parámetro `name`
- la API key de Spoonacular no está configurada
- la petición HTTP no es válida
- ocurre un error interno al consultar Spoonacular

Ejemplos:

```bash
curl "http://localhost:8000/recipes"
```

Respuesta típica:

```json
{
  "error": "The query parameter \"name\" is required."
}
```

## Documentación Swagger

La documentación interactiva está disponible en:

```bash
http://localhost:8000/swagger.html
```

Desde ahí puedes:

- ver la especificación OpenAPI
- consultar los parámetros del endpoint
- probar la API directamente en el navegador

La especificación completa también está en:

```bash
http://localhost:8000/openapi.yaml
```

## Tests con PHPUnit

El proyecto incluye pruebas unitarias para validar la lógica del servicio, el handler y la validación de entrada.

Ejecuta la suite:

```bash
./vendor/bin/phpunit
```

O, si prefieres ejecutar con Docker:

```bash
docker compose exec app ./vendor/bin/phpunit
```

## Estructura del proyecto

```text
.
├── public/
│   ├── index.php
│   ├── openapi.yaml
│   ├── swagger.html
│   └── recipes/
│       └── index.php
├── src/
│   ├── RecipeRepositoryInterface.php
│   ├── SpoonacularClient.php
│   ├── RecipeService.php
│   ├── RecipeRequestHandler.php
│   └── FileCache.php
├── tests/
├── .env.example
├── .env
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── phpunit.xml
├── README.md
└── storage/
```

## Notas finales

- La API usa una caché local simple para evitar llamadas repetidas a Spoonacular.
- El endpoint principal es `GET /recipes?name=...`.
- El proyecto está pensado como una solución mínima y clara para un servicio REST simple en PHP.
