# ==============================================================================
# Makefile for Hi.Events (FrankenPHP & React Edition)
# Designed for high-velocity local development on macOS.
# ==============================================================================

.PHONY: setup deps build up down restart logs ps shell-backend shell-frontend help octane-reload db-migrate test

# Default target: display help
.DEFAULT_GOAL := help

# Variables
DOCKER_DEV_COMPOSE := docker/development/docker-compose.dev.yml

help: ## Display available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

setup: ## Full project initialization (Env, Deps, Build, Up)
	@echo "🔧 Provisioning local environment..."
	@if [ ! -f backend/.env ]; then cp backend/.env.example backend/.env; fi
	@if [ ! -f frontend/.env ]; then cp frontend/.env.example frontend/.env; fi
	@$(MAKE) deps
	@$(MAKE) build
	@$(MAKE) up
	@echo "⏳ Waiting for backend to stabilize..."
	@sleep 5
	@$(MAKE) ps

deps: ## Install local dependencies (Composer & Yarn) to prevent container exit
	@echo "📦 Installing backend dependencies via Composer..."
	@cd backend && composer install --no-interaction --prefer-dist
	@echo "📦 Installing frontend dependencies via Yarn..."
	@cd frontend && yarn install --frozen-lockfile

build: ## Build or rebuild the Docker services
	@echo "🏗️ Building containers..."
	docker compose -f $(DOCKER_DEV_COMPOSE) build

up: ## Start the environment in detached mode
	@echo "🚀 Launching containers..."
	docker compose -f $(DOCKER_DEV_COMPOSE) up -d
	@echo "------------------------------------------------------------------------------"
	@echo "Backend API: http://localhost:8000"
	@echo "Frontend:    http://localhost:3000"
	@echo "------------------------------------------------------------------------------"

down: ## Stop and remove containers, networks, and images
	@echo "🛑 Shutting down environment..."
	docker compose -f $(DOCKER_DEV_COMPOSE) down

ps: ## Check the status of the containers
	docker compose -f $(DOCKER_DEV_COMPOSE) ps

restart: down up ## Restart the entire Docker stack

logs: ## Follow logs from all containers
	docker compose -f $(DOCKER_DEV_COMPOSE) logs -f

octane-reload: ## Reload the Laravel Octane worker
	docker compose -f $(DOCKER_DEV_COMPOSE) exec backend php artisan octane:reload

db-migrate: ## Run database migrations
	docker compose -f $(DOCKER_DEV_COMPOSE) exec backend php artisan migrate --force

shell-backend: ## Open a shell inside the backend (FrankenPHP) container
	docker compose -f $(DOCKER_DEV_COMPOSE) exec backend bash

shell-frontend: ## Open a shell inside the frontend container
	docker compose -f $(DOCKER_DEV_COMPOSE) exec frontend sh

test: ## Run backend unit and feature tests
	@echo "🧪 Running tests..."
	docker compose -f $(DOCKER_DEV_COMPOSE) exec backend php artisan test
	