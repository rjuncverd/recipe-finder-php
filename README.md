# Recipe Finder API

Servicio REST mínimo en PHP sin framework, con Docker.

## Requisitos

- Docker
- Docker Compose
- Una API key de Spoonacular

## Configuración

1. Copia tu clave de Spoonacular en el archivo `.env`:

```env
SPOONACULAR_API_KEY=tu_api_key_aqui
```

2. Levanta el servicio:

```bash
docker compose up --build
```

3. Consulta la API:

```bash
curl "http://localhost:8000/?name=pasta"
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
  "imageUrl": "https://...jpg"
}
```

El servicio busca por nombre y devuelve el primer resultado disponible de Spoonacular.
