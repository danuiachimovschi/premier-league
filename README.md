# PHP Premier League

## Setup
```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

## Run Tests
```bash
docker compose exec app composer test
```

## Development
```bash
docker compose up -d
```
