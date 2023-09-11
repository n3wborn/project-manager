# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc db-fixtures db-create db-migration db-reset db-restore db-drop db-save

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build

build-pull-cache: ## Pull images, don't use cache and build the Docker image
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub
	@$(DOCKER_COMP) up --force-recreate --remove-orphans

up-renew-anon-volumes: ## Start the docker hub
	@$(DOCKER_COMP) up --force-recreate --remove-orphans --renew-anon-volumes

stop: ## Stop containers
	@$(DOCKER_COMP) stop

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Database ———————————————————————————————————————————————————————————————
db-create:
	@$(SYMFONY) doctrine:database:create --if-not-exists || true

db-migration:
	@$(SYMFONY) doctrine:migrations:migrate -n

db-drop:
	@$(SYMFONY) doctrine:database:drop --force || true

db-save:
	docker exec -it project-manager-database mysqldump -uroot -proot db_name | gzip > db.sql.gz

db-restore:
	gunzip -c db.sql.gz | docker exec -i project-manager-database -uroot -proot db_name

db-reset: db-drop db-create db-migration db-fixtures

db-fixtures:
	@$(SYMFONY) doctrine:fixtures:load -n --env=dev

