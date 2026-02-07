.PHONY: help install lint lint-fix test setup ci

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install PHP dependencies via Composer
	composer install --no-interaction --prefer-dist

setup: install ## Full project setup (install deps + create .env)
	@test -f .env || (cp .env.example .env && echo "Created .env from .env.example — edit with your DB credentials")

lint: ## Run PHP_CodeSniffer linting
	composer lint

lint-fix: ## Auto-fix linting issues
	composer lint:fix

test: ## Run PHPUnit tests
	composer test

ci: lint test ## Run full CI pipeline (lint + test)
