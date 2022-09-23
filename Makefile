# === Makefile Helper ===

# Styles
YELLOW=$(shell echo "\033[00;33m")
RED=$(shell echo "\033[00;31m")
RESTORE=$(shell echo "\033[0m")


# Variables
PHP := php
COMPOSER := composer
CURRENT_DIR := $(shell pwd)
CHECKER_VERSION := 1.0.0
CHECKER_OS := linux_amd64

.DEFAULT_GOAL := list

.PHONY: list
list:
	@echo "******************************"
	@echo "${YELLOW}Available targets${RESTORE}:"
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | sort | awk 'BEGIN {FS = ":.*?## "}; {printf " ${YELLOW}%-15s${RESTORE} > %s\n", $$1, $$2}'
	@echo "${RED}==============================${RESTORE}"


.PHONY: install
install: ## Install vendors
	@$(COMPOSER) install

.PHONY: codeclean
codeclean: ## Coding Standard checks
codeclean: install
	@$(PHP) $(CURRENT_DIR)/vendor/bin/php-cs-fixer fix --config=$(CURRENT_DIR)/.cs/.php_cs.php
	@$(PHP) $(CURRENT_DIR)/vendor/bin/phpcbf --standard=$(CURRENT_DIR)/.cs/cs_ruleset.xml --extensions=php $(CURRENT_DIR)/bundle/
	@$(PHP) $(CURRENT_DIR)/vendor/bin/phpcs --standard=$(CURRENT_DIR)/.cs/cs_ruleset.xml --extensions=php $(CURRENT_DIR)/bundle/

.PHONY: security-checker
security-checker: # check security
	@bash scripts/security-checker.bash ${CHECKER_VERSION} ${CHECKER_OS}