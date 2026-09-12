.PHONY: build

RM = rm -rf

composer-version:
	composer --version

composer-install:
	composer install --prefer-dist

composer-autoload:
	composer dump-autoload -o

cleanup:
	@$(RM) build/api/*
	@$(RM) build/coverage/*
	@$(RM) build/logs/*
	@$(RM) build/pdepend/*
	@$(RM) build/phpdox/*

lint:
	vendor/bin/parallel-lint -j $(shell nproc --all) --blame src tests

phpunit:
	XDEBUG_MODE=coverage php -d zend_extension=xdebug.so tools/phpunit --configuration=./build/phpunit-all.xml --coverage-text --only-summary-for-coverage-text #--display-deprecations

build: composer-install lint phpunit

deploy: build
	# No Deployment here -- library only

