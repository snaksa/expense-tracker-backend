.PHONY: init up down start stop build ssh help
.DEFAULT_GOAL := help

help:
	@echo ""
	@echo "usage: make COMMAND"
	@echo ""
	@echo "Commands:"
	@echo "     init            Initializes the project for first time."
	@echo "     up              Starts application containers and services."
	@echo "     down            Stops application containers and services."
	@echo "     build           Builds the application containers."
	@echo "     ssh             Use it if you want to SSH into the PHP service."

init: dependencies
	@echo "⬆ Container initialized"

dependencies: composer.json composer.lock
	$(info 📦 Make: Installing dependencies.)
	@docker-compose run --rm php composer self-update
	@docker-compose run --rm php composer validate
	@docker-compose run --rm php composer install

up:
	$(info 🔥 Make: Starting up.)
	@docker-compose up -d php

down:
	$(info 💥 Make: Shutting down.)
	@docker-compose down

build:
	$(info 🏗  Make: Building environment images.)
	@docker-compose rm -vsf
	@docker-compose down -v --remove-orphans
	@docker-compose build

ssh:
	$(info 💻 Make: SSH into PHP container.)
	@docker-compose exec php bash