.PHONY: help build up down restart shell composer artisan npm install

help:
	@echo "Available commands:"
	@echo "  make build       - Build Docker containers"
	@echo "  make up          - Start Docker containers"
	@echo "  make down        - Stop Docker containers"
	@echo "  make restart     - Restart Docker containers"
	@echo "  make shell       - Access PHP container shell"
	@echo "  make composer    - Run Composer commands"
	@echo "  make artisan     - Run Artisan commands"
	@echo "  make npm         - Run NPM commands"
	@echo "  make install     - Full installation"

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

restart: down up

shell:
	docker-compose exec php sh

composer:
	docker-compose exec php composer $(filter-out $@,$(MAKECMDGOALS))

artisan:
	docker-compose exec php php artisan $(filter-out $@,$(MAKECMDGOALS))

npm:
	docker-compose run --rm node npm $(filter-out $@,$(MAKECMDGOALS))

install:
	@echo "Installing Laravel project..."
	docker-compose build
	docker-compose up -d
	docker-compose exec php composer create-project laravel/laravel temp-laravel
	docker-compose exec php sh -c "mv temp-laravel/* . && mv temp-laravel/.* . 2>/dev/null || true && rm -rf temp-laravel"
	docker-compose exec php composer install
	docker-compose exec php php artisan key:generate
	docker-compose run --rm node npm install
	docker-compose run --rm node npm run build
	@echo "Installation complete!"

%:
	@: