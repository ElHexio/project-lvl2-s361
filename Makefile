install:
	composer install

lint:
	composer run-script phpcs -- --standard=PSR12 src bin tests

test:
	composer run-script test

test_coverage:
	composer run-script test_coverage
