#LINT_TARGETS = $(addprefix lint-,$(shell find ./ -name "*.php"  \( ! -name "*%%*" ! -name ".*"  \)))

all: tests

.PHONY: $(LINT_TARGETS)
$(LINT_TARGETS):lint-%:%
	@php -l $< >/dev/null

.PHONY: lint
lint: $(LINT_TARGETS)
	@echo Lint finished

docker-enter:
	docker-compose exec apache /bin/bash

