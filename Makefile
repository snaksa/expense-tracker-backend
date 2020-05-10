.PHONY: init up down start stop build ssh help
.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "usage: make COMMAND"
	@echo ""
	@echo "Commands:"
	@echo "     up              Starts application containers and services."
	@echo "     down            Stops application containers and services."
	@echo "     build           Builds the application containers."
	@echo "     ssh             Use it if you want to SSH into the PHP service."

up:
	$(info 🔥 Make: Starting up.)
	@docker-compose up -d php db

down:
	$(info 💥 Make: Shutting down.)
	@docker-compose down

build:
	$(info 🏗  Make: Building environment images.)
	@docker-compose rm -vsf
	@docker-compose down -v --remove-orphans
	@docker-compose build

migrate:
	$(info 📦 Make: Running migrations)
	@docker-compose run --rm backend bin/console doctrine:migrations:migrate --quiet

fixtures:
	$(info 📦 Make: Running migrations)
	@docker-compose run --rm backend bin/console doctrine:fixtures:load --quiet --env=fixtures

ssh:
	$(info 💻 Make: SSH into PHP container.)
	@docker-compose exec backend bash
